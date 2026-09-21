<?php

namespace App\Domain\Person;

use App\Support\LikeSearch;
use App\Support\PersonAvatar;
use App\Support\SqlPaginator;
use App\Support\TableColumnFilters;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Optional 1:1 PersonFolar assignment, scoped like the served directory.
 */
class PersonFolarService
{
    /**
     * @param  array<string, string>  $columnFilters
     */
    public function paginateForAssign(
        int $viewerId,
        ?string $term = null,
        array $columnFilters = [],
        int $perPage = 25,
    ): LengthAwarePaginator {
        [$scopedFrom, $bindings] = $this->scopedFromSql($viewerId, $term, $columnFilters);

        $sql = '
            SELECT
                pi.PersonID,
                pi.FirstName,
                pi.SecondName,
                pi.ThirdName,
                pi.FourthName,
                pi.Gender,
                (
                    SELECT q2.QetaaName
                    FROM PersonQetaa pq2
                    JOIN Qetaa q2 ON q2.QetaaID = pq2.QetaaID
                    WHERE pq2.PersonID = pi.PersonID
                    LIMIT 1
                ) AS QetaaName,
                (
                    SELECT sm2.SanaMarhalaName
                    FROM PersonSanaMarhala psm2
                    JOIN SanaMarhala sm2 ON sm2.SanaMarhalaID = psm2.SanaMarhalaID
                    WHERE psm2.PersonID = pi.PersonID
                    LIMIT 1
                ) AS SanaMarhalaName,
                pf.FolarID,
                (
                    SELECT img.PersonSystemImageThumbnailPath
                    FROM PersonImages img
                    WHERE img.PersonID = pi.PersonID
                    LIMIT 1
                ) AS PersonSystemImageThumbnailPath
            FROM PersonInformation pi
            INNER JOIN (
                SELECT DISTINCT pi.PersonID
                '.$scopedFrom.'
            ) scoped ON scoped.PersonID = pi.PersonID
            LEFT JOIN PersonFolar pf ON pf.PersonID = pi.PersonID
            ORDER BY pi.FirstName ASC, pi.PersonID ASC
        ';

        $countSql = '
            SELECT COUNT(*) AS aggregate FROM (
                SELECT DISTINCT pi.PersonID
                '.$scopedFrom.'
            ) AS pagination_count_sub
        ';

        return SqlPaginator::paginate($sql, $bindings, $perPage, $countSql)
            ->through(function ($person) {
                $person->full_name = trim(implode(' ', array_filter([
                    $person->FirstName,
                    $person->SecondName,
                    $person->ThirdName,
                    $person->FourthName ?? null,
                ])));
                $person->photo_url = PersonAvatar::photoUrl($person->PersonSystemImageThumbnailPath ?? null)
                    ?? PersonAvatar::defaultUrl($person->Gender ?? null);

                return $person;
            });
    }

    public function nameFor(int $personId): ?string
    {
        $name = DB::table('PersonFolar')
            ->join('Folar', 'Folar.FolarID', '=', 'PersonFolar.FolarID')
            ->where('PersonFolar.PersonID', $personId)
            ->value('Folar.FolarName');

        return is_string($name) && $name !== '' ? $name : null;
    }

    public function idFor(int $personId): ?int
    {
        $id = DB::table('PersonFolar')
            ->join('Folar', 'Folar.FolarID', '=', 'PersonFolar.FolarID')
            ->where('PersonFolar.PersonID', $personId)
            ->value('Folar.FolarID');

        return $id !== null ? (int) $id : null;
    }

    /**
     * @param  array<int|string, mixed>  $folarByPersonId
     */
    public function syncAssignments(int $viewerId, array $folarByPersonId): void
    {
        if (count($folarByPersonId) > 2000) {
            throw ValidationException::withMessages([
                'folar' => __('Too many scarf assignments in one save.'),
            ]);
        }

        $allowed = $this->allowedPersonIdSet($viewerId);
        $validFolarIds = array_flip(
            DB::table('Folar')->pluck('FolarID')->map(fn ($id) => (int) $id)->all()
        );

        $normalized = [];
        foreach ($folarByPersonId as $personId => $folarId) {
            $pid = (int) $personId;
            if ($pid <= 0 || ! isset($allowed[$pid])) {
                throw ValidationException::withMessages([
                    'folar' => __('You cannot assign a scarf outside your served members.'),
                ]);
            }

            if ($folarId === null || $folarId === '') {
                $normalized[$pid] = null;

                continue;
            }

            if (! is_numeric($folarId) || (int) $folarId != $folarId) {
                throw ValidationException::withMessages([
                    'folar' => __('Choose scout scarf'),
                ]);
            }

            $fid = (int) $folarId;
            if (! isset($validFolarIds[$fid])) {
                throw ValidationException::withMessages([
                    'folar' => __('Choose scout scarf'),
                ]);
            }

            $normalized[$pid] = $fid;
        }

        DB::transaction(function () use ($normalized) {
            foreach ($normalized as $personId => $folarId) {
                $this->assignOne($personId, $folarId);
            }
        });
    }

    /**
     * @return array{0: string, 1: list<mixed>}
     */
    private function scopedFromSql(int $viewerId, ?string $term, array $columnFilters): array
    {
        $bindings = [$viewerId];
        $where = [];

        if ($term !== null) {
            $fragment = LikeSearch::sqlFlexibleOr([
                'CAST(pi.PersonID AS CHAR)',
                'pi.ShamandoraCode',
                'pi.FirstName',
                'pi.SecondName',
                'pi.ThirdName',
                'pi.FourthName',
                "CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName)",
            ], $term, []);
            $where[] = $fragment['sql'];
            $bindings = array_merge($bindings, $fragment['bindings']);
        }

        $filterFrag = TableColumnFilters::sqlEquals($columnFilters, [
            'SanaMarhalaName' => 'sm.SanaMarhalaName',
        ]);
        if ($filterFrag['sql'] !== '') {
            $where[] = $filterFrag['sql'];
            $bindings = array_merge($bindings, $filterFrag['bindings']);
        }

        $extraWhere = $where === [] ? '' : (' AND '.implode(' AND ', $where));

        $from = '
            FROM PersonInformation pi
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
            LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
            LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
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
            '.$extraWhere;

        return [$from, $bindings];
    }

    /** Caller must authorize the person and validate $folarId against Folar. */
    public function assignOne(int $personId, mixed $folarId): void
    {
        if ($folarId !== null && $folarId !== '') {
            DB::table('PersonFolar')->updateOrInsert(
                ['PersonID' => $personId],
                ['FolarID' => (int) $folarId]
            );

            return;
        }

        DB::table('PersonFolar')->where('PersonID', $personId)->delete();
    }

    /**
     * @return array<int, true>
     */
    private function allowedPersonIdSet(int $viewerId): array
    {
        [$from, $bindings] = $this->scopedFromSql($viewerId, null, []);

        $ids = DB::select('SELECT DISTINCT pi.PersonID '.$from, $bindings);

        $set = [];
        foreach ($ids as $row) {
            $set[(int) $row->PersonID] = true;
        }

        return $set;
    }
}
