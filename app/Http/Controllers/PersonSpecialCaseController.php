<?php

namespace App\Http\Controllers;

use App\Domain\SpecialCase\PersonSpecialCaseService;
use App\Support\LikeSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonSpecialCaseController extends Controller
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
     * Only PersonID version for access checks
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

    private function getAllowedCase($specialCaseId, $userId = null)
    {
        $userId = $userId ?? Auth::id();

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
        ", [$specialCaseId, $userId]);
    }

    public function index(Request $request)
    {
        $userId = $request->query('id') ?? Auth::id();

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
        ", [$userId]);

        return view('personspecialcase.index', ['cases' => $cases]);
    }

    public function create(Request $request)
    {
        $userId = $request->query('id') ?? Auth::id();

        $persons = DB::select("
            {$this->allowedPersonsSql()}
            ORDER BY ShamandoraCode ASC
        ", [$userId]);

        return view('personspecialcase.create', ['persons' => $persons]);
    }

    public function insert(Request $request, PersonSpecialCaseService $specialCases)
    {
        $request->validate([
            'person_id' => 'required|integer',
            'note' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();

        if (! $this->allowedPersonExists($request->person_id, $userId)) {
            return redirect()->back()->with('status', 'هذا الشخص غير متاح لك');
        }

        $exists = DB::table('PersonSpecialCase')
            ->where('PersonID', $request->person_id)
            ->whereDate('CaseDate', now()->toDateString())
            ->first();

        if ($exists) {
            return redirect()->back()->with('status', 'تمت إضافة هذا الشخص بالفعل اليوم');
        }

        $specialCases->create(
            (int) $request->person_id,
            (int) $userId,
            $request->note
        );

        return redirect()->route('personspecialcase.index')
            ->with('status', 'تم إضافة الحالة الخاصة بنجاح');
    }

    public function edit($id)
    {
        $case = $this->getAllowedCase($id);

        if (! $case) {
            abort(403, 'غير مسموح لك بالوصول لهذه الحالة');
        }

        return view('personspecialcase.edit', [
            'case' => $case,
            'title' => 'تعديل حالة خاصة',
        ]);
    }

    public function updates(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $case = $this->getAllowedCase($id);

        if (! $case) {
            abort(403, 'غير مسموح لك بتعديل هذه الحالة');
        }

        DB::table('PersonSpecialCase')
            ->where('SpecialCaseID', $id)
            ->update([
                'Note' => $request->note,
            ]);

        return redirect()->route('personspecialcase.index')
            ->with('status', 'تم تعديل الملاحظة بنجاح');
    }

    public function deletes($id)
    {
        $case = $this->getAllowedCase($id);

        if (! $case) {
            abort(403, 'غير مسموح لك بحذف هذه الحالة');
        }

        return view('personspecialcase.delete', [
            'case' => $case,
            'title' => 'حذف حالة خاصة',
        ]);
    }

    public function destroy($id)
    {
        $case = $this->getAllowedCase($id);

        if (! $case) {
            abort(403, 'غير مسموح لك بحذف هذه الحالة');
        }

        DB::table('PersonSpecialCase')
            ->where('SpecialCaseID', $id)
            ->delete();

        return redirect()->route('personspecialcase.index')
            ->with('status', 'تم حذف الحالة الخاصة بنجاح');
    }

    public function searchPersons(Request $request)
    {
        $userId = Auth::id();
        $term = LikeSearch::fromRequest($request, ['search', 'q']);

        try {
            $bindings = [$userId];
            $whereSql = '1=1';
            if ($term !== null) {
                $fragment = LikeSearch::sqlOr(
                    LikeSearch::allowedPeopleColumns(),
                    $term
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
            Log::error('searchPersons failed', [
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
