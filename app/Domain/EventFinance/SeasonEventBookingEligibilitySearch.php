<?php

namespace App\Domain\EventFinance;

use App\Support\LikeSearch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Typeahead search for eligible booking entities (persons / guests / families).
 */
class SeasonEventBookingEligibilitySearch
{
    private SeasonEventPriceResolver $prices;

    public function __construct(?SeasonEventPriceResolver $prices = null)
    {
        $this->prices = $prices ?? new SeasonEventPriceResolver;
    }

    public function searchGuests(int $seasonEventId, ?string $term, int $limit = 20): Collection
    {
        $fields = LikeSearch::namedPartyFields('g', 'GuestID');

        return DB::table('Guests as g')
            ->when($term !== null, function ($query) use ($term, $fields) {
                $query->where(function ($sub) use ($term, $fields) {
                    LikeSearch::applyOr($sub, $term, $fields['columns'], $fields['raw']);
                });
            })
            ->select(
                'g.GuestID',
                'g.MobileNumber as PersonPersonalMobileNumber',
                DB::raw("
                TRIM(CONCAT(
                    COALESCE(g.FirstName,''), ' ',
                    COALESCE(g.SecondName,''), ' ',
                    COALESCE(g.ThirdName,''), ' ',
                    COALESCE(g.FourthName,'')
                )) as PersonFullName
            "),
                DB::raw("'ضيوف' as QetaaNames"),
                DB::raw('0 as IsBlacklisted'),
                DB::raw('0 as IsSpecialCase'),
                DB::raw('
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM SeasonEventParticipantFinance b
                    WHERE b.SeasonEventID = '.(int) $seasonEventId.'
                    AND b.GuestID = g.GuestID
                ) THEN 1 ELSE 0 END as AlreadyBooked
            ')
            )
            ->orderBy('PersonFullName')
            ->limit($limit)
            ->get();
    }

    public function searchFamilies(int $seasonEventId, ?string $term, int $limit = 20): Collection
    {
        $fields = LikeSearch::namedPartyFields('f', 'FamilyID');

        return DB::table('FamilyMembers as f')
            ->when($term !== null, function ($query) use ($term, $fields) {
                $query->where(function ($sub) use ($term, $fields) {
                    LikeSearch::applyOr($sub, $term, $fields['columns'], $fields['raw']);
                });
            })
            ->select(
                'f.FamilyID',
                'f.MobileNumber as PersonPersonalMobileNumber',
                DB::raw("
                TRIM(CONCAT(
                    COALESCE(f.FirstName,''), ' ',
                    COALESCE(f.SecondName,''), ' ',
                    COALESCE(f.ThirdName,''), ' ',
                    COALESCE(f.FourthName,'')
                )) as PersonFullName
            "),
                DB::raw("'اهالي' as QetaaNames"),
                DB::raw('0 as IsBlacklisted'),
                DB::raw('0 as IsSpecialCase'),
                DB::raw('
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM SeasonEventParticipantFinance b
                    WHERE b.SeasonEventID = '.(int) $seasonEventId.'
                    AND b.FamilyID = f.FamilyID
                ) THEN 1 ELSE 0 END as AlreadyBooked
            ')
            )
            ->orderBy('PersonFullName')
            ->limit($limit)
            ->get();
    }

    public function searchPersons(int $seasonEventId, ?string $term, int $limit = 20): Collection
    {
        $event = DB::table('SeasonEvent')->where('SeasonEventID', $seasonEventId)->first();
        if (! $event) {
            return collect();
        }

        $eligibleQetaaIDs = DB::table('EventQetaa')
            ->where('EventID', $event->EventID)
            ->pluck('QetaaID')
            ->toArray();

        if (empty($eligibleQetaaIDs)) {
            return collect();
        }

        // The person booking form always prices by today's date.
        $sectorPrices = $this->prices->sectorPrices($seasonEventId, now()->toDateString());

        return DB::table('PersonInformation as p')
            ->join('PersonQetaa as pq', 'p.PersonID', '=', 'pq.PersonID')
            ->join('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
            ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
            ->leftJoin('PersonBlackList as pb', 'p.PersonID', '=', 'pb.PersonID')
            ->leftJoin('PersonSpecialCase as psc', 'p.PersonID', '=', 'psc.PersonID')
            ->whereIn('pq.QetaaID', $eligibleQetaaIDs)
            ->when($term !== null, function ($query) use ($term) {
                $query->where(function ($sub) use ($term) {
                    LikeSearch::applyFlexiblePersonMatch($sub, $term, 'p', 'ppn');
                });
            })
            ->select(
                'p.PersonID',
                'ppn.PersonPersonalMobileNumber',
                DB::raw("TRIM(CONCAT(
                    COALESCE(p.FirstName,''), ' ',
                    COALESCE(p.SecondName,''), ' ',
                    COALESCE(p.ThirdName,''), ' ',
                    COALESCE(p.FourthName,'')
                )) as PersonFullName"),
                DB::raw("GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ' , ') as QetaaNames"),
                DB::raw('GROUP_CONCAT(DISTINCT pq.QetaaID) as QetaaIDs'),
                DB::raw('CASE WHEN COUNT(DISTINCT pb.BlackListID) > 0 THEN 1 ELSE 0 END as IsBlacklisted'),
                DB::raw('CASE WHEN COUNT(DISTINCT psc.SpecialCaseID) > 0 THEN 1 ELSE 0 END as IsSpecialCase'),
                DB::raw('CASE WHEN EXISTS (
                    SELECT 1 FROM SeasonEventParticipantFinance b
                    WHERE b.SeasonEventID = '.(int) $seasonEventId.'
                    AND b.PersonID = p.PersonID
                ) THEN 1 ELSE 0 END as AlreadyBooked')
            )
            ->groupBy(
                'p.PersonID',
                'ppn.PersonPersonalMobileNumber',
                'p.FirstName',
                'p.SecondName',
                'p.ThirdName',
                'p.FourthName'
            )
            ->orderBy('PersonFullName')
            ->limit($limit)
            ->get()
            ->map(fn (object $person) => $this->withResolvedPrice($person, $sectorPrices));
    }

    /**
     * Price the person would pay today, from their eligible sectors; null when the plan has none.
     *
     * @param  array<int, int>  $sectorPrices  QetaaID => Price
     */
    private function withResolvedPrice(object $person, array $sectorPrices): object
    {
        $qetaaIds = array_map('intval', array_filter(explode(',', (string) ($person->QetaaIDs ?? ''))));
        $person->ResolvedPrice = $this->prices->cheapestSectorPrice($sectorPrices, $qetaaIds);
        unset($person->QetaaIDs);

        return $person;
    }
}
