<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PersonSpecialCaseController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('id') ?? Auth::id();
        Log::info("Fetching PersonSpecialCase for user ID: " . $userId);

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
                SELECT DISTINCT
                    pi.PersonID
                FROM PersonInformation pi
                LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
                LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
                JOIN GroupQetaa gq ON gq.QetaaID = q.QetaaID
                JOIN PersonGroup pg2 ON pg2.GroupID = gq.GroupID
                WHERE q.QetaaID IN (
                    SELECT gq2.QetaaID
                    FROM GroupQetaa gq2
                    WHERE gq2.GroupID = (
                        SELECT pg3.GroupID
                        FROM PersonGroup pg3
                        WHERE pg3.PersonID = ?
                        LIMIT 1
                    )
                    LIMIT 1
                )
            )
            ORDER BY psc.SpecialCaseID DESC
        ", [$userId]);

        return view("personspecialcase.index", ['cases' => $cases]);
    }

    public function create(Request $request)
    {
        $userId = $request->query('id') ?? Auth::id();

        $persons = DB::select("
            SELECT DISTINCT
                pi.PersonID,
                pi.ShamandoraCode,
                CONCAT(
                    COALESCE(pi.FirstName, ''), ' ',
                    COALESCE(pi.SecondName, ''), ' ',
                    COALESCE(pi.ThirdName, ''), ' ',
                    COALESCE(pi.FourthName, '')
                ) AS PersonName
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
            WHERE q.QetaaID = (
                SELECT gq2.QetaaID
                FROM GroupQetaa gq2
                WHERE gq2.GroupID = (
                    SELECT pg3.GroupID
                    FROM PersonGroup pg3
                    WHERE pg3.PersonID = ?
                    LIMIT 1
                )
                LIMIT 1
            )
            ORDER BY pi.ShamandoraCode ASC
        ", [$userId]);

        return view("personspecialcase.create", ['persons' => $persons]);
    }

    public function insert(Request $request)
    {
        $request->validate([
            'person_id' => 'required|integer',
            'note'      => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();

        $person = DB::selectOne("
            SELECT DISTINCT
                pi.PersonID
            FROM PersonInformation pi
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
            JOIN GroupQetaa gq ON gq.QetaaID = q.QetaaID
            JOIN PersonGroup pg2 ON pg2.GroupID = gq.GroupID
            WHERE pi.PersonID = ?
              AND q.QetaaID = (
                    SELECT gq2.QetaaID
                    FROM GroupQetaa gq2
                    WHERE gq2.GroupID = (
                        SELECT pg3.GroupID
                        FROM PersonGroup pg3
                        WHERE pg3.PersonID = ?
                        LIMIT 1
                    )
                    LIMIT 1
              )
        ", [$request->person_id, $userId]);

        if ($person == null) {
            return redirect()->back()->with('status', 'هذا الشخص غير متاح لك');
        }

        $exists = DB::table('PersonSpecialCase')
            ->where('PersonID', $request->person_id)
            ->whereDate('CaseDate', now()->toDateString())
            ->first();

        if ($exists != null) {
            return redirect()->back()->with('status', 'تمت إضافة هذا الشخص بالفعل اليوم');
        }

        DB::table('PersonSpecialCase')->insert([
            'PersonID'  => $request->person_id,
            'ServentID' => $userId,
            'CaseDate'  => now(),
            'Note'      => $request->note
        ]);

        return redirect()->route('personspecialcase.index')
            ->with('status', 'تم إضافة الحالة الخاصة بنجاح');
    }

    public function edit($id)
    {
        $case = DB::selectOne("
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
                ) AS PersonName
            FROM PersonSpecialCase psc
            LEFT JOIN PersonInformation p ON p.PersonID = psc.PersonID
            WHERE psc.SpecialCaseID = ?
        ", [$id]);

        return view("personspecialcase.edit", [
            'case'  => $case,
            'title' => 'تعديل حالة خاصة'
        ]);
    }

    public function updates(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        DB::table('PersonSpecialCase')
            ->where('SpecialCaseID', $id)
            ->update([
                'Note' => $request->note
            ]);

        return redirect()->route('personspecialcase.index')
            ->with('status', 'تم تعديل الملاحظة بنجاح');
    }

    public function deletes($id)
    {
        $case = DB::selectOne("
            SELECT 
                psc.SpecialCaseID,
                psc.PersonID,
                psc.Note,
                CONCAT(
                    COALESCE(p.FirstName, ''), ' ',
                    COALESCE(p.SecondName, ''), ' ',
                    COALESCE(p.ThirdName, ''), ' ',
                    COALESCE(p.FourthName, '')
                ) AS PersonName
            FROM PersonSpecialCase psc
            LEFT JOIN PersonInformation p ON p.PersonID = psc.PersonID
            WHERE psc.SpecialCaseID = ?
        ", [$id]);

        return view("personspecialcase.delete", [
            'case'  => $case,
            'title' => 'حذف حالة خاصة'
        ]);
    }

    public function destroy($id)
    {
        DB::table('PersonSpecialCase')
            ->where('SpecialCaseID', $id)
            ->delete();

        return redirect()->route('personspecialcase.index')
            ->with('status', 'تم حذف الحالة الخاصة بنجاح');
    }

    public function searchPersons(Request $request)
    {
        $userId = $request->query('id') ?? Auth::id();
        $search = trim($request->query('search', ''));

        $persons = DB::select("
            SELECT DISTINCT
                pi.PersonID,
                pi.ShamandoraCode,
                CONCAT(
                    COALESCE(pi.FirstName, ''), ' ',
                    COALESCE(pi.SecondName, ''), ' ',
                    COALESCE(pi.ThirdName, ''), ' ',
                    COALESCE(pi.FourthName, '')
                ) AS PersonName
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
            WHERE q.QetaaID = (
                SELECT gq2.QetaaID
                FROM GroupQetaa gq2
                WHERE gq2.GroupID = (
                    SELECT pg3.GroupID
                    FROM PersonGroup pg3
                    WHERE pg3.PersonID = ?
                    LIMIT 1
                )
                LIMIT 1
            )
            AND (
                pi.FirstName LIKE ?
                OR pi.SecondName LIKE ?
                OR pi.ThirdName LIKE ?
                OR pi.FourthName LIKE ?
                OR pi.RaqamQawmy LIKE ?
                OR pi.ShamandoraCode LIKE ?
                OR CAST(pi.PersonID AS CHAR) LIKE ?
                OR CONCAT(
                    COALESCE(pi.FirstName, ''), ' ',
                    COALESCE(pi.SecondName, ''), ' ',
                    COALESCE(pi.ThirdName, ''), ' ',
                    COALESCE(pi.FourthName, '')
                ) LIKE ?
            )
            ORDER BY pi.ShamandoraCode ASC
        ", [
            $userId,
            "%$search%", "%$search%", "%$search%", "%$search%",
            "%$search%", "%$search%", "%$search%", "%$search%"
        ]);

        return response()->json($persons);
    }
}