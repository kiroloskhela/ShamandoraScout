<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttendanceSaveRequest;
use App\Http\Resources\AttendancePersonResource;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Attendance API — mobile-facing endpoints.
 *
 * All responses follow the envelope:  { ok: bool, ...payload }
 * Errors add a `message` key; 4xx/5xx status codes are also set.
 *
 * Auth: expects a token-authenticated user whose model exposes `PersonID`.
 */
class AttendanceApiController extends Controller
{
    // =========================================================================
    //  GET /api/attendance/events
    //
    //  Query params:
    //    person_id   (optional)  – fetch all seasons+events for that person
    //                              (must match the authenticated user's PersonID)
    //    season_id   (required if person_id absent) – events for one season
    //
    //  Response A  (person_id given):
    //    { ok: true, seasons: [ { SeasonID, SeasonName, SeasonYear,
    //                             events: [ { SeasonEventID, EventID,
    //                                         EventName, EventStartDate, EventEndDate } ] } ] }
    //
    //  Response B  (season_id given):
    //    { ok: true, events: [ { SeasonEventID, EventID,
    //                            EventName, EventStartDate, EventEndDate } ] }
    // =========================================================================
    public function events(Request $request)
    {
        $authPersonId = $this->resolveAuthPersonId();
        if (! $authPersonId) {
            return $this->unauthorized();
        }

        // ── A) all seasons + events for a specific person ──────────────────
        if ($request->has('person_id')) {
            $personId = (int) $request->query('person_id');

            if ($personId !== $authPersonId) {
                return $this->forbidden('person_id mismatch');
            }

            $myGroups = $this->getServantGroups($personId);
            if (empty($myGroups)) {
                return response()->json(['ok' => true, 'seasons' => []]);
            }

            $rows = DB::table('SeasonEvent as se')
                ->join('Event as e', 'e.EventID', '=', 'se.EventID')
                ->join('Season as s', 's.SeasonID', '=', 'se.SeasonID')
                ->whereExists(fn ($q) => $this->scopeToMyGroups($q, $myGroups))
                ->select(
                    's.SeasonID', 's.SeasonName', 's.SeasonYear',
                    'se.SeasonEventID',
                    'e.EventID', 'e.EventName', 'e.EventStartDate', 'e.EventEndDate'
                )
                ->orderBy('s.SeasonYear', 'desc')
                ->orderBy('e.EventStartDate', 'desc')
                ->get();

            $bySeason = [];
            foreach ($rows as $r) {
                $sid = $r->SeasonID;
                $bySeason[$sid] ??= [
                    'SeasonID' => $r->SeasonID,
                    'SeasonName' => $r->SeasonName,
                    'SeasonYear' => $r->SeasonYear,
                    'events' => [],
                ];
                $bySeason[$sid]['events'][] = $this->formatEvent($r);
            }

            return response()->json(['ok' => true, 'seasons' => array_values($bySeason)]);
        }

        // ── B) events for a single season ──────────────────────────────────
        $request->validate(['season_id' => 'required|integer']);
        $seasonId = (int) $request->query('season_id');

        $myGroups = $this->getServantGroups($authPersonId);
        if (empty($myGroups)) {
            return response()->json(['ok' => true, 'events' => []]);
        }

        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->where('se.SeasonID', $seasonId)
            ->whereExists(fn ($q) => $this->scopeToMyGroups($q, $myGroups))
            ->select('se.SeasonEventID', 'se.SeasonID', 'e.EventID', 'e.EventName', 'e.EventStartDate', 'e.EventEndDate')
            ->orderBy('e.EventStartDate', 'desc')
            ->get()
            ->map(fn ($r) => $this->formatEvent($r));

        return response()->json(['ok' => true, 'events' => $events]);
    }

    // =========================================================================
    //  GET /api/attendance/persons?season_event_id={id}
    //  GET /api/attendance/{seasonEventId}/persons          ← route-param alias
    //
    //  Response:
    //    { ok: true, persons: [ { PersonID, PersonName, PhoneNumber,
    //                             QetaaName, SanaMarhalaName,
    //                             Status: "present"|"absent"|"excused",
    //                             Excuse: string|null } ] }
    // =========================================================================
    public function persons(Request $request)
    {
        $request->validate(['season_event_id' => 'required|integer']);
        $seasonEventId = (int) $request->query('season_event_id');

        return $this->buildPersonsResponse($seasonEventId);
    }

    /** Route-param alias: GET /api/attendance/{seasonEventId}/persons */
    public function personsBySeasonEventId(int $seasonEventId)
    {
        return $this->buildPersonsResponse($seasonEventId);
    }

    // =========================================================================
    //  POST /api/attendance/save
    //
    //  Body (JSON):
    //    {
    //      "SeasonEventID": 12,
    //      "attendance": {
    //        "101": { "status": "present",  "excuse": null },
    //        "102": { "status": "excused",  "excuse": "sick" },
    //        "103": { "status": "absent",   "excuse": null }
    //      }
    //    }
    //
    //  Rules (mirrors the web controller exactly):
    //   • Uses INSERT … ON DUPLICATE KEY UPDATE (upsert) — never a full delete.
    //   • Only rows for PersonIDs within the servant's allowed Qetaas are written.
    //   • Concurrent saves from different servants do not overwrite each other.
    //   • `excuse` is only persisted when status = "excused"; otherwise NULL.
    //
    //  Response:
    //    { ok: true, message: "Attendance saved", count: 3,
    //      saved: [ { PersonID, Status, Excuse } ],
    //      skipped: [ PersonID, ... ] }
    // =========================================================================
    public function save(StoreAttendanceSaveRequest $request)
    {
        $authPersonId = $this->resolveAuthPersonId();
        if (! $authPersonId) {
            return $this->unauthorized();
        }

        $data = $request->validated();

        $seasonEventId = (int) $data['SeasonEventID'];
        $serventId = $authPersonId;

        // Authorisation: which Qetaas can this servant record for this event?
        $allowedQetaas = $this->allowedQetaas($serventId, $seasonEventId);
        if (empty($allowedQetaas)) {
            return $this->forbidden('Not allowed to take attendance for this event');
        }

        // The full set of PersonIDs this servant is authorised to write
        $allowedPersonIds = DB::table('PersonQetaa')
            ->whereIn('QetaaID', $allowedQetaas)
            ->pluck('PersonID')
            ->map(fn ($v) => (int) $v)
            ->flip()           // use as a set for O(1) lookup
            ->toArray();

        $rows = [];
        $saved = [];
        $skipped = [];

        foreach ((array) $data['attendance'] as $personId => $entry) {
            $personId = (int) $personId;

            // Skip (and report) any PersonID the servant has no authority over
            if (! isset($allowedPersonIds[$personId])) {
                $skipped[] = $personId;

                continue;
            }

            $status = $entry['status'];
            $excuse = ($status === 'excused') ? ($entry['excuse'] ?? null) : null;

            $rows[] = [
                'SeasonEventID' => $seasonEventId,
                'ServedID' => $personId,
                'ServentID' => $serventId,
                'AttendanceStatus' => $status,
                'Excuse' => $excuse,
            ];

            $saved[] = [
                'PersonID' => $personId,
                'Status' => $status,
                'Excuse' => $excuse,
            ];
        }

        if (! empty($rows)) {
            // Safe concurrent upsert — identical to the web controller strategy.
            // Each (SeasonEventID, ServedID) pair is an independent atomic operation;
            // rows owned by other servants are never touched.
            DB::table('Attendance')->upsert(
                $rows,
                ['SeasonEventID', 'ServedID'],               // unique key
                ['ServentID', 'AttendanceStatus', 'Excuse']  // update on conflict
            );
        }

        return response()->json([
            'ok' => true,
            'message' => 'Attendance saved',
            'count' => count($saved),
            'saved' => $saved,
            'skipped' => $skipped,
        ]);
    }

    public function mine(Request $request)
    {
        $personId = $this->resolveAuthPersonId();
        if (! $personId) {
            return $this->unauthorized();
        }

        $perPage = min(50, max(1, (int) $request->query('per_page', 20)));

        $page = DB::table('Attendance as a')
            ->leftJoin('SeasonEvent as se', 'se.SeasonEventID', '=', 'a.SeasonEventID')
            ->leftJoin('Event as e', 'e.EventID', '=', 'se.EventID')
            ->where('a.ServedID', $personId)
            ->select(
                'a.AttendanceID',
                'a.SeasonEventID',
                'a.AttendanceStatus',
                'a.Excuse',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate'
            )
            ->orderByDesc('e.EventStartDate')
            ->orderByDesc('a.AttendanceID')
            ->paginate($perPage);

        return response()->json([
            'ok' => true,
            'data' => $page->items(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
        ]);
    }

    // =========================================================================
    //  HELPERS
    // =========================================================================

    /** Returns the PersonID of the authenticated user, or null. */
    private function resolveAuthPersonId(): ?int
    {
        $id = optional(Auth::user())->PersonID ?? Auth::id();

        return $id ? (int) $id : null;
    }

    /** GroupIDs the servant belongs to. */
    private function getServantGroups(int $serventId): array
    {
        return DB::table('PersonGroup')
            ->where('PersonID', $serventId)
            ->pluck('GroupID')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }

    /**
     * Intersection of the servant's Qetaas and the event's Qetaas.
     * Returns [] if the SeasonEventID is invalid or there is no overlap.
     */
    private function allowedQetaas(int $serventId, int $seasonEventId): array
    {
        $eventId = DB::table('SeasonEvent')
            ->where('SeasonEventID', $seasonEventId)
            ->value('EventID');

        if (! $eventId) {
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

    /**
     * Builds the persons list with Status + Excuse — mirrors the web controller.
     * Authorisation is enforced via allowedQetaas().
     */
    private function buildPersonsResponse(int $seasonEventId)
    {
        $authPersonId = $this->resolveAuthPersonId();
        if (! $authPersonId) {
            return $this->unauthorized();
        }

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
            ->groupBy(
                'p.PersonID', 'p.FirstName', 'p.SecondName',
                'p.ThirdName', 'p.FourthName', 'sm.SanaMarhalaName'
            )
            ->selectRaw("
                p.PersonID,
                p.FirstName, p.SecondName, p.ThirdName, p.FourthName,
                COALESCE(MAX(ph.PersonPersonalMobileNumber), '') AS PhoneNumber,
                COALESCE(GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ', '), '') AS QetaaName,
                COALESCE(sm.SanaMarhalaName, '') AS SanaMarhalaName
            ")
            ->orderBy('p.FirstName')
            ->get();

        // Keyed attendance map — matches the web controller's approach exactly
        $attendanceMap = DB::table('Attendance')
            ->where('SeasonEventID', $seasonEventId)
            ->get(['ServedID', 'AttendanceStatus', 'Excuse'])
            ->keyBy('ServedID');

        $rows = $persons->map(function ($p) use ($attendanceMap) {
            $record = $attendanceMap->get($p->PersonID);

            return [
                'PersonID' => (int) $p->PersonID,
                'PersonName' => trim("{$p->FirstName} {$p->SecondName} {$p->ThirdName} {$p->FourthName}"),
                'PhoneNumber' => $p->PhoneNumber,
                'QetaaName' => $p->QetaaName,
                'SanaMarhalaName' => $p->SanaMarhalaName,
                // Default to 'absent' when no record exists — identical to web controller
                'Status' => $record?->AttendanceStatus ?? 'absent',
                'Excuse' => $record?->Excuse ?? null,
            ];
        });

        return response()->json([
            'ok' => true,
            'persons' => AttendancePersonResource::collection($rows)->resolve(),
        ]);
    }

    /**
     * Reusable whereExists closure that filters SeasonEvents to only those
     * reachable through the servant's GroupIDs.
     */
    private function scopeToMyGroups(Builder $q, array $myGroups): void
    {
        $q->select(DB::raw(1))
            ->from('EventQetaa as eq')
            ->join('GroupQetaa as gq', 'gq.QetaaID', '=', 'eq.QetaaID')
            ->whereColumn('eq.EventID', 'se.EventID')
            ->whereIn('gq.GroupID', $myGroups)
            ->limit(1);
    }

    private function formatEvent(object $r): array
    {
        return [
            'SeasonEventID' => $r->SeasonEventID,
            'EventID' => $r->EventID,
            'EventName' => $r->EventName,
            'EventStartDate' => $r->EventStartDate,
            'EventEndDate' => $r->EventEndDate,
        ];
    }

    private function unauthorized()
    {
        return response()->json(['ok' => false, 'message' => 'Unauthorized'], 401);
    }

    private function forbidden(string $reason = 'Forbidden')
    {
        return response()->json(['ok' => false, 'message' => $reason], 403);
    }
}
