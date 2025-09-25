<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // Optional middleware
    // public function __construct(){ $this->middleware('auth'); }

 public function manage(Request $request)
{
    $me = \Illuminate\Support\Facades\Auth::user();
    $meId = optional($me)->PersonID ?? \Illuminate\Support\Facades\Auth::id();

    $seasons = DB::table('Season')->select('SeasonID','SeasonName','SeasonYear')->orderBy('SeasonYear','desc')->get();

    $seasonId = $request->get('season_id');
    $seasonEventId = $request->get('season_event_id');

    // 1) My groups
    $myGroups = DB::table('PersonGroup')
        ->where('PersonID', $meId)
        ->pluck('GroupID')
        ->toArray();

    $events = collect();
    $persons = collect();
    $attendance = [];

    // 2) Events in this season that overlap with MY GROUPS (via EventQetaa ↔ GroupQetaa)
    if ($seasonId && !empty($myGroups)) {
        $events = DB::table('SeasonEvent as se')
            ->join('Event as e','e.EventID','=','se.EventID')
            ->where('se.SeasonID', $seasonId)
            ->whereExists(function($q) use ($myGroups) {
                $q->select(DB::raw(1))
                  ->from('EventQetaa as eq')
                  ->join('GroupQetaa as gq','gq.QetaaID','=','eq.QetaaID')
                  ->whereColumn('eq.EventID','se.EventID')
                  ->whereIn('gq.GroupID', $myGroups)
                  ->limit(1);
            })
            ->select('se.SeasonEventID','se.SeasonID','e.EventID','e.EventName','e.EventStartDate','e.EventEndDate')
            ->orderBy('e.EventStartDate','asc')
            ->get();
    }

    // 3) Persons = people IN QetaaIDs (via PersonQetaa), limited to
    //    intersection( my Qetaas, event's Qetaas )
    if ($seasonEventId && !empty($myGroups)) {
        $eventId = DB::table('SeasonEvent')->where('SeasonEventID', $seasonEventId)->value('EventID');

        // QetaaIDs of my groups
        $myQetaas = DB::table('GroupQetaa')
            ->whereIn('GroupID', $myGroups)
            ->pluck('QetaaID')
            ->toArray();

        // QetaaIDs of the selected event
        $eventQetaas = DB::table('EventQetaa')
            ->where('EventID', $eventId)
            ->pluck('QetaaID')
            ->toArray();

        // Intersection
        $allowedQetaas = array_values(array_intersect($myQetaas, $eventQetaas));

        if (!empty($allowedQetaas)) {
            // 🔹 persons come from PersonQetaa (NOT PersonGroup)
            $persons = DB::table('PersonQetaa as pq')
                ->join('PersonInformation as p','p.PersonID','=','pq.PersonID')
                ->whereIn('pq.QetaaID', $allowedQetaas)
                ->select('p.PersonID','p.FirstName','p.SecondName','p.ThirdName','p.FourthName')
                ->distinct()
                ->orderBy('p.FirstName')
                ->get();

            // Existing attendance for pre-check
            $attendance = DB::table('Attendance')
                ->where('SeasonEventID',$seasonEventId)
                ->pluck('ServedID')
                ->toArray();
        }
    }

    return view('attendance.manage', compact(
        'seasons','events','persons','attendance','seasonId','seasonEventId','me'
    ));
}


    public function save(Request $request, $seasonEventId)
    {
        // Servent is ALWAYS the logged-in user
        $serventId = optional(Auth::user())->PersonID ?? Auth::id();

        // We will re-enforce access: only save for groups the user serves & that belong to this event
        $myGroupIds = DB::table('PersonGroup')
            ->where('PersonID', $serventId)
            ->pluck('GroupID');

        if ($myGroupIds->isEmpty()) {
            return back()->with('success','لا تمتلك مجموعات لادارة حضورها.');
        }

        $eventId = DB::table('SeasonEvent')->where('SeasonEventID', $seasonEventId)->value('EventID');
        if (!$eventId) {
            return back()->with('success','الفعالية غير موجودة.');
        }

        $eventQetaaIds = DB::table('EventQetaa')->where('EventID', $eventId)->pluck('QetaaID');
        $eventGroupIds = DB::table('GroupQetaa')->whereIn('QetaaID', $eventQetaaIds)->pluck('GroupID');
        $allowedGroupIds = $eventGroupIds->intersect($myGroupIds)->values();

        // Normalize ServedIDs (only keep persons from allowed groups)
        $servedIds = collect($request->input('ServedIDs', []))->map(fn($v)=>(int)$v)->unique()->values();

        if ($servedIds->isNotEmpty()) {
            // Filter servedIds to persons that are actually in allowed groups
            $validServedIds = DB::table('PersonGroup')
                ->whereIn('GroupID', $allowedGroupIds)
                ->whereIn('PersonID', $servedIds)
                ->pluck('PersonID');

            $servedIds = $servedIds->intersect($validServedIds)->values();
        }

        // Replace attendance for this SeasonEvent with the filtered list
        DB::table('Attendance')->where('SeasonEventID', $seasonEventId)->delete();

        if ($servedIds->isNotEmpty()) {
            $rows = $servedIds->map(function($sid) use ($seasonEventId, $serventId) {
                return [
                    'SeasonEventID' => (int)$seasonEventId,
                    'ServedID'      => (int)$sid,
                    'ServentID'     => (int)$serventId,
                ];
            })->all();

            DB::table('Attendance')->insert($rows);
        }

        return redirect()->route('attendance.manage', [
            'season_id' => $request->season_id,
            'season_event_id' => $seasonEventId
        ])->with('success','تم حفظ الحضور بنجاح');
    }
}