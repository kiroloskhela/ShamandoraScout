<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // 1️⃣ Persons count
        $row = DB::selectOne("
            SELECT COUNT(DISTINCT pi.PersonID) AS total
            FROM PersonInformation pi
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
            JOIN GroupQetaa gq ON gq.QetaaID = q.QetaaID
            JOIN PersonGroup pg2 ON pg2.GroupID = gq.GroupID
            WHERE pg2.PersonID = ?
        ", [$userId]);

        $personsCount = $row ? (int) $row->total : 0;

        // 2️⃣ Calendar events
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
        ", [$userId]);

        // 3️⃣ Pass to view
        return view('index', compact('personsCount', 'events'));
    }
}