<?php

namespace App\Http\Controllers;

use App\Jobs\SendAttendanceQrWhatsApp;
use App\Services\AttendanceQrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceQrService $qr,
    ) {}

    public function manage(Request $request): View
    {
        $me = Auth::user();
        $meId = (int) (optional($me)->PersonID ?? Auth::id());
        $ctx = $this->selectionContext($request, $meId);

        $tableRows = [];
        if ($ctx['seasonEventId'] && ! empty($ctx['allowedQetaas'])) {
            $tableRows = $this->rosterRows((int) $ctx['seasonEventId'], $ctx['allowedQetaas']);
        }

        return view('attendance.manage', [
            'seasons' => $ctx['seasons'],
            'events' => $ctx['events'],
            'seasonId' => $ctx['seasonId'],
            'seasonEventId' => $ctx['seasonEventId'],
            'tableRows' => $tableRows,
            'me' => $me,
        ]);
    }

    public function scan(Request $request): View
    {
        $me = Auth::user();
        $meId = (int) (optional($me)->PersonID ?? Auth::id());
        $ctx = $this->selectionContext($request, $meId);

        return view('attendance.scan', [
            'seasons' => $ctx['seasons'],
            'events' => $ctx['events'],
            'seasonId' => $ctx['seasonId'],
            'seasonEventId' => $ctx['seasonEventId'],
            'me' => $me,
        ]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $meId = (int) (optional(Auth::user())->PersonID ?? Auth::id());
        $data = $request->validate([
            'season_event_id' => 'required|integer',
            'code' => 'required|string|max:255',
        ]);

        $personId = $this->qr->parsePersonId($data['code']);
        if (! $personId) {
            return response()->json(['ok' => false, 'error' => __('Invalid QR code.')], 422);
        }

        $allowedQetaas = $this->allowedQetaas($meId, (int) $data['season_event_id']);
        if (empty($allowedQetaas)) {
            return response()->json(['ok' => false, 'error' => __('Not allowed to take attendance for this event')], 403);
        }

        $allowed = DB::table('PersonQetaa')
            ->where('PersonID', $personId)
            ->whereIn('QetaaID', $allowedQetaas)
            ->exists();

        if (! $allowed) {
            return response()->json(['ok' => false, 'error' => __('This person is not in your authorized sectors for this event.')], 403);
        }

        $card = $this->qr->personCard($personId);
        if (! $card) {
            return response()->json(['ok' => false, 'error' => __('Person not found.')], 404);
        }

        $status = DB::table('Attendance')
            ->where('SeasonEventID', (int) $data['season_event_id'])
            ->where('ServedID', $personId)
            ->value('AttendanceStatus');

        return response()->json([
            'ok' => true,
            'person' => $card,
            'status' => $status ?: null,
        ]);
    }

    public function markPresent(Request $request): JsonResponse
    {
        $meId = (int) (optional(Auth::user())->PersonID ?? Auth::id());
        $data = $request->validate([
            'season_event_id' => 'required|integer',
            'person_id' => 'required|integer',
        ]);

        $seasonEventId = (int) $data['season_event_id'];
        $personId = (int) $data['person_id'];

        $allowedQetaas = $this->allowedQetaas($meId, $seasonEventId);
        if (empty($allowedQetaas)) {
            return response()->json(['ok' => false, 'error' => __('Not allowed to take attendance for this event')], 403);
        }

        $allowed = DB::table('PersonQetaa')
            ->where('PersonID', $personId)
            ->whereIn('QetaaID', $allowedQetaas)
            ->exists();

        if (! $allowed) {
            return response()->json(['ok' => false, 'error' => __('This person is not in your authorized sectors for this event.')], 403);
        }

        DB::table('Attendance')->upsert(
            [[
                'SeasonEventID' => $seasonEventId,
                'ServedID' => $personId,
                'ServentID' => $meId,
                'AttendanceStatus' => 'present',
                'Excuse' => null,
            ]],
            ['SeasonEventID', 'ServedID'],
            ['ServentID', 'AttendanceStatus', 'Excuse']
        );

        return response()->json([
            'ok' => true,
            'message' => __('Marked present successfully.'),
            'status' => 'present',
        ]);
    }

    public function sendQr(Request $request, int $personId)
    {
        $meId = (int) (optional(Auth::user())->PersonID ?? Auth::id());
        $data = $request->validate([
            'season_event_id' => 'nullable|integer',
        ]);

        $seasonEventId = isset($data['season_event_id']) ? (int) $data['season_event_id'] : null;
        if ($seasonEventId) {
            $allowedQetaas = $this->allowedQetaas($meId, $seasonEventId);
            $allowed = ! empty($allowedQetaas) && DB::table('PersonQetaa')
                ->where('PersonID', $personId)
                ->whereIn('QetaaID', $allowedQetaas)
                ->exists();
            if (! $allowed) {
                return back()->with('error', __('This person is not in your authorized sectors for this event.'));
            }
        }

        $eventName = $seasonEventId
            ? (string) DB::table('SeasonEvent as se')
                ->join('Event as e', 'e.EventID', '=', 'se.EventID')
                ->where('se.SeasonEventID', $seasonEventId)
                ->value('e.EventName')
            : null;

        try {
            $this->qr->sendQrViaWhatsApp($personId, $eventName ?: null);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('QR code sent via WhatsApp.'));
    }

    public function sendQrBulk(Request $request)
    {
        $meId = (int) (optional(Auth::user())->PersonID ?? Auth::id());
        $data = $request->validate([
            'season_id' => 'nullable|integer',
            'season_event_id' => 'required|integer',
        ]);

        $seasonEventId = (int) $data['season_event_id'];
        $allowedQetaas = $this->allowedQetaas($meId, $seasonEventId);
        if (empty($allowedQetaas)) {
            return back()->with('error', __('Not allowed to take attendance for this event'));
        }

        $personIds = DB::table('PersonQetaa')
            ->whereIn('QetaaID', $allowedQetaas)
            ->distinct()
            ->pluck('PersonID')
            ->map(fn ($id) => (int) $id)
            ->all();

        $eventName = (string) DB::table('SeasonEvent as se')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->where('se.SeasonEventID', $seasonEventId)
            ->value('e.EventName');

        $queued = 0;
        foreach ($personIds as $personId) {
            $phone = DB::table('PersonPhoneNumbers')
                ->where('PersonID', $personId)
                ->value('PersonPersonalMobileNumber');
            if (! $phone) {
                continue;
            }
            SendAttendanceQrWhatsApp::dispatch($personId, $eventName ?: null);
            $queued++;
        }

        return redirect()->route('attendance.scan', [
            'season_id' => $data['season_id'] ?? null,
            'season_event_id' => $seasonEventId,
        ])->with('success', __('Queued :count QR WhatsApp messages.', ['count' => $queued]));
    }

    public function save(Request $request, $seasonEventId)
    {
        $serventId = optional(Auth::user())->PersonID ?? Auth::id();

        $request->validate([
            'attendance' => 'array',
            'attendance.*.status' => 'required|in:present,absent,excused',
            'attendance.*.excuse' => 'nullable|string|max:1000',
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

            if (! isset($allowedPersonIds[$personId])) {
                continue;
            }

            $status = $data['status'];
            $excuse = ($status === 'excused') ? ($data['excuse'] ?? null) : null;

            $rows[] = [
                'SeasonEventID' => (int) $seasonEventId,
                'ServedID' => $personId,
                'ServentID' => (int) $serventId,
                'AttendanceStatus' => $status,
                'Excuse' => $excuse,
            ];
        }

        if (! empty($rows)) {
            DB::table('Attendance')->upsert(
                $rows,
                ['SeasonEventID', 'ServedID'],
                ['ServentID', 'AttendanceStatus', 'Excuse']
            );
        }

        return redirect()->route('attendance.manage', [
            'season_id' => $request->season_id,
            'season_event_id' => $seasonEventId,
        ])->with('success', 'تم حفظ الحضور بنجاح');
    }

    /**
     * @return array{seasons: \Illuminate\Support\Collection, events: \Illuminate\Support\Collection, seasonId: mixed, seasonEventId: mixed, allowedQetaas: array<int>}
     */
    private function selectionContext(Request $request, int $meId): array
    {
        $seasons = DB::table('Season')
            ->select('SeasonID', 'SeasonName', 'SeasonYear')
            ->orderBy('SeasonYear', 'desc')
            ->get();

        $seasonId = $request->get('season_id');
        $seasonEventId = $request->get('season_event_id');

        $myGroups = DB::table('PersonGroup')
            ->where('PersonID', $meId)
            ->pluck('GroupID')
            ->toArray();

        $events = collect();
        if ($seasonId && ! empty($myGroups)) {
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

        $allowedQetaas = [];
        if ($seasonEventId && ! empty($myGroups)) {
            $allowedQetaas = $this->allowedQetaas($meId, (int) $seasonEventId);
        }

        return [
            'seasons' => $seasons,
            'events' => $events,
            'seasonId' => $seasonId,
            'seasonEventId' => $seasonEventId,
            'allowedQetaas' => $allowedQetaas,
        ];
    }

    /**
     * @param  array<int>  $allowedQetaas
     * @return array<int, array<string, mixed>>
     */
    private function rosterRows(int $seasonEventId, array $allowedQetaas): array
    {
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

        $attendanceMap = DB::table('Attendance')
            ->where('SeasonEventID', $seasonEventId)
            ->get(['ServedID', 'AttendanceStatus', 'Excuse'])
            ->keyBy('ServedID');

        return $persons->map(function ($p) use ($attendanceMap) {
            $fullName = trim("{$p->FirstName} {$p->SecondName} {$p->ThirdName} {$p->FourthName}");
            $record = $attendanceMap->get($p->PersonID);

            return [
                'PersonID' => (int) $p->PersonID,
                'PersonName' => $fullName,
                'PhoneNumber' => $p->PhoneNumber,
                'QetaaName' => $p->QetaaName,
                'SanaMarhalaName' => $p->SanaMarhalaName,
                'Status' => $record?->AttendanceStatus ?? 'absent',
                'Excuse' => $record?->Excuse ?? '',
            ];
        })->toArray();
    }

    /**
     * Intersection of the servant's Qetaas (via groups) and the event's Qetaas.
     *
     * @return array<int>
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
