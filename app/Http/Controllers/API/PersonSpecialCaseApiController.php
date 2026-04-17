<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonSpecialCaseApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Role Helpers
    |--------------------------------------------------------------------------
    */

    private function authUser()
    {
        return Auth::user();
    }

    private function authPersonId(): ?int
    {
        return optional(Auth::user())->PersonID ?? Auth::id();
    }

    private function isSuperAdmin(): bool
    {
        $user = $this->authUser();

        return $user && $user->role()->where('RoleName', 'SuperAdmin')->exists();
    }

    private function isAdminQetaa(): bool
    {
        $user = $this->authUser();

        return $user && $user->role()->where('RoleName', 'AdminQetaa')->exists();
    }

    private function hasSpecialCaseAccess(): bool
    {
        return $this->isSuperAdmin() || $this->isAdminQetaa();
    }

    private function denyIfUnauthorized()
    {
        if (!$this->authUser()) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        return null;
    }

    private function denyIfNoSpecialCaseAccess()
    {
        if ($deny = $this->denyIfUnauthorized()) {
            return $deny;
        }

        if (!$this->hasSpecialCaseAccess()) {
            return response()->json([
                'ok' => false,
                'message' => 'Forbidden',
            ], 403);
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | SQL Helpers
    |--------------------------------------------------------------------------
    */

    private function allowedPersonsSql()
    {
        return "
            SELECT DISTINCT
                pi.PersonID,
                pi.ShamandoraCode,
                CONCAT(
                    COALESCE(pi.FirstName, ''), ' ',
                    COALESCE(pi.SecondName, ''), ' ',
                    COALESCE(pi.ThirdName, ''), ' ',
                    COALESCE(pi.FourthName, '')
                ) AS PersonName,
                pi.FirstName,
                pi.SecondName,
                pi.ThirdName,
                pi.FourthName,
                q.QetaaName,
                pi.ScoutJoiningYear,
                sm.SanaMarhalaName,
                pi.RaqamQawmy,
                ppn.PersonPersonalMobileNumber,
                q.QetaaID,
                PG.PersonID AS GroupPersonID,
                IF(peq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions,
                psm.SanaMarhalaID
            FROM PersonInformation pi
            LEFT JOIN PersonEntryQuestions peq ON pi.PersonID = peq.PersonID
            LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
            LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
            LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
            LEFT JOIN PersonGroup PG ON PG.PersonID = pi.PersonID
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
        ";
    }

    private function allowedPersonIdsSql()
    {
        return "
            SELECT DISTINCT
                pi.PersonID
            FROM PersonInformation pi
            LEFT JOIN PersonEntryQuestions peq ON pi.PersonID = peq.PersonID
            LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
            LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
            LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
            LEFT JOIN PersonGroup PG ON PG.PersonID = pi.PersonID
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
        ";
    }

    /*
    |--------------------------------------------------------------------------
    | Access Helpers
    |--------------------------------------------------------------------------
    */

    private function allowedPersonExists(int $personId, ?int $userPersonId = null): bool
    {
        if ($this->isSuperAdmin()) {
            return DB::table('PersonInformation')
                ->where('PersonID', $personId)
                ->exists();
        }

        $userPersonId = $userPersonId ?? $this->authPersonId();

        if (!$userPersonId) {
            return false;
        }

        $person = DB::selectOne("
            SELECT PersonID
            FROM (
                {$this->allowedPersonIdsSql()}
            ) allowed_people
            WHERE allowed_people.PersonID = ?
            LIMIT 1
        ", [$userPersonId, $personId]);

        return $person !== null;
    }

    private function getAllowedCase(int $specialCaseId, ?int $userPersonId = null)
    {
        if ($this->isSuperAdmin()) {
            return DB::selectOne("
                SELECT
                    psc.SpecialCaseID,
                    psc.PersonID,
                    psc.ServentID,
                    psc.CaseDate,
                    psc.Note,
                    CONCAT(
                        COALESCE(p.FirstName, ''), ' ',
                        COALESCE(p.SecondName, ''), ' ',
                        COALESCE(p.ThirdName, ''), ' ',
                        COALESCE(p.FourthName, '')
                    ) AS PersonName,
                    CONCAT(
                        COALESCE(s.FirstName, ''), ' ',
                        COALESCE(s.SecondName, ''), ' ',
                        COALESCE(s.ThirdName, ''), ' ',
                        COALESCE(s.FourthName, '')
                    ) AS ServentName
                FROM PersonSpecialCase psc
                LEFT JOIN PersonInformation p ON p.PersonID = psc.PersonID
                LEFT JOIN PersonInformation s ON s.PersonID = psc.ServentID
                WHERE psc.SpecialCaseID = ?
                LIMIT 1
            ", [$specialCaseId]);
        }

        $userPersonId = $userPersonId ?? $this->authPersonId();

        if (!$userPersonId) {
            return null;
        }

        return DB::selectOne("
            SELECT
                psc.SpecialCaseID,
                psc.PersonID,
                psc.ServentID,
                psc.CaseDate,
                psc.Note,
                CONCAT(
                    COALESCE(p.FirstName, ''), ' ',
                    COALESCE(p.SecondName, ''), ' ',
                    COALESCE(p.ThirdName, ''), ' ',
                    COALESCE(p.FourthName, '')
                ) AS PersonName,
                CONCAT(
                    COALESCE(s.FirstName, ''), ' ',
                    COALESCE(s.SecondName, ''), ' ',
                    COALESCE(s.ThirdName, ''), ' ',
                    COALESCE(s.FourthName, '')
                ) AS ServentName
            FROM PersonSpecialCase psc
            LEFT JOIN PersonInformation p ON p.PersonID = psc.PersonID
            LEFT JOIN PersonInformation s ON s.PersonID = psc.ServentID
            WHERE psc.SpecialCaseID = ?
              AND psc.PersonID IN (
                  {$this->allowedPersonIdsSql()}
              )
            LIMIT 1
        ", [$specialCaseId, $userPersonId]);
    }

    /*
    |--------------------------------------------------------------------------
    | API Methods
    |--------------------------------------------------------------------------
    */

    /**
     * GET /api/person-special-cases
     */
    public function index(Request $request)
    {
        if ($deny = $this->denyIfNoSpecialCaseAccess()) {
            return $deny;
        }

        $userPersonId = $this->authPersonId();

        if ($this->isSuperAdmin()) {
            $cases = DB::select("
                SELECT
                    psc.SpecialCaseID,
                    psc.PersonID,
                    psc.ServentID,
                    psc.CaseDate,
                    psc.Note,
                    CONCAT(
                        COALESCE(p.FirstName, ''), ' ',
                        COALESCE(p.SecondName, ''), ' ',
                        COALESCE(p.ThirdName, ''), ' ',
                        COALESCE(p.FourthName, '')
                    ) AS PersonName,
                    CONCAT(
                        COALESCE(s.FirstName, ''), ' ',
                        COALESCE(s.SecondName, ''), ' ',
                        COALESCE(s.ThirdName, ''), ' ',
                        COALESCE(s.FourthName, '')
                    ) AS ServentName
                FROM PersonSpecialCase psc
                LEFT JOIN PersonInformation p ON p.PersonID = psc.PersonID
                LEFT JOIN PersonInformation s ON s.PersonID = psc.ServentID
                ORDER BY psc.SpecialCaseID DESC
            ");
        } else {
            $cases = DB::select("
                SELECT
                    psc.SpecialCaseID,
                    psc.PersonID,
                    psc.ServentID,
                    psc.CaseDate,
                    psc.Note,
                    CONCAT(
                        COALESCE(p.FirstName, ''), ' ',
                        COALESCE(p.SecondName, ''), ' ',
                        COALESCE(p.ThirdName, ''), ' ',
                        COALESCE(p.FourthName, '')
                    ) AS PersonName,
                    CONCAT(
                        COALESCE(s.FirstName, ''), ' ',
                        COALESCE(s.SecondName, ''), ' ',
                        COALESCE(s.ThirdName, ''), ' ',
                        COALESCE(s.FourthName, '')
                    ) AS ServentName
                FROM PersonSpecialCase psc
                LEFT JOIN PersonInformation p ON p.PersonID = psc.PersonID
                LEFT JOIN PersonInformation s ON s.PersonID = psc.ServentID
                WHERE psc.PersonID IN (
                    {$this->allowedPersonIdsSql()}
                )
                ORDER BY psc.SpecialCaseID DESC
            ", [$userPersonId]);
        }

        return response()->json([
            'ok' => true,
            'cases' => $cases,
        ]);
    }

    /**
     * GET /api/person-special-cases/persons
     */
    public function persons(Request $request)
    {
        if ($deny = $this->denyIfNoSpecialCaseAccess()) {
            return $deny;
        }

        $userPersonId = $this->authPersonId();

        if ($this->isSuperAdmin()) {
            $persons = DB::select("
                SELECT DISTINCT
                    pi.PersonID,
                    pi.ShamandoraCode,
                    CONCAT(
                        COALESCE(pi.FirstName, ''), ' ',
                        COALESCE(pi.SecondName, ''), ' ',
                        COALESCE(pi.ThirdName, ''), ' ',
                        COALESCE(pi.FourthName, '')
                    ) AS PersonName,
                    pi.FirstName,
                    pi.SecondName,
                    pi.ThirdName,
                    pi.FourthName,
                    q.QetaaName,
                    pi.ScoutJoiningYear,
                    sm.SanaMarhalaName,
                    pi.RaqamQawmy,
                    ppn.PersonPersonalMobileNumber,
                    q.QetaaID,
                    PG.PersonID AS GroupPersonID,
                    IF(peq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions,
                    psm.SanaMarhalaID
                FROM PersonInformation pi
                LEFT JOIN PersonEntryQuestions peq ON pi.PersonID = peq.PersonID
                LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
                LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
                LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
                LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
                LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
                LEFT JOIN PersonGroup PG ON PG.PersonID = pi.PersonID
                ORDER BY pi.ShamandoraCode ASC
            ");
        } else {
            $persons = DB::select("
                {$this->allowedPersonsSql()}
                ORDER BY ShamandoraCode ASC
            ", [$userPersonId]);
        }

        return response()->json([
            'ok' => true,
            'persons' => $persons,
        ]);
    }

    /**
     * GET /api/person-special-cases/{id}
     */
    public function show($id)
    {
        if ($deny = $this->denyIfNoSpecialCaseAccess()) {
            return $deny;
        }

        $case = $this->getAllowedCase((int) $id);

        if (!$case) {
            return response()->json([
                'ok' => false,
                'message' => 'Case not found or not allowed',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'case' => $case,
        ]);
    }

    /**
     * POST /api/person-special-cases
     */
    public function store(Request $request)
    {
        if ($deny = $this->denyIfNoSpecialCaseAccess()) {
            return $deny;
        }

        $data = $request->validate([
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
            'note' => 'nullable|string|max:1000',
        ]);

        $userPersonId = $this->authPersonId();

        if (!$userPersonId) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (!$this->allowedPersonExists((int) $data['person_id'], $userPersonId)) {
            return response()->json([
                'ok' => false,
                'message' => 'This person is not allowed for you',
            ], 403);
        }

        $exists = DB::table('PersonSpecialCase')
            ->where('PersonID', $data['person_id'])
            ->whereDate('CaseDate', now()->toDateString())
            ->exists();

        if ($exists) {
            return response()->json([
                'ok' => false,
                'message' => 'This person already has a special case today',
            ], 409);
        }

        $specialCaseId = DB::table('PersonSpecialCase')->insertGetId([
            'PersonID'  => (int) $data['person_id'],
            'ServentID' => (int) $userPersonId,
            'CaseDate'  => now(),
            'Note'      => $data['note'] ?? null,
        ]);

        $case = $this->getAllowedCase((int) $specialCaseId, $userPersonId);

        return response()->json([
            'ok' => true,
            'message' => 'Special case created successfully',
            'case' => $case,
        ], 201);
    }

    /**
     * PUT /api/person-special-cases/{id}
     */
    public function update(Request $request, $id)
    {
        if ($deny = $this->denyIfNoSpecialCaseAccess()) {
            return $deny;
        }

        $data = $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $case = $this->getAllowedCase((int) $id);

        if (!$case) {
            return response()->json([
                'ok' => false,
                'message' => 'Case not found or not allowed',
            ], 404);
        }

        DB::table('PersonSpecialCase')
            ->where('SpecialCaseID', (int) $id)
            ->update([
                'Note' => $data['note'] ?? null,
            ]);

        $updatedCase = $this->getAllowedCase((int) $id);

        return response()->json([
            'ok' => true,
            'message' => 'Special case updated successfully',
            'case' => $updatedCase,
        ]);
    }

    /**
     * DELETE /api/person-special-cases/{id}
     */
    public function destroy($id)
    {
        if ($deny = $this->denyIfNoSpecialCaseAccess()) {
            return $deny;
        }

        $case = $this->getAllowedCase((int) $id);

        if (!$case) {
            return response()->json([
                'ok' => false,
                'message' => 'Case not found or not allowed',
            ], 404);
        }

        DB::table('PersonSpecialCase')
            ->where('SpecialCaseID', (int) $id)
            ->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Special case deleted successfully',
        ]);
    }

    /**
     * GET /api/person-special-cases/search/persons?search=...
     */
    public function searchPersons(Request $request)
    {
        if ($deny = $this->denyIfNoSpecialCaseAccess()) {
            return $deny;
        }

        $search = trim($request->query('search', ''));
        $userPersonId = $this->authPersonId();

        try {
            if ($this->isSuperAdmin()) {
                $persons = DB::select("
                    SELECT DISTINCT
                        pi.PersonID,
                        pi.ShamandoraCode,
                        CONCAT(
                            COALESCE(pi.FirstName, ''), ' ',
                            COALESCE(pi.SecondName, ''), ' ',
                            COALESCE(pi.ThirdName, ''), ' ',
                            COALESCE(pi.FourthName, '')
                        ) AS PersonName,
                        pi.FirstName,
                        pi.SecondName,
                        pi.ThirdName,
                        pi.FourthName,
                        q.QetaaName,
                        pi.ScoutJoiningYear,
                        sm.SanaMarhalaName,
                        pi.RaqamQawmy,
                        ppn.PersonPersonalMobileNumber,
                        q.QetaaID,
                        PG.PersonID AS GroupPersonID,
                        IF(peq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions,
                        psm.SanaMarhalaID
                    FROM PersonInformation pi
                    LEFT JOIN PersonEntryQuestions peq ON pi.PersonID = peq.PersonID
                    LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
                    LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
                    LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
                    LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
                    LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
                    LEFT JOIN PersonGroup PG ON PG.PersonID = pi.PersonID
                    WHERE
                        CONCAT(
                            COALESCE(pi.FirstName, ''), ' ',
                            COALESCE(pi.SecondName, ''), ' ',
                            COALESCE(pi.ThirdName, ''), ' ',
                            COALESCE(pi.FourthName, '')
                        ) LIKE ?
                        OR CAST(pi.PersonID AS CHAR) LIKE ?
                        OR ppn.PersonPersonalMobileNumber LIKE ?
                        OR pi.FirstName LIKE ?
                        OR pi.SecondName LIKE ?
                        OR pi.ThirdName LIKE ?
                        OR pi.FourthName LIKE ?
                        OR pi.ShamandoraCode LIKE ?
                        OR pi.RaqamQawmy LIKE ?
                    ORDER BY pi.ShamandoraCode ASC
                    LIMIT 20
                ", [
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%"
                ]);
            } else {
                $persons = DB::select("
                    SELECT *
                    FROM (
                        {$this->allowedPersonsSql()}
                    ) allowed_people
                    WHERE
                        allowed_people.PersonName LIKE ?
                        OR CAST(allowed_people.PersonID AS CHAR) LIKE ?
                        OR allowed_people.PersonPersonalMobileNumber LIKE ?
                        OR allowed_people.FirstName LIKE ?
                        OR allowed_people.SecondName LIKE ?
                        OR allowed_people.ThirdName LIKE ?
                        OR allowed_people.FourthName LIKE ?
                        OR allowed_people.ShamandoraCode LIKE ?
                        OR allowed_people.RaqamQawmy LIKE ?
                    ORDER BY allowed_people.ShamandoraCode ASC
                    LIMIT 20
                ", [
                    $userPersonId,
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%",
                    "%{$search}%"
                ]);
            }

            return response()->json([
                'ok' => true,
                'persons' => $persons,
            ]);
        } catch (\Throwable $e) {
            Log::error('PersonSpecialCaseApiController searchPersons failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Search failed',
            ], 500);
        }
    }
}