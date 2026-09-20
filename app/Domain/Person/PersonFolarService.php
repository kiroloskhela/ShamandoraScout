<?php

namespace App\Domain\Person;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Optional 1:1 PersonFolar assignment, scoped like the served directory.
 */
class PersonFolarService
{
    public function listForAssign(int $viewerId): Collection
    {
        $rows = DB::select('
            SELECT DISTINCT
                pi.PersonID,
                pi.FirstName,
                pi.SecondName,
                pi.ThirdName,
                pi.FourthName,
                q.QetaaName,
                sm.SanaMarhalaName,
                pf.FolarID
            FROM PersonInformation pi
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
            LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
            LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
            LEFT JOIN PersonFolar pf ON pf.PersonID = pi.PersonID
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
            ORDER BY pi.FirstName ASC, pi.PersonID ASC
        ', [$viewerId]);

        return collect($rows)->unique('PersonID')->values()->map(function ($person) {
            $person->full_name = trim(implode(' ', array_filter([
                $person->FirstName,
                $person->SecondName,
                $person->ThirdName,
                $person->FourthName ?? null,
            ])));

            return $person;
        });
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

    private function assignOne(int $personId, mixed $folarId): void
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
        $ids = DB::select('
            SELECT DISTINCT pi.PersonID
            FROM PersonInformation pi
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
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
        ', [$viewerId]);

        $set = [];
        foreach ($ids as $row) {
            $set[(int) $row->PersonID] = true;
        }

        return $set;
    }
}
