<?php

namespace App\Domain\EventFinance;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

/**
 * Finance-plan price intervals: validation, normalisation and persistence.
 *
 * Every interval applies to an audience set made of audience keys:
 * "Q:<QetaaID>" (a sector linked to the event), "FAMILY" and/or "GUEST".
 * Per audience key the intervals must form one contiguous chain that ends on
 * or before the event start; a missing tail is filled with the last price.
 */
class FinancePlanIntervals
{
    public const QETAA = 'QETAA';

    public const FAMILY = 'FAMILY';

    public const GUEST = 'GUEST';

    public const MAX_ROWS = 60;

    /**
     * Sectors linked to the season event's underlying event.
     *
     * @return array<int, string> QetaaID => QetaaName
     */
    public function eventSectors(int $seasonEventId): array
    {
        $eventId = DB::table('SeasonEvent')->where('SeasonEventID', $seasonEventId)->value('EventID');
        if (! $eventId) {
            return [];
        }

        return $this->sectorsByEvent([(int) $eventId])[(int) $eventId] ?? [];
    }

    /**
     * EventQetaa has no primary key, so duplicates are collapsed here.
     *
     * @param  list<int>  $eventIds
     * @return array<int, array<int, string>> EventID => [QetaaID => QetaaName]
     */
    public function sectorsByEvent(array $eventIds): array
    {
        if ($eventIds === []) {
            return [];
        }

        $rows = DB::table('EventQetaa as eq')
            ->join('Qetaa as q', 'eq.QetaaID', '=', 'q.QetaaID')
            ->whereIn('eq.EventID', $eventIds)
            ->distinct()
            ->orderBy('q.QetaaName')
            ->get(['eq.EventID', 'q.QetaaID', 'q.QetaaName']);

        $sectors = [];
        foreach ($rows as $row) {
            $sectors[(int) $row->EventID][(int) $row->QetaaID] = (string) $row->QetaaName;
        }

        return $sectors;
    }

    /**
     * Validate raw form rows and normalise them (sorted, tails auto-filled).
     *
     * @param  mixed  $rows  intervals[i] => ['start_date', 'end_date', 'price', 'audience' => list<string>]
     * @param  array<int, string>  $sectors  eligible sectors of the event: QetaaID => QetaaName
     * @return array{success: bool, message?: string, intervals?: list<array{StartDate: string, EndDate: string, Price: int, Audience: list<string>}>}
     */
    public function prepare(mixed $rows, array $sectors, string $eventStartDate): array
    {
        try {
            $eventStart = $this->parseDate($eventStartDate);
            $intervals = $this->normaliseRows($rows, $sectors, $eventStart);
            $fillers = $this->assertChainsAndFillTails($intervals, $sectors, $eventStart);
            $this->assertEverySectorPriced($intervals, $sectors);
        } catch (InvalidArgumentException $invalid) {
            return ['success' => false, 'message' => $invalid->getMessage()];
        }

        return ['success' => true, 'intervals' => [...$intervals, ...$fillers]];
    }

    /**
     * @param  array<int, string>  $sectors
     * @return list<array{StartDate: string, EndDate: string, Price: int, Audience: list<string>}>
     */
    private function normaliseRows(mixed $rows, array $sectors, Carbon $eventStart): array
    {
        if (! is_array($rows) || $rows === []) {
            throw new InvalidArgumentException(__('At least one price interval is required.'));
        }

        if (count($rows) > self::MAX_ROWS) {
            throw new InvalidArgumentException(__('Too many price intervals (max :max).', ['max' => self::MAX_ROWS]));
        }

        $intervals = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                throw new InvalidArgumentException(__('Price interval data is invalid.'));
            }
            $intervals[] = $this->normaliseRow($row, $sectors, $eventStart);
        }

        usort($intervals, fn (array $a, array $b) => [$a['StartDate'], $a['Price']] <=> [$b['StartDate'], $b['Price']]);

        return $intervals;
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $sectors
     * @return array{StartDate: string, EndDate: string, Price: int, Audience: list<string>}
     */
    private function normaliseRow(array $row, array $sectors, Carbon $eventStart): array
    {
        $start = trim((string) ($row['start_date'] ?? ''));
        $end = trim((string) ($row['end_date'] ?? ''));
        $price = $row['price'] ?? null;

        if ($start === '' || $end === '' || $price === null || $price === '') {
            throw new InvalidArgumentException(__('All price interval fields must be filled.'));
        }

        if (! is_int($price) && ! ctype_digit((string) $price)) {
            throw new InvalidArgumentException(__('Price must be a whole number without cents.'));
        }

        $startDate = $this->parseDate($start);
        $endDate = $this->parseDate($end);

        if ($startDate->gt($endDate)) {
            throw new InvalidArgumentException(__('Interval start date must be on or before the end date.'));
        }

        if ($endDate->gt($eventStart)) {
            throw new InvalidArgumentException(__('No price interval may exceed the event start date.'));
        }

        return [
            'StartDate' => $startDate->format('Y-m-d'),
            'EndDate' => $endDate->format('Y-m-d'),
            'Price' => (int) $price,
            'Audience' => $this->normaliseAudience($row['audience'] ?? null, $sectors),
        ];
    }

    /**
     * Per audience key the intervals must chain without gaps or overlap; a chain ending
     * before the event start is extended with a filler row carrying the last price.
     *
     * @param  list<array{StartDate: string, EndDate: string, Price: int, Audience: list<string>}>  $intervals  sorted by StartDate
     * @param  array<int, string>  $sectors
     * @return list<array{StartDate: string, EndDate: string, Price: int, Audience: list<string>}>
     */
    private function assertChainsAndFillTails(array $intervals, array $sectors, Carbon $eventStart): array
    {
        $fillers = [];
        foreach ($this->audienceKeys($intervals) as $key) {
            $chain = array_values(array_filter($intervals, fn (array $interval) => in_array($key, $interval['Audience'], true)));

            for ($i = 1; $i < count($chain); $i++) {
                $expectedStart = Carbon::parse($chain[$i - 1]['EndDate'])->addDay()->format('Y-m-d');
                if ($chain[$i]['StartDate'] !== $expectedStart) {
                    throw new InvalidArgumentException(__('For :audience, each interval must start the day after the previous one ends, with no gaps or overlap.', [
                        'audience' => $this->audienceLabel($key, $sectors),
                    ]));
                }
            }

            $last = end($chain);
            $lastEnd = Carbon::parse($last['EndDate']);
            if ($lastEnd->lt($eventStart)) {
                $fillerStart = $lastEnd->copy()->addDay()->format('Y-m-d');
                $fillerKey = $fillerStart.'|'.$last['Price'];
                $fillers[$fillerKey] ??= [
                    'StartDate' => $fillerStart,
                    'EndDate' => $eventStart->format('Y-m-d'),
                    'Price' => $last['Price'],
                    'Audience' => [],
                ];
                $fillers[$fillerKey]['Audience'][] = $key;
            }
        }

        return array_values($fillers);
    }

    /**
     * @param  list<array{Audience: list<string>}>  $intervals
     * @param  array<int, string>  $sectors
     */
    private function assertEverySectorPriced(array $intervals, array $sectors): void
    {
        $pricedSectorIds = [];
        foreach ($this->audienceKeys($intervals) as $key) {
            if (str_starts_with($key, 'Q:')) {
                $pricedSectorIds[(int) substr($key, 2)] = true;
            }
        }

        $missing = array_diff_key($sectors, $pricedSectorIds);
        if ($missing !== []) {
            throw new InvalidArgumentException(__('Sectors without a price: :sectors', ['sectors' => implode('، ', $missing)]));
        }
    }

    private function parseDate(string $date): Carbon
    {
        try {
            return Carbon::parse($date)->startOfDay();
        } catch (Throwable) {
            throw new InvalidArgumentException(__('One of the interval dates is invalid.'));
        }
    }

    /**
     * Replace every price row (and its audiences) of the season event. Call inside a transaction.
     *
     * @param  list<array{StartDate: string, EndDate: string, Price: int, Audience: list<string>}>  $intervals
     */
    public function replace(int $seasonEventId, array $intervals): void
    {
        $this->deleteAll($seasonEventId);

        foreach ($intervals as $interval) {
            $priceId = (int) DB::table('SeasonEventFinancePrice')->insertGetId([
                'SeasonEventID' => $seasonEventId,
                'StartDate' => $interval['StartDate'],
                'EndDate' => $interval['EndDate'],
                'Price' => $interval['Price'],
            ]);

            DB::table('SeasonEventFinancePriceAudience')->insert(array_map(
                fn (string $key) => $this->audienceRow($priceId, $key),
                $interval['Audience']
            ));
        }
    }

    public function deleteAll(int $seasonEventId): void
    {
        $priceIds = DB::table('SeasonEventFinancePrice')
            ->where('SeasonEventID', $seasonEventId)
            ->pluck('SeasonEventFinancePriceID');

        if ($priceIds->isEmpty()) {
            return;
        }

        // Explicit delete: the SQLite test schema has no FK cascade.
        DB::table('SeasonEventFinancePriceAudience')->whereIn('SeasonEventFinancePriceID', $priceIds)->delete();
        DB::table('SeasonEventFinancePrice')->whereIn('SeasonEventFinancePriceID', $priceIds)->delete();
    }

    /**
     * Saved rows in the same shape as the form posts them.
     *
     * @return Collection<int, array{start_date: string, end_date: string, price: int, audience: list<string>}>
     */
    public function forEdit(int $seasonEventId): Collection
    {
        $prices = DB::table('SeasonEventFinancePrice')
            ->where('SeasonEventID', $seasonEventId)
            ->orderBy('StartDate')
            ->orderBy('Price')
            ->get();

        $audiences = DB::table('SeasonEventFinancePriceAudience')
            ->whereIn('SeasonEventFinancePriceID', $prices->pluck('SeasonEventFinancePriceID'))
            ->get()
            ->groupBy('SeasonEventFinancePriceID');

        return $prices->map(fn (object $price) => [
            'start_date' => (string) $price->StartDate,
            'end_date' => (string) $price->EndDate,
            'price' => (int) $price->Price,
            'audience' => $audiences->get($price->SeasonEventFinancePriceID, collect())
                ->map(fn (object $audience) => $this->audienceKey($audience))
                ->values()
                ->all(),
        ])->values();
    }

    public function audienceLabel(string $key, array $sectors): string
    {
        return match ($key) {
            self::FAMILY => __('Families'),
            self::GUEST => __('Guests'),
            default => (string) ($sectors[(int) substr($key, 2)] ?? $key),
        };
    }

    /**
     * Client audience values → deduplicated keys; only FAMILY, GUEST and this event's sectors are allowed.
     *
     * @param  array<int, string>  $sectors
     * @return list<string>
     */
    private function normaliseAudience(mixed $raw, array $sectors): array
    {
        if ($raw === null || $raw === '' || $raw === []) {
            throw new InvalidArgumentException(__('Each price interval must apply to at least one sector, families, or guests.'));
        }

        $invalid = new InvalidArgumentException(__('Price interval audience is invalid.'));
        if (! is_array($raw)) {
            throw $invalid;
        }

        $keys = [];
        foreach ($raw as $value) {
            if (! is_string($value) && ! is_int($value)) {
                throw $invalid;
            }

            $value = trim((string) $value);
            if ($value === self::FAMILY || $value === self::GUEST) {
                $keys[$value] = true;
            } elseif (preg_match('/^Q:(\d{1,10})$/', $value, $matches) && isset($sectors[(int) $matches[1]])) {
                $keys['Q:'.(int) $matches[1]] = true;
            } else {
                throw $invalid;
            }
        }

        return array_keys($keys);
    }

    /**
     * @param  list<array{Audience: list<string>}>  $intervals
     * @return list<string>
     */
    private function audienceKeys(array $intervals): array
    {
        $keys = [];
        foreach ($intervals as $interval) {
            foreach ($interval['Audience'] as $key) {
                $keys[$key] = true;
            }
        }

        return array_keys($keys);
    }

    /**
     * @return array{SeasonEventFinancePriceID: int, AudienceType: string, QetaaID: int|null}
     */
    private function audienceRow(int $priceId, string $key): array
    {
        if ($key === self::FAMILY || $key === self::GUEST) {
            return ['SeasonEventFinancePriceID' => $priceId, 'AudienceType' => $key, 'QetaaID' => null];
        }

        return ['SeasonEventFinancePriceID' => $priceId, 'AudienceType' => self::QETAA, 'QetaaID' => (int) substr($key, 2)];
    }

    private function audienceKey(object $audience): string
    {
        return $audience->AudienceType === self::QETAA
            ? 'Q:'.(int) $audience->QetaaID
            : (string) $audience->AudienceType;
    }
}
