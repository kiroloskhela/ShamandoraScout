<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    // public function __construct() { $this->middleware('auth'); }

    public function manage(Request $request)
    {
        $me = Auth::user();
        $meId = optional($me)->PersonID ?? Auth::id();

        // Seasons
        $seasons = DB::table('Season')
            ->select('SeasonID','SeasonName','SeasonYear')
            ->orderBy('SeasonYear','desc')
            ->get();

        $seasonId = $request->get('season_id');
        $seasonEventId = $request->get('season_event_id');

        // My groups
        $myGroups = DB::table('PersonGroup')
            ->where('PersonID', $meId)
            ->pluck('GroupID')
            ->toArray();

        $events = collect();
        $persons = collect();
        $attendanceIds = [];
        $tableRows = [];

        // Events in season that overlap with my groups via EventQetaa ↔ GroupQetaa
        if ($seasonId && !empty($myGroups)) {
            $events = DB::table('SeasonEvent as se')
                ->join('Event as e','e.EventID','=','se.EventID')
                ->where('se.SeasonID', $seasonId)
                ->whereExists(function($q) use ($myGroups){
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

        // Persons = members of PersonQetaa in intersection(my Qetaas, event Qetaas)
        if ($seasonEventId && !empty($myGroups)) {
            $eventId = DB::table('SeasonEvent')->where('SeasonEventID', $seasonEventId)->value('EventID');

            $myQetaas = DB::table('GroupQetaa')
                ->whereIn('GroupID', $myGroups)
                ->pluck('QetaaID')
                ->toArray();

            $eventQetaas = DB::table('EventQetaa')
                ->where('EventID', $eventId)
                ->pluck('QetaaID')
                ->toArray();

            $allowedQetaas = array_values(array_intersect($myQetaas, $eventQetaas));

            if (!empty($allowedQetaas)) {
                $persons = DB::table('PersonQetaa as pq')
                    ->join('PersonInformation as p','p.PersonID','=','pq.PersonID')
                    ->leftJoin('PersonPhoneNumbers as ph','ph.PersonID','=','p.PersonID')
                    ->leftJoin('PersonSanaMarhala as psm','psm.PersonID','=','p.PersonID')
                    ->leftJoin('SanaMarhala as sm','sm.SanaMarhalaID','=','psm.SanaMarhalaID')
                    ->leftJoin('Qetaa as q','q.QetaaID','=','pq.QetaaID')
                    ->whereIn('pq.QetaaID', $allowedQetaas)
                    ->groupBy('p.PersonID','p.FirstName','p.SecondName','p.ThirdName','p.FourthName','sm.SanaMarhalaName')
                    ->selectRaw("
                        p.PersonID,
                        p.FirstName, p.SecondName, p.ThirdName, p.FourthName,
                        COALESCE(MAX(ph.PersonPersonalMobileNumber),'') as PhoneNumber,
                        COALESCE(GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ', '),'') as QetaaName,
                        COALESCE(sm.SanaMarhalaName,'') as SanaMarhalaName
                    ")
                    ->orderBy('p.FirstName')
                    ->get();

                $attendanceIds = DB::table('Attendance')
                    ->where('SeasonEventID',$seasonEventId)
                    ->pluck('ServedID')
                    ->toArray();

                $tableRows = $persons->map(function($p) use ($attendanceIds){
                    $fullName = trim("{$p->FirstName} {$p->SecondName} {$p->ThirdName} {$p->FourthName}");
                    $isPresent = in_array($p->PersonID, $attendanceIds);
                    return [
                        'PersonID'        => (int)$p->PersonID,
                        'PersonName'      => $fullName,
                        'PhoneNumber'     => $p->PhoneNumber,
                        'QetaaName'       => $p->QetaaName,
                        'SanaMarhalaName' => $p->SanaMarhalaName,
                        'Attended'        => $isPresent ? 'نعم' : 'لا',
                    ];
                })->toArray();
            }
        }

        return view('attendance.manage', [
            'seasons'        => $seasons,
            'events'         => $events,
            'seasonId'       => $seasonId,
            'seasonEventId'  => $seasonEventId,
            'tableRows'      => $tableRows,
            'attendanceIds'  => $attendanceIds,
            'me'             => $me,
        ]);
    }

    // Save all toggles (non-AJAX submit)
    public function save(Request $request, $seasonEventId)
    {
        $serventId = optional(Auth::user())->PersonID ?? Auth::id();

        $request->validate([
            'ServedIDs' => 'array'
        ]);

        DB::table('Attendance')->where('SeasonEventID', $seasonEventId)->delete();

        $rows = [];
        foreach ((array)$request->input('ServedIDs', []) as $servedId) {
            $rows[] = [
                'SeasonEventID' => (int)$seasonEventId,
                'ServedID'      => (int)$servedId,
                'ServentID'     => (int)$serventId,
            ];
        }
        if (!empty($rows)) {
            DB::table('Attendance')->insert($rows);
        }

        return redirect()->route('attendance.manage', [
            'season_id'       => $request->season_id,
            'season_event_id' => $seasonEventId
        ])->with('success','تم حفظ الحضور بنجاح');
    }
}