<?php

namespace App\Domain\Person;

use Illuminate\Support\Facades\DB;

class PersonApiQueryService
{
    public function personsVisibleTo(int $userId)
    {
        return DB::table('PersonInformation as pi')
            ->leftJoin('PersonEntryQuestions as peq', 'pi.PersonID', '=', 'peq.PersonID')
            ->leftJoin('PersonSanaMarhala as psm', 'pi.PersonID', '=', 'psm.PersonID')
            ->leftJoin('SanaMarhala as sm', 'sm.SanaMarhalaID', '=', 'psm.SanaMarhalaID')
            ->leftJoin('PersonQetaa as pq', 'pi.PersonID', '=', 'pq.PersonID')
            ->leftJoin('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
            ->leftJoin('PersonPhoneNumbers as ppn', 'pi.PersonID', '=', 'ppn.PersonID')
            ->leftJoin('PersonGroup as pg_main', 'pg_main.PersonID', '=', 'pi.PersonID')
            ->leftJoin('PersonImages as pi_img', 'pi_img.PersonID', '=', 'pi.PersonID')
            ->join('GroupQetaa as gq', 'gq.QetaaID', '=', 'q.QetaaID')
            ->join('PersonGroup as pg2', 'pg2.GroupID', '=', 'gq.GroupID')
            ->where('pg2.PersonID', $userId)
            ->select([
                'pi.PersonID',
                'pi.ShamandoraCode',
                'pi.FirstName',
                'pi.SecondName',
                'pi.ThirdName',
                'pi.FourthName',
                'q.QetaaName',
                'pi.ScoutJoiningYear',
                'sm.SanaMarhalaName',
                'pi.RaqamQawmy',
                'ppn.PersonPersonalMobileNumber',
                'q.QetaaID',
                'pi_img.PersonSystemImagePath',
                DB::raw('pg_main.PersonID AS GroupPersonID'),
                DB::raw("IF(peq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions"),
                'psm.SanaMarhalaID',
            ])
            ->distinct()
            ->orderBy('pi.ShamandoraCode', 'asc')
            ->get()
            ->map(function ($person) {
                $person->full_name = trim(
                    "{$person->FirstName} {$person->SecondName} {$person->ThirdName} {$person->FourthName}"
                );

                return $person;
            });
    }

    /**
     * Same sector/group scope as {@see personsVisibleTo()} / GET /api/show-persons.
     */
    public function isVisibleTo(int $viewerId, int $personId): bool
    {
        if ($viewerId <= 0 || $personId <= 0) {
            return false;
        }

        return DB::table('PersonQetaa as pq')
            ->join('GroupQetaa as gq', 'gq.QetaaID', '=', 'pq.QetaaID')
            ->join('PersonGroup as pg2', 'pg2.GroupID', '=', 'gq.GroupID')
            ->where('pg2.PersonID', $viewerId)
            ->where('pq.PersonID', $personId)
            ->exists();
    }
}
