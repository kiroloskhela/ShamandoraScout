<?php

namespace App\Http\Controllers;

use App\Domain\Event\EventAudience;
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
        $events = DB::table('Event as e')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->join('SeasonEvent as se', 'se.EventID', '=', 'e.EventID')
            ->join('Season as S', 'S.SeasonID', '=', 'se.SeasonID')
            ->where(function ($query) use ($userId) {
                app(EventAudience::class)->whereVisibleToPerson($query, (int) $userId, 'e.EventID');
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('e.EventEndDate')
                    ->orWhere('e.EventEndDate', '>=', $today);
            })
            ->select(
                'e.EventID',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate',
                'et.EventTypeName',
                'S.SeasonName',
                'S.SeasonYear'
            )
            ->distinct()
            ->orderBy('e.EventStartDate')
            ->orderBy('e.EventID')
            ->limit(150)
            ->get();

        return view('index', compact('personsCount', 'events'));
    }
}
