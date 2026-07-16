<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function manage(Request $request)
    {
        $me   = Auth::user();
        $meId = optional($me)->PersonID ?? Auth::id();

        // Seasons
        $seasons = DB::table('Season')
            ->select('SeasonID', 'SeasonName', 'SeasonYear')
            ->orderBy('SeasonYear', 'desc')
            ->get();

        $seasonId      = $request->get('season_id');
        $seasonEventId = $request->get('season_event_id');

        // My groups
        $myGroups = DB::table('PersonGroup')
            ->where('PersonID', $meId)
            ->pluck('GroupID')
            ->toArray();

        $events    = collect();
        $tableRows = [];

        // Events in season that overlap with my groups via EventQetaa ↔ GroupQetaa
        if ($seasonId && !empty($myGroups)) {
            $events = DB::table('SeasonEvent as se')
                ->join('Event as e', 'e.EventID', '=', 'se.EventID')
                ->where('se.SeasonID', $seasonId)
                ->whereExists(function ($q) use ($myGroups) {
                    $q->select(DB::raw(1))
                        ->from('EventQetaa as eq')
                        ->join('GroupQetaa as gq', 'gq.QetaaID', '=', 'eq.QetaaID')
                        ->whereColumn('eq.EventID', 'se.EventID')
                        ->whereIn('gq.GroupID', $myGroups)
                        ->limit(1);
                })
                ->select('se.SeasonEventID', 'se.SeasonID', 'e.EventID', 'e.EventName', 'e.EventStartDate', 'e.EventEndDate')
                ->orderBy('e.EventStartDate', 'asc')
                ->get();
        }

        // Persons + their current attendance status for this event
        if ($seasonEventId && !empty($myGroups)) {
            $eventId = DB::table('SeasonEvent')
                ->where('SeasonEventID', $seasonEventId)
                ->value('EventID');

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
                    ->join('PersonInformation as p', 'p.PersonID', '=', 'pq.PersonID')
                    ->leftJoin('PersonPhoneNumbers as ph', 'ph.PersonID', '=', 'p.PersonID')
                    ->leftJoin('PersonSanaMarhala as psm', 'psm.PersonID', '=', 'p.PersonID')
                    ->leftJoin('SanaMarhala as sm', 'sm.SanaMarhalaID', '=', 'psm.SanaMarhalaID')
                    ->leftJoin('Qetaa as q', 'q.QetaaID', '=', 'pq.QetaaID')
                    ->whereIn('pq.QetaaID', $allowedQetaas)
                    ->groupBy('p.PersonID', 'p.FirstName', 'p.SecondName', 'p.ThirdName', 'p.FourthName', 'sm.SanaMarhalaName')
                    ->selectRaw("
                        p.PersonID,
                        p.FirstName, p.SecondName, p.ThirdName, p.FourthName,
                        COALESCE(MAX(ph.PersonPersonalMobileNumber), '') as PhoneNumber,
                        COALESCE(GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ', '), '') as QetaaName,
                        COALESCE(sm.SanaMarhalaName, '') as SanaMarhalaName
                    ")
                    ->orderBy('p.FirstName')
                    ->get();

                // Fetch existing attendance records keyed by PersonID
                // so we can show the current status without affecting other users' saved data
                $attendanceMap = DB::table('Attendance')
                    ->where('SeasonEventID', $seasonEventId)
                    ->get(['ServedID', 'AttendanceStatus', 'Excuse'])
                    ->keyBy('ServedID');

                $tableRows = $persons->map(function ($p) use ($attendanceMap) {
                    $fullName  = trim("{$p->FirstName} {$p->SecondName} {$p->ThirdName} {$p->FourthName}");
                    $record    = $attendanceMap->get($p->PersonID);

                    return [
                        'PersonID'        => (int) $p->PersonID,
                        'PersonName'      => $fullName,
                        'PhoneNumber'     => $p->PhoneNumber,
                        'QetaaName'       => $p->QetaaName,
                        'SanaMarhalaName' => $p->SanaMarhalaName,
                        // Default to 'absent' if no record exists yet
                        'Status'          => $record?->AttendanceStatus ?? 'absent',
                        'Excuse'          => $record?->Excuse ?? '',
                    ];
                })->toArray();
            }
        }

        return view('attendance.manage', [
            'seasons'       => $seasons,
            'events'        => $events,
            'seasonId'      => $seasonId,
            'seasonEventId' => $seasonEventId,
            'tableRows'     => $tableRows,
            'me'            => $me,
        ]);
    }

    public function save(Request $request, $seasonEventId)
    {
        $serventId = optional(Auth::user())->PersonID ?? Auth::id();

        $request->validate([
            'attendance'             => 'array',
            'attendance.*.status'    => 'required|in:present,absent,excused',
            'attendance.*.excuse'    => 'nullable|string|max:1000',
        ]);

        $allowedQetaas = $this->allowedQetaas((int) $serventId, (int) $seasonEventId);
        if (empty($allowedQetaas)) {
            abort(403, 'Not allowed to take attendance for this event');
        }

        $allowedPersonIds = DB::table('PersonQetaa')
            ->whereIn('QetaaID', $allowedQetaas)
            ->pluck('PersonID')
            ->map(fn ($v) => (int) $v)
            ->flip()
            ->toArray();

        $rows = [];

        foreach ((array) $request->input('attendance', []) as $personId => $data) {
            $personId = (int) $personId;

            // Silently skip any PersonID the servant has no authority over
            if (! isset($allowedPersonIds[$personId])) {
                continue;
            }

            $status = $data['status'];
            $excuse = ($status === 'excused') ? ($data['excuse'] ?? null) : null;

            $rows[] = [
                'SeasonEventID'    => (int) $seasonEventId,
                'ServedID'         => $personId,
                'ServentID'        => (int) $serventId,
                'AttendanceStatus' => $status,
                'Excuse'           => $excuse,
            ];
        }

        if (!empty($rows)) {
            // INSERT ... ON DUPLICATE KEY UPDATE
            // Safe for multiple users saving at the same time:
            // each row is an independent atomic upsert on (SeasonEventID, ServedID)
            // No full-table delete — other users' rows are never touched
            DB::table('Attendance')->upsert(
                $rows,
                ['SeasonEventID', 'ServedID'],          // unique key columns
                ['ServentID', 'AttendanceStatus', 'Excuse'] // columns to update on conflict
            );
        }

        return redirect()->route('attendance.manage', [
            'season_id'       => $request->season_id,
            'season_event_id' => $seasonEventId,
        ])->with('success', 'تم حفظ الحضور بنجاح');
    }

    /**
     * Intersection of the servant's Qetaas (via groups) and the event's Qetaas.
     * Mirrors AttendanceApiController::allowedQetaas.
     */
    private function allowedQetaas(int $serventId, int $seasonEventId): array
    {
        $eventId = DB::table('SeasonEvent')
            ->where('SeasonEventID', $seasonEventId)
            ->value('EventID');

        if (! $eventId) {
            return [];
        }

        $myGroups = DB::table('PersonGroup')
            ->where('PersonID', $serventId)
            ->pluck('GroupID')
            ->toArray();

        if (empty($myGroups)) {
            return [];
        }

        $myQetaas = DB::table('GroupQetaa')
            ->whereIn('GroupID', $myGroups)
            ->pluck('QetaaID')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $eventQetaas = DB::table('EventQetaa')
            ->where('EventID', $eventId)
            ->pluck('QetaaID')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        return array_values(array_intersect($myQetaas, $eventQetaas));
    }
}