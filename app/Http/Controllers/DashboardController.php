<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // ✅ الدالة الأولى: ترجع عدد الأشخاص فقط
    public function ShowPersonsCount(Request $request)
    {
        $userId = $request->input('id');

        $count = DB::table('PersonInformation as pi')
            ->select(DB::raw('COUNT(DISTINCT pi.PersonID) as total'))
            ->join('PersonGroup as pg2', 'pg2.PersonID', '=', DB::raw('?'))
            ->join('GroupQetaa as gq', 'pg2.GroupID', '=', 'gq.GroupID')
            ->join('Qetaa as q', 'gq.QetaaID', '=', 'q.QetaaID')
            ->join('PersonQetaa as pq', function ($join) {
                $join->on('pi.PersonID', '=', 'pq.PersonID')
                     ->on('pq.QetaaID', '=', 'q.QetaaID');
            })
            ->setBindings([$userId]) // لضبط قيمة المعامل ؟
            ->value('total'); // يرجّع العدد فقط بدون array

        return response()->json(['count' => $count]);
    }

    // ✅ الدالة الثانية كما هي (لعرض الأحداث)
    public function ShowCalendar($id)
    {
        $events = DB::select("
            SELECT e.EventID, e.EventName, e.EventStartDate, e.EventEndDate, 
                   et.EventTypeName, S.SeasonName, S.SeasonYear
            FROM PersonGroup pg
            JOIN GroupQetaa gq ON pg.GroupID = gq.GroupID
            JOIN Qetaa q ON gq.QetaaID = q.QetaaID
            JOIN EventQetaa eq ON q.QetaaID = eq.QetaaID
            JOIN Event e ON eq.EventID = e.EventID
            JOIN EventType et ON e.EventTypeID = et.EventTypeID
            JOIN SeasonEvent se ON se.EventID = e.EventID
            JOIN Season S ON S.SeasonID = se.SeasonID
            WHERE pg.PersonID = ?
            ORDER BY e.EventStartDate ASC
        ", [$id]);

        return response()->json(['events' => $events]);
    }
}