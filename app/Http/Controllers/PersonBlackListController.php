<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PersonBlackListController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('id') ?? Auth::id();
        Log::info("Fetching PersonBlackList for user ID: " . $userId);

        $blacklist = DB::select("
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
            WHERE pbl.PersonID IN (
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
            ORDER BY pbl.BlackListID DESC
        ", [$userId]);

        return view("personblacklist.index", ['blacklist' => $blacklist]);
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

        return view("personblacklist.create", ['persons' => $persons]);
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

        $exists = DB::table('PersonBlackList')
            ->where('PersonID', $request->person_id)
            ->whereDate('CaseDate', now()->toDateString())
            ->first();

        if ($exists != null) {
            return redirect()->back()->with('status', 'تمت إضافة هذا الشخص بالفعل اليوم إلى القائمة السوداء');
        }

        DB::table('PersonBlackList')->insert([
            'PersonID'  => $request->person_id,
            'ServentID' => $userId,
            'CaseDate'  => now(),
            'Note'      => $request->note
        ]);

        return redirect()->route('personblacklist.index')
            ->with('status', 'تم إضافة الشخص إلى القائمة السوداء بنجاح');
    }

    public function edit($id)
    {
        $black = DB::selectOne("
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
                ) AS PersonName
            FROM PersonBlackList pbl
            LEFT JOIN PersonInformation p ON p.PersonID = pbl.PersonID
            WHERE pbl.BlackListID = ?
        ", [$id]);

        return view("personblacklist.edit", [
            'black' => $black,
            'title' => 'تعديل القائمة السوداء'
        ]);
    }

    public function updates(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        DB::table('PersonBlackList')
            ->where('BlackListID', $id)
            ->update([
                'Note' => $request->note
            ]);

        return redirect()->route('personblacklist.index')
            ->with('status', 'تم تعديل الملاحظة بنجاح');
    }

    public function deletes($id)
    {
        $black = DB::selectOne("
            SELECT 
                pbl.BlackListID,
                pbl.PersonID,
                pbl.Note,
                CONCAT(
                    COALESCE(p.FirstName, ''), ' ',
                    COALESCE(p.SecondName, ''), ' ',
                    COALESCE(p.ThirdName, ''), ' ',
                    COALESCE(p.FourthName, '')
                ) AS PersonName
            FROM PersonBlackList pbl
            LEFT JOIN PersonInformation p ON p.PersonID = pbl.PersonID
            WHERE pbl.BlackListID = ?
        ", [$id]);

        return view("personblacklist.delete", [
            'black' => $black,
            'title' => 'حذف من القائمة السوداء'
        ]);
    }

    public function destroy($id)
    {
        DB::table('PersonBlackList')
            ->where('BlackListID', $id)
            ->delete();

        return redirect()->route('personblacklist.index')
            ->with('status', 'تم حذف الشخص من القائمة السوداء بنجاح');
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
            "%$search%", "%$search%", "%$search%"
        ]);

        return response()->json($persons);
    }
}