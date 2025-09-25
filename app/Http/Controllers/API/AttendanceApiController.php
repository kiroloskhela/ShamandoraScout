<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AttendanceApiController extends Controller
{
    /**
     * GET /api/attendance/events
     *
     * Modes:
     *  - ?person_id=55  → returns seasons[] each with its allowed events[] (for that person)
     *  - ?season_id=3   → returns flat events[] for that season (for the authenticated user)
     */
    public function events(Request $request)
    {
        $authPersonId = optional(Auth::user())->PersonID ?? Auth::id();
        if (!$authPersonId) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // A) seasons + events for a specific person
        if ($request->has('person_id')) {
            $personId = (int) $request->query('person_id');

            // Safety: only allow requesting your own person_id (adjust if you want admin override)
            if ($personId !== (int) $authPersonId) {
                return response()->json(['ok' => false, 'message' => 'Forbidden: person_id mismatch'], 403);
            }

            $myGroups = $this->getServantGroups($personId);
            if (empty($myGroups)) {
                return response()->json(['ok' => true, 'seasons' => []]);
            }

            $rows = DB::table('SeasonEvent as se')
                ->join('Event as e', 'e.EventID', '=', 'se.EventID')
                ->join('Season as s', 's.SeasonID', '=', 'se.SeasonID')
                ->whereExists(function ($q) use ($myGroups) {
                    $q->select(DB::raw(1))
                        ->from('EventQetaa as eq')
                        ->join('GroupQetaa as gq', 'gq.QetaaID', '=', 'eq.QetaaID')
                        ->whereColumn('eq.EventID', 'se.EventID')
                        ->whereIn('gq.GroupID', $myGroups)
                        ->limit(1);
                })
                ->select(
                    's.SeasonID',
                    's.SeasonName',
                    's.SeasonYear',
                    'se.SeasonEventID',
                    'e.EventID',
                    'e.EventName',
                    'e.EventStartDate',
                    'e.EventEndDate'
                )
                ->orderBy('s.SeasonYear', 'desc')
                ->orderBy('e.EventStartDate', 'asc')
                ->get();

            // group by season
            $bySeason = [];
            foreach ($rows as $r) {
                $sid = $r->SeasonID;
                if (!isset($bySeason[$sid])) {
                    $bySeason[$sid] = [
                        'SeasonID'   => $r->SeasonID,
                        'SeasonName' => $r->SeasonName,
                        'SeasonYear' => $r->SeasonYear,
                        'events'     => [],
                    ];
                }
                $bySeason[$sid]['events'][] = [
                    'SeasonEventID'  => $r->SeasonEventID,
                    'EventID'        => $r->EventID,
                    'EventName'      => $r->EventName,
                    'EventStartDate' => $r->EventStartDate,
                    'EventEndDate'   => $r->EventEndDate,
                ];
            }

            return response()->json(['ok' => true, 'seasons' => array_values($bySeason)]);
        }

        // B) events for a single season (auth user)
        $request->validate(['season_id' => 'required|integer']);
        $seasonId = (int) $request->query('season_id');

        $myGroups = $this->getServantGroups((int) $authPersonId);
        if (empty($myGroups)) {
            return response()->json(['ok' => true, 'events' => []]);
        }

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

        return response()->json(['ok' => true, 'events' => $events]);
    }

    /**
     * GET /api/attendance/persons?season_event_id=999  (legacy)
     * Returns ONLY persons (from PersonQetaa) the auth user is allowed to manage for this event.
     */
    public function persons(Request $request)
    {
        $request->validate(['season_event_id' => 'required|integer']);
        $seasonEventId = (int) $request->query('season_event_id');

        $authPersonId = optional(Auth::user())->PersonID ?? Auth::id();
        if (!$authPersonId) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return $this->buildPersonsResponse((int) $authPersonId, $seasonEventId);
    }

    /**
     * GET /api/attendance/persons/{seasonEventId}  (recommended path param)
     */
    public function personsBySeasonEventId(int $seasonEventId)
    {
        $authPersonId = optional(Auth::user())->PersonID ?? Auth::id();
        if (!$authPersonId) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        return $this->buildPersonsResponse((int) $authPersonId, (int) $seasonEventId);
    }

    /**
     * POST /api/attendance/save
     * Body JSON:
     * {
     *   "SeasonEventID": 999,
     *   "ServentID": 55,
     *   "Served": [101,102,...]
     * }
     * Saves attendance ONLY for persons the servant is authorized to manage (doesn't overwrite other servants' subsets).
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'SeasonEventID' => 'required|integer|exists:SeasonEvent,SeasonEventID',
            'ServentID'     => 'required|integer|exists:PersonInformation,PersonID',
            'Served'        => 'array',
            'Served.*'      => 'integer|exists:PersonInformation,PersonID',
        ]);

        $authPersonId = optional(Auth::user())->PersonID ?? Auth::id();
        if (!$authPersonId) {
            return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
        }

        // Tight policy: the caller can only save for themselves
        if ((int) $data['ServentID'] !== (int) $authPersonId) {
            return response()->json(['ok' => false, 'message' => 'Forbidden: ServentID mismatch'], 403);
        }

        $seasonEventId = (int) $data['SeasonEventID'];
        $serventId     = (int) $data['ServentID'];
        $servedInput   = array_map('intval', $data['Served'] ?? []);

        // Find which Qetaas this servant can act on for this event
        $allowedQetaas = $this->allowedQetaas($serventId, $seasonEventId);
        if (empty($allowedQetaas)) {
            return response()->json(['ok' => false, 'message' => 'Not allowed to take attendance for this event'], 403);
        }

        // Allowed persons are those in PersonQetaa within allowed Qetaas
        $allowedPersonIds = DB::table('PersonQetaa')
            ->whereIn('QetaaID', $allowedQetaas)
            ->pluck('PersonID')
            ->unique()
            ->map(fn ($v) => (int) $v)
            ->toArray();

        // Keep only allowed persons from the provided list
        $served = array_values(array_intersect($servedInput, $allowedPersonIds));

        DB::beginTransaction();
        try {
            // Remove existing attendance ONLY for the subset this servant controls
            if (!empty($allowedPersonIds)) {
                DB::table('Attendance')
                    ->where('SeasonEventID', $seasonEventId)
                    ->whereIn('ServedID', $allowedPersonIds)
                    ->delete();
            }

            // Insert new attendance for selected persons
            if (!empty($served)) {
                $rows = array_map(function ($pid) use ($seasonEventId, $serventId) {
                    return [
                        'SeasonEventID' => $seasonEventId,
                        'ServedID'      => (int) $pid,
                        'ServentID'     => $serventId,
                    ];
                }, $served);

                DB::table('Attendance')->insert($rows);
            }

            DB::commit();
            return response()->json([
                'ok'      => true,
                'message' => 'Attendance saved',
                'count'   => count($served),
                'served'  => $served,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'ok'      => false,
                'message' => 'Failed to save attendance',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ===================== helpers =====================

    /** Groups the authenticated servant belongs to. */
    private function getServantGroups(int $serventId): array
    {
        return DB::table('PersonGroup')
            ->where('PersonID', $serventId)
            ->pluck('GroupID')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }

    /** Intersection of servant Qetaas and event Qetaas for a SeasonEvent. */
    private function allowedQetaas(int $serventId, int $seasonEventId): array
    {
        $eventId = DB::table('SeasonEvent')
            ->where('SeasonEventID', $seasonEventId)
            ->value('EventID');

        if (!$eventId) {
            return [];
        }

        $myQetaas = DB::table('GroupQetaa')
            ->whereIn('GroupID', $this->getServantGroups($serventId))
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

    /** Build persons list (and attended flag) for auth user and a SeasonEvent. */
    private function buildPersonsResponse(int $authPersonId, int $seasonEventId)
    {
        // Allowed Qetaas = intersection(my qetaas, event qetaas)
        $allowedQetaas = $this->allowedQetaas($authPersonId, $seasonEventId);
        if (empty($allowedQetaas)) {
            return response()->json(['ok' => true, 'persons' => []]);
        }

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
                COALESCE(MAX(ph.PersonPersonalMobileNumber),'') as PhoneNumber,
                COALESCE(GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ', '),'') as QetaaName,
                COALESCE(sm.SanaMarhalaName,'') as SanaMarhalaName
            ")
            ->orderBy('p.FirstName')
            ->get();

        $present = DB::table('Attendance')
            ->where('SeasonEventID', $seasonEventId)
            ->pluck('ServedID')
            ->toArray();
        $presentSet = array_flip($present);

        $rows = $persons->map(function ($p) use ($presentSet) {
            return [
                'PersonID'        => (int) $p->PersonID,
                'PersonName'      => trim("{$p->FirstName} {$p->SecondName} {$p->ThirdName} {$p->FourthName}"),
                'PhoneNumber'     => $p->PhoneNumber,
                'QetaaName'       => $p->QetaaName,
                'SanaMarhalaName' => $p->SanaMarhalaName,
                'Attended'        => isset($presentSet[$p->PersonID]),
            ];
        });

        return response()->json(['ok' => true, 'persons' => $rows]);
    }
}