<?php

namespace App\Http\Controllers;

use App\Domain\Event\EventAudience;
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
        $events = DB::table('Event as e')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->join('SeasonEvent as se', 'se.EventID', '=', 'e.EventID')
            ->join('Season as S', 'S.SeasonID', '=', 'se.SeasonID')
            ->where(function ($query) use ($id) {
                app(EventAudience::class)->whereVisibleToPerson($query, (int) $id, 'e.EventID');
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
            ->get();

        return response()->json(['events' => $events]);
    }
}
