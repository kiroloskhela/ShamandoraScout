<?php

namespace App\Domain\EventFinance;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the booking price of a season event from (date × who is booking).
 *
 * A price row applies only through its audience rows; a row with no audience
 * never matches. When several rows match, the cheapest wins.
 */
class SeasonEventPriceResolver
{
    /**
     * Price for a person on the given date: their sectors ∩ the event's sectors.
     */
    public function personPrice(int $seasonEventId, string $date, int $personId): ?int
    {
        $qetaaIds = DB::table('PersonQetaa as pq')
            ->join('EventQetaa as eq', 'pq.QetaaID', '=', 'eq.QetaaID')
            ->join('SeasonEvent as se', 'eq.EventID', '=', 'se.EventID')
            ->where('se.SeasonEventID', $seasonEventId)
            ->where('pq.PersonID', $personId)
            ->distinct()
            ->pluck('pq.QetaaID')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $this->cheapestSectorPrice($this->sectorPrices($seasonEventId, $date), $qetaaIds);
    }

    /**
     * Price for FAMILY or GUEST bookings on the given date.
     */
    public function audiencePrice(int $seasonEventId, string $date, string $audienceType): ?int
    {
        $price = $this->validOn($seasonEventId, $date)
            ->join('SeasonEventFinancePriceAudience as a', 'a.SeasonEventFinancePriceID', '=', 'p.SeasonEventFinancePriceID')
            ->where('a.AudienceType', $audienceType)
            ->min('p.Price');

        return $price === null ? null : (int) $price;
    }

    /**
     * Cheapest price per sector valid on the given date.
     *
     * @return array<int, int> QetaaID => Price
     */
    public function sectorPrices(int $seasonEventId, string $date): array
    {
        return $this->validOn($seasonEventId, $date)
            ->join('SeasonEventFinancePriceAudience as a', 'a.SeasonEventFinancePriceID', '=', 'p.SeasonEventFinancePriceID')
            ->where('a.AudienceType', FinancePlanIntervals::QETAA)
            ->groupBy('a.QetaaID')
            ->selectRaw('a.QetaaID as QetaaID, MIN(p.Price) as Price')
            ->get()
            ->mapWithKeys(fn (object $row) => [(int) $row->QetaaID => (int) $row->Price])
            ->all();
    }

    /**
     * @param  array<int, int>  $sectorPrices  QetaaID => Price
     * @param  list<int>  $qetaaIds
     */
    public function cheapestSectorPrice(array $sectorPrices, array $qetaaIds): ?int
    {
        // Normally exactly one sector is priced for a person; if several are, the cheapest wins deterministically.
        $prices = array_intersect_key($sectorPrices, array_flip($qetaaIds));

        return $prices === [] ? null : min($prices);
    }

    /**
     * All intervals of one audience type, for client-side display on the booking form.
     *
     * @return Collection<int, object{StartDate: string, EndDate: string, Price: int}>
     */
    public function audienceIntervals(int $seasonEventId, string $audienceType): Collection
    {
        return DB::table('SeasonEventFinancePrice as p')
            ->join('SeasonEventFinancePriceAudience as a', 'a.SeasonEventFinancePriceID', '=', 'p.SeasonEventFinancePriceID')
            ->where('p.SeasonEventID', $seasonEventId)
            ->where('a.AudienceType', $audienceType)
            ->orderBy('p.StartDate')
            ->orderBy('p.Price')
            ->get(['p.StartDate', 'p.EndDate', 'p.Price'])
            ->map(function (object $row) {
                $row->Price = (int) $row->Price;

                return $row;
            });
    }

    private function validOn(int $seasonEventId, string $date): Builder
    {
        return DB::table('SeasonEventFinancePrice as p')
            ->where('p.SeasonEventID', $seasonEventId)
            ->where('p.StartDate', '<=', $date)
            ->where('p.EndDate', '>=', $date);
    }
}
