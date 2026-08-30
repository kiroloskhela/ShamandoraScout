<?php

namespace App\Domain\Person;

use App\Support\LikeSearch;
use App\Support\SqlPaginator;
use App\Support\TableColumnFilters;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Person list + typeahead search shared across web controllers.
 * Controllers pass auth/scoping constraints; matching uses LikeSearch.
 */
class PersonSearchService
{
    /** @var array<string, string> */
    private const DIRECTORY_FILTER_COLUMNS = [
        'SanaMarhalaName' => 'sm.SanaMarhalaName',
        'QetaaName' => 'q.QetaaName',
    ];

    /**
     * SuperAdmin all-persons directory (optional text + column filters).
     *
     * @param  array<string, string>  $columnFilters
     */
    public function paginateAllPersons(?string $term, array $columnFilters = [], int $perPage = 25): LengthAwarePaginator
    {
        $bindings = [];
        $whereParts = [];

        if ($term !== null) {
            $fragment = LikeSearch::sqlFlexibleOr(
                LikeSearch::personDirectoryColumns(),
                $term,
                LikeSearch::personPhoneColumns(),
            );
            $whereParts[] = $fragment['sql'];
            $bindings = array_merge($bindings, $fragment['bindings']);
        }

        $filterFrag = TableColumnFilters::sqlEquals($columnFilters, self::DIRECTORY_FILTER_COLUMNS);
        if ($filterFrag['sql'] !== '') {
            $whereParts[] = $filterFrag['sql'];
            $bindings = array_merge($bindings, $filterFrag['bindings']);
        }

        $searchSql = $whereParts === [] ? '' : (' WHERE '.implode(' AND ', $whereParts));
        $fromSql = $this->directoryFromSql().$searchSql;

        return $this->paginateDirectory($fromSql, $bindings, $perPage);
    }

    /**
     * Scoped directory for the authenticated person's groups.
     *
     * @param  array<string, string>  $columnFilters
     */
    public function paginateScopedToPerson(int $userId, ?string $term = null, array $columnFilters = [], int $perPage = 25): LengthAwarePaginator
    {
        $bindings = [$userId];
        $searchParts = [];

        if ($term !== null) {
            $fragment = LikeSearch::sqlFlexibleOr(
                LikeSearch::personDirectoryColumns(),
                $term,
                LikeSearch::personPhoneColumns(),
            );
            $searchParts[] = $fragment['sql'];
            $bindings = array_merge($bindings, $fragment['bindings']);
        }

        $filterFrag = TableColumnFilters::sqlEquals($columnFilters, self::DIRECTORY_FILTER_COLUMNS);
        if ($filterFrag['sql'] !== '') {
            $searchParts[] = $filterFrag['sql'];
            $bindings = array_merge($bindings, $filterFrag['bindings']);
        }

        $searchSql = $searchParts === [] ? '' : (' AND '.implode(' AND ', $searchParts));
        $fromSql = $this->directoryFromSql().'
            JOIN GroupQetaa gq ON gq.QetaaID = q.QetaaID
            JOIN PersonGroup pg2 ON pg2.GroupID = gq.GroupID
            WHERE q.QetaaID IN (
                SELECT gq2.QetaaID
                FROM GroupQetaa gq2
                WHERE gq2.GroupID IN (
                    SELECT pg3.GroupID
                    FROM PersonGroup pg3
                    WHERE pg3.PersonID = ?
                )
            )
            '.$searchSql;

        return $this->paginateDirectory($fromSql, $bindings, $perPage);
    }

    /**
     * Distinct filter options for directory column filters (full dataset).
     *
     * @return array{SanaMarhalaName: list<string>, QetaaName: list<string>}
     */
    public function directoryFilterOptions(?int $scopedUserId = null): array
    {
        if ($scopedUserId !== null) {
            $scope = '
                AND q.QetaaID IN (
                    SELECT gq2.QetaaID
                    FROM GroupQetaa gq2
                    WHERE gq2.GroupID IN (
                        SELECT pg3.GroupID FROM PersonGroup pg3 WHERE pg3.PersonID = ?
                    )
                )
            ';
            $bindings = [$scopedUserId];

            $stages = DB::select("
                SELECT DISTINCT sm.SanaMarhalaName AS v
                FROM PersonInformation pi
                LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
                LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
                LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
                LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
                WHERE sm.SanaMarhalaName IS NOT NULL AND sm.SanaMarhalaName <> ''
                {$scope}
                ORDER BY sm.SanaMarhalaName
            ", $bindings);

            $sectors = DB::select("
                SELECT DISTINCT q.QetaaName AS v
                FROM PersonInformation pi
                LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
                LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
                WHERE q.QetaaName IS NOT NULL AND q.QetaaName <> ''
                {$scope}
                ORDER BY q.QetaaName
            ", $bindings);
        } else {
            $stages = DB::select("
                SELECT DISTINCT SanaMarhalaName AS v FROM SanaMarhala
                WHERE SanaMarhalaName IS NOT NULL AND SanaMarhalaName <> ''
                ORDER BY SanaMarhalaName
            ");
            $sectors = DB::select("
                SELECT DISTINCT QetaaName AS v FROM Qetaa
                WHERE QetaaName IS NOT NULL AND QetaaName <> ''
                ORDER BY QetaaName
            ");
        }

        return [
            'SanaMarhalaName' => array_values(array_map(fn ($r) => (string) $r->v, $stages)),
            'QetaaName' => array_values(array_map(fn ($r) => (string) $r->v, $sectors)),
        ];
    }

    /**
     * Typeahead: word-mode names OR identity/code/id/phones (personal + parents).
     */
    public function typeaheadByNameOrIdentity(?string $term, int $limit = 15): Collection
    {
        if ($term === null) {
            return collect();
        }

        $likePrefix = $term.'%';

        $results = DB::table('PersonInformation as pi')
            ->leftJoin('PersonPhoneNumbers as ppn', 'pi.PersonID', '=', 'ppn.PersonID')
            ->leftJoin('PersonQetaa as pq', 'pi.PersonID', '=', 'pq.PersonID')
            ->leftJoin('Qetaa as qt', 'pq.QetaaID', '=', 'qt.QetaaID')
            ->select(
                'pi.PersonID',
                'pi.ShamandoraCode',
                'pi.FirstName',
                'pi.SecondName',
                'pi.ThirdName',
                'pi.FourthName',
                'pi.RaqamQawmy',
                'ppn.PersonPersonalMobileNumber',
                'pq.QetaaID',
                'qt.QetaaName'
            )
            ->where(function ($query) use ($term) {
                LikeSearch::applyFlexiblePersonMatch($query, $term, 'pi', 'ppn');
            })
            ->groupBy(
                'pi.PersonID',
                'pi.ShamandoraCode',
                'pi.FirstName',
                'pi.SecondName',
                'pi.ThirdName',
                'pi.FourthName',
                'pi.RaqamQawmy',
                'ppn.PersonPersonalMobileNumber',
                'pq.QetaaID',
                'qt.QetaaName'
            )
            ->orderByRaw('
                CASE
                    WHEN pi.FirstName   LIKE ? THEN 1
                    WHEN pi.SecondName  LIKE ? THEN 2
                    WHEN pi.ThirdName   LIKE ? THEN 3
                    WHEN pi.RaqamQawmy  LIKE ? THEN 4
                    ELSE 5
                END
            ', [$likePrefix, $likePrefix, $likePrefix, $likePrefix])
            ->limit($limit)
            ->get();

        return $results->map(function ($person) {
            $person->FullName = collect([
                $person->FirstName,
                $person->SecondName,
                $person->ThirdName,
                $person->FourthName,
            ])->filter()->implode(' ');

            return $person;
        });
    }

    /**
     * Medicine / generic identity typeahead with phone join (personal + parents).
     */
    public function typeaheadWithPhone(?string $term, int $limit = 20): Collection
    {
        if ($term === null) {
            return collect();
        }

        $fields = LikeSearch::personIdentityFields('pi', 'ppn');

        return DB::table('PersonInformation as pi')
            ->leftJoin('PersonPhoneNumbers as ppn', 'ppn.PersonID', '=', 'pi.PersonID')
            ->select(
                'pi.PersonID',
                'pi.ShamandoraCode',
                DB::raw('MIN(ppn.PersonPersonalMobileNumber) as PersonPersonalMobileNumber'),
                DB::raw("CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName) as PersonName")
            )
            ->where(function ($query) use ($term, $fields) {
                LikeSearch::applyFlexibleOr(
                    $query,
                    $term,
                    $fields['columns'],
                    $fields['raw'],
                    LikeSearch::personPhoneColumns('ppn'),
                );
            })
            ->groupBy('pi.PersonID', 'pi.ShamandoraCode', 'pi.FirstName', 'pi.SecondName', 'pi.ThirdName', 'pi.FourthName')
            ->orderBy('pi.ShamandoraCode')
            ->limit($limit)
            ->get();
    }

    /**
     * Shared FROM/JOIN for directory lists. Question answers use EXISTS in SELECT
     * so PersonEntryQuestions cannot multiply rows.
     */
    private function directoryFromSql(): string
    {
        return '
            FROM PersonInformation pi
            LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
            LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
            LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
            LEFT JOIN PersonGroup PG ON PG.PersonID = pi.PersonID
        ';
    }

    /**
     * @param  list<mixed>  $bindings
     */
    private function paginateDirectory(string $fromSql, array $bindings, int $perPage): LengthAwarePaginator
    {
        $sql = '
            SELECT DISTINCT
                pi.PersonID,
                pi.ShamandoraCode,
                pi.FirstName,
                pi.SecondName,
                pi.ThirdName,
                pi.FourthName,
                q.QetaaName,
                pi.ScoutJoiningYear,
                sm.SanaMarhalaName,
                pi.RaqamQawmy,
                ppn.PersonPersonalMobileNumber,
                ppn.FatherMobileNumber,
                ppn.MotherMobileNumber,
                q.QetaaID,
                PG.PersonID AS GroupPersonID,
                '.$this->hasAnsweredQuestionsSelect().' AS HasAnsweredQuestions,
                psm.SanaMarhalaID
            '.$fromSql.'
            ORDER BY pi.ShamandoraCode ASC, pi.PersonID ASC
        ';

        $mapper = function ($person) {
            $person->full_name = trim("{$person->FirstName} {$person->SecondName} {$person->ThirdName} {$person->FourthName}");

            return $person;
        };

        $countSql = '
            SELECT COUNT(*) AS aggregate FROM (
                SELECT DISTINCT
                    pi.PersonID,
                    q.QetaaID,
                    psm.SanaMarhalaID,
                    PG.PersonID AS GroupPersonID,
                    ppn.PersonPersonalMobileNumber,
                    ppn.FatherMobileNumber,
                    ppn.MotherMobileNumber
                '.$fromSql.'
            ) AS pagination_count_sub
        ';

        return SqlPaginator::paginate($sql, $bindings, $perPage, $countSql)->through($mapper);
    }

    private function hasAnsweredQuestionsSelect(): string
    {
        return "CASE WHEN EXISTS (SELECT 1 FROM PersonEntryQuestions peq WHERE peq.PersonID = pi.PersonID) THEN 'نعم' ELSE 'لا' END";
    }
}
