<?php

namespace App\Console\Commands;

use App\Domain\EventFinance\SeasonEventBookingService;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Copy Person bookings from one season event onto another as amount-0 rows
 * so they appear in reservation attendance. Insert-only; never updates money.
 */
class InjectSeasonEventPersonsCommand extends Command
{
    protected $signature = 'event-finance:inject-persons
        {fromSeasonEventId : Source SeasonEventID}
        {toSeasonEventId : Target SeasonEventID}
        {--execute : Write inserts (default is dry-run)}
        {--dry-run : Report without writing (wins over --execute)}
        {--servent-id= : PersonID recorded as servant on new bookings (required with --execute)}';

    protected $description = 'Copy Person bookings onto another event as amount-0 attendance rows. Skips existing PersonIDs. Does not send WhatsApp or create payments.';

    public function handle(SeasonEventBookingService $bookings): int
    {
        $from = (int) $this->argument('fromSeasonEventId');
        $to = (int) $this->argument('toSeasonEventId');
        $execute = (bool) $this->option('execute') && ! $this->option('dry-run');
        $serventId = (int) ($this->option('servent-id') ?: 0);

        if ($from <= 0 || $to <= 0) {
            $this->error('fromSeasonEventId and toSeasonEventId must be positive integers.');

            return self::FAILURE;
        }

        if ($from === $to) {
            $this->error('Source and target SeasonEventID must be different.');

            return self::FAILURE;
        }

        $database = (string) (config('database.connections.'.config('database.default').'.database') ?: config('database.default'));
        $this->info("Database: {$database}");

        $fromEvent = $bookings->getEventInfo($from);
        $toEvent = $bookings->getEventInfo($to);
        if (! $fromEvent || ! $toEvent) {
            $this->error(
                ! $fromEvent
                    ? "SeasonEvent {$from} not found."
                    : "SeasonEvent {$to} not found."
            );

            return self::FAILURE;
        }

        $this->table(
            ['SeasonEventID', 'EventName', 'TakesReservation'],
            [
                [$from, $fromEvent->EventName, (int) $fromEvent->TakesReservation],
                [$to, $toEvent->EventName, (int) $toEvent->TakesReservation],
            ]
        );

        if (empty($toEvent->TakesReservation)) {
            $this->error("Target SeasonEvent {$to} does not take reservations; bookings would not appear in attendance.");

            return self::FAILURE;
        }

        if (! $this->hasSeasonEventPersonUnique()) {
            $this->error('Missing unique (SeasonEventID, PersonID) on SeasonEventParticipantFinance. Aborting.');

            return self::FAILURE;
        }

        if ($execute) {
            if ($serventId <= 0) {
                $this->error('--servent-id is required with --execute.');

                return self::FAILURE;
            }
            if (! Schema::hasTable('PersonInformation')
                || ! DB::table('PersonInformation')->where('PersonID', $serventId)->exists()) {
                $this->error("Servent PersonID {$serventId} was not found in PersonInformation.");

                return self::FAILURE;
            }
        }

        $this->printPlanSummary($from, $to);

        if (! $execute) {
            $this->info('Dry-run only. Pass --execute --servent-id=<PersonID> to write.');

            return self::SUCCESS;
        }

        return $this->insertMissingPersons($from, $to, $serventId);
    }

    private function printPlanSummary(int $from, int $to): void
    {
        $sourcePersonIds = DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventID', $from)
            ->whereNotNull('PersonID')
            ->distinct()
            ->pluck('PersonID')
            ->map(fn ($id) => (int) $id)
            ->all();

        $alreadyOnTarget = $sourcePersonIds === []
            ? collect()
            : DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventID', $to)
                ->whereNotNull('PersonID')
                ->whereIn('PersonID', $sourcePersonIds)
                ->get(['PersonID', 'IsRefunded']);

        $alreadyIds = $alreadyOnTarget->pluck('PersonID')->map(fn ($id) => (int) $id)->all();
        $blockedRefundedOnTarget = $alreadyOnTarget->where('IsRefunded', 1)->count();
        $toInsertIds = array_values(array_diff($sourcePersonIds, $alreadyIds));

        $guestsFamiliesIgnored = (int) DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventID', $from)
            ->whereNull('PersonID')
            ->count();

        $sourceRefundedIncluded = (int) DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventID', $from)
            ->whereNotNull('PersonID')
            ->where('IsRefunded', 1)
            ->distinct()
            ->count('PersonID');

        $this->table(
            ['Bucket', 'Count'],
            [
                ['Source persons', count($sourcePersonIds)],
                ['Already on target (skipped)', count($alreadyIds)],
                ['Blocked: refunded on target (subset of skipped)', $blockedRefundedOnTarget],
                ['Source refunded (still injected if missing)', $sourceRefundedIncluded],
                ['Guests/families ignored', $guestsFamiliesIgnored],
                ['Would insert', count($toInsertIds)],
            ]
        );
    }

    private function insertMissingPersons(int $from, int $to, int $serventId): int
    {
        $notes = "Injected from SeasonEvent {$from} for attendance";
        $now = now()->format('Y-m-d H:i:s');
        $before = (int) DB::table('SeasonEventParticipantFinance')->where('SeasonEventID', $to)->count();

        try {
            DB::transaction(function () use ($from, $to, $serventId, $now, $notes) {
                DB::insert(
                    'INSERT INTO SeasonEventParticipantFinance (
                        SeasonEventID, PersonID, GuestID, FamilyID, ServentID, FirstPaymentDate,
                        OriginalPrice, DiscountAmount, FinalRequiredAmount, SpecialCaseType, SpecialCaseNote,
                        HasPersonSpecialCase, LockedPrice, IsRefunded, RefundDate, InstallmentsNumber,
                        AmountPaid, RemainingAmount, ShirtSize, Notes
                    )
                    SELECT
                        ?, p.PersonID, NULL, NULL, ?, ?,
                        0, 0, 0, ?, NULL,
                        0, 0, 0, NULL, 1,
                        0, 0, NULL, ?
                    FROM (
                        SELECT DISTINCT PersonID
                        FROM SeasonEventParticipantFinance
                        WHERE SeasonEventID = ?
                          AND PersonID IS NOT NULL
                    ) p
                    WHERE NOT EXISTS (
                        SELECT 1
                        FROM SeasonEventParticipantFinance dst
                        WHERE dst.SeasonEventID = ?
                          AND dst.PersonID = p.PersonID
                    )',
                    [$to, $serventId, $now, 'NONE', $notes, $from, $to]
                );
            });
        } catch (QueryException $e) {
            report($e);
            $this->error('Insert failed (possible unique conflict). No rows were committed.');

            return self::FAILURE;
        }

        $inserted = (int) DB::table('SeasonEventParticipantFinance')->where('SeasonEventID', $to)->count() - $before;
        $this->info("Inserted {$inserted} amount-0 person booking(s) onto SeasonEvent {$to}.");

        return self::SUCCESS;
    }

    private function hasSeasonEventPersonUnique(): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('SeasonEventParticipantFinance')");
            foreach ($indexes as $index) {
                if ((int) $index->unique !== 1) {
                    continue;
                }
                $cols = collect(DB::select('PRAGMA index_info('.$this->sqliteIdent((string) $index->name).')'))
                    ->pluck('name')
                    ->all();
                sort($cols);
                if ($cols === ['PersonID', 'SeasonEventID']) {
                    return true;
                }
            }

            return false;
        }

        $rows = DB::select('SHOW INDEX FROM SeasonEventParticipantFinance WHERE Non_unique = 0');
        $byKey = [];
        foreach ($rows as $row) {
            $byKey[$row->Key_name][] = $row->Column_name;
        }
        foreach ($byKey as $cols) {
            sort($cols);
            if ($cols === ['PersonID', 'SeasonEventID']) {
                return true;
            }
        }

        return false;
    }

    private function sqliteIdent(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }
}
