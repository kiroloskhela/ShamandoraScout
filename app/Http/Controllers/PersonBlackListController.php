<?php

namespace App\Http\Controllers;

use App\Support\LikeSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonBlackListController extends Controller
{
    /**
     * Full allowed persons query
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
                ppn.FatherMobileNumber,
                ppn.MotherMobileNumber,
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

    /**
     * IDs-only version for IN() and access checks
     */
    private function allowedPersonIdsSql()
    {
        return '
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
        ';
    }

    private function allowedPersonExists($personId, $userId = null)
    {
        $userId = $userId ?? Auth::id();

        $person = DB::selectOne("
            SELECT PersonID
            FROM (
                {$this->allowedPersonIdsSql()}
            ) allowed_people
            WHERE allowed_people.PersonID = ?
            LIMIT 1
        ", [$userId, $personId]);

        return $person !== null;
    }

    private function getAllowedBlackList($blackListId, $userId = null)
    {
        $userId = $userId ?? Auth::id();

        return DB::selectOne("
            SELECT
                pbl.BlackListID,
                pbl.PersonID,
                pbl.ServentID,
                pbl.CaseDate,
                pbl.Note,
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
            FROM PersonBlackList pbl
            LEFT JOIN PersonInformation p ON p.PersonID = pbl.PersonID
            LEFT JOIN PersonInformation s ON s.PersonID = pbl.ServentID
            WHERE pbl.BlackListID = ?
              AND pbl.PersonID IN (
                  {$this->allowedPersonIdsSql()}
              )
            LIMIT 1
        ", [$blackListId, $userId]);
    }

    public function index(Request $request)
    {
        $userId = $request->query('id') ?? Auth::id();
        Log::info('Fetching PersonBlackList for user ID: '.$userId);

        $bindings = [$userId];

        $sql = "
            SELECT
                pbl.BlackListID,
                pbl.PersonID,
                pbl.ServentID,
                pbl.CaseDate,
                pbl.Note,
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
            FROM PersonBlackList pbl
            LEFT JOIN PersonInformation p ON p.PersonID = pbl.PersonID
            LEFT JOIN PersonInformation s ON s.PersonID = pbl.ServentID
            LEFT JOIN PersonPhoneNumbers ppn ON ppn.PersonID = pbl.PersonID
            WHERE pbl.PersonID IN (
                {$this->allowedPersonIdsSql()}
            )
            ORDER BY pbl.BlackListID DESC
        ";

        $blacklist = collect(DB::select($sql, $bindings));

        return view('personblacklist.index', [
            'blacklist' => $blacklist,
        ]);
    }

    public function create(Request $request)
    {
        $userId = $request->query('id') ?? Auth::id();

        $persons = DB::select("
            {$this->allowedPersonsSql()}
            ORDER BY ShamandoraCode ASC
        ", [$userId]);

        return view('personblacklist.create', ['persons' => $persons]);
    }

    public function insert(Request $request)
    {
        $request->validate([
            'person_id' => 'required|integer',
            'note' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();

        if (! $this->allowedPersonExists($request->person_id, $userId)) {
            return redirect()->back()->with('status', __('This person is not available to you'));
        }

        $exists = DB::table('PersonBlackList')
            ->where('PersonID', $request->person_id)
            ->whereDate('CaseDate', now()->toDateString())
            ->first();

        if ($exists) {
            return redirect()->back()->with('status', __('This person was already added to the blacklist today'));
        }

        DB::table('PersonBlackList')->insert([
            'PersonID' => $request->person_id,
            'ServentID' => $userId,
            'CaseDate' => now(),
            'Note' => $request->note,
        ]);

        return redirect()->route('personblacklist.index')
            ->with('status', __('Person added to blacklist successfully'));
    }

    public function edit($id)
    {
        $black = $this->getAllowedBlackList($id);

        if (! $black) {
            abort(403, __('You are not allowed to access this record'));
        }

        return view('personblacklist.edit', [
            'black' => $black,
            'title' => __('Edit blacklist entry'),
        ]);
    }

    public function updates(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $black = $this->getAllowedBlackList($id);

        if (! $black) {
            abort(403, __('You are not allowed to edit this record'));
        }

        DB::table('PersonBlackList')
            ->where('BlackListID', $id)
            ->update([
                'Note' => $request->note,
            ]);

        return redirect()->route('personblacklist.index')
            ->with('status', __('Note updated successfully'));
    }

    public function deletes($id)
    {
        $black = $this->getAllowedBlackList($id);

        if (! $black) {
            abort(403, __('You are not allowed to delete this record'));
        }

        return view('personblacklist.delete', [
            'black' => $black,
            'title' => __('Remove from blacklist'),
        ]);
    }

    public function destroy($id)
    {
        $black = $this->getAllowedBlackList($id);

        if (! $black) {
            abort(403, __('You are not allowed to delete this record'));
        }

        DB::table('PersonBlackList')
            ->where('BlackListID', $id)
            ->delete();

        return redirect()->route('personblacklist.index')
            ->with('status', __('Person removed from blacklist successfully'));
    }

    public function searchPersons(Request $request)
    {
        $userId = Auth::id();
        $term = LikeSearch::fromRequest($request, ['search', 'q']);

        try {
            $bindings = [$userId];
            $whereSql = '1=1';
            if ($term !== null) {
                $fragment = LikeSearch::sqlFlexibleOr(
                    LikeSearch::allowedPeopleColumns(),
                    $term,
                    [
                        'allowed_people.PersonPersonalMobileNumber',
                        'allowed_people.FatherMobileNumber',
                        'allowed_people.MotherMobileNumber',
                    ]
                );
                $whereSql = $fragment['sql'];
                $bindings = array_merge($bindings, $fragment['bindings']);
            }

            $persons = DB::select("
                SELECT *
                FROM (
                    {$this->allowedPersonsSql()}
                ) allowed_people
                WHERE {$whereSql}
                ORDER BY allowed_people.ShamandoraCode ASC
                LIMIT 20
            ", $bindings);

            return response()->json($persons);
        } catch (\Throwable $e) {
            Log::error('personblacklist searchPersons failed', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'message' => 'Search failed',
            ], 500);
        }
    }
}
