<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $today = now()->toDateString();

        $row = DB::selectOne('
            SELECT COUNT(DISTINCT pq.PersonID) AS total
            FROM PersonGroup pg2
            JOIN GroupQetaa gq ON gq.GroupID = pg2.GroupID
            JOIN PersonQetaa pq ON pq.QetaaID = gq.QetaaID
            WHERE pg2.PersonID = ?
        ', [$userId]);

        $personsCount = $row ? (int) $row->total : 0;

        // Upcoming (and undated) events only; cap keeps the home calendar bounded.
        $events = DB::select('
            SELECT DISTINCT
                e.EventID, e.EventName, e.EventStartDate, e.EventEndDate,
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
              AND (e.EventEndDate IS NULL OR e.EventEndDate >= ?)
            ORDER BY e.EventStartDate ASC, e.EventID ASC
            LIMIT 150
        ', [$userId, $today]);

        return view('index', compact('personsCount', 'events'));
    }
}
