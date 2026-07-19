<?php

namespace App\Http\Controllers;

use App\Jobs\SendAttendanceQrWhatsApp;
use App\Services\AttendanceQrService;
use App\Services\BookingAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceQrService $qr,
        private readonly BookingAttendanceService $bookingAttendance,
    ) {}

    public function manage(Request $request): View
    {
        $me = Auth::user();
        $meId = (int) (optional($me)->PersonID ?? Auth::id());
        $ctx = $this->selectionContext($request, $meId);

        $tableRows = [];
        if ($ctx['seasonEventId'] && ! empty($ctx['allowedQetaas']) && ! $ctx['takesReservation']) {
            $tableRows = $this->rosterRows((int) $ctx['seasonEventId'], $ctx['allowedQetaas']);
        }

        return view('attendance.manage', [
            'seasons' => $ctx['seasons'],
            'events' => $ctx['events'],
            'seasonId' => $ctx['seasonId'],
            'seasonEventId' => $ctx['seasonEventId'],
            'takesReservation' => $ctx['takesReservation'],
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
            'takesReservation' => $ctx['takesReservation'],
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

        $seasonEventId = (int) $data['season_event_id'];
        if (! $this->canAccessEvent($meId, $seasonEventId)) {
            return response()->json(['ok' => false, 'error' => __('Not allowed to take attendance for this event')], 403);
        }

        if ($this->qr->eventTakesReservation($seasonEventId)) {
            return $this->lookupReservation($seasonEventId, $data['code']);
        }

        return $this->lookupAttendanceOnly($meId, $seasonEventId, $data['code']);
    }

    public function markPresent(Request $request): JsonResponse
    {
        $request->merge(['status' => 'present']);

        return $this->markStatus($request);
    }

    public function markStatus(Request $request): JsonResponse
    {
        $meId = (int) (optional(Auth::user())->PersonID ?? Auth::id());
        $data = $request->validate([
            'season_event_id' => 'required|integer',
            'status' => 'required|in:present,absent,outside,excused',
            'person_id' => 'nullable|integer',
            'booking_id' => 'nullable|integer',
        ]);

        $seasonEventId = (int) $data['season_event_id'];
        if (! $this->canAccessEvent($meId, $seasonEventId)) {
            return response()->json(['ok' => false, 'error' => __('Not allowed to take attendance for this event')], 403);
        }

        if ($this->qr->eventTakesReservation($seasonEventId)) {
            if (empty($data['booking_id'])) {
                return response()->json(['ok' => false, 'error' => __('No active booking found for this QR code.')], 422);
            }
            if (! in_array($data['status'], BookingAttendanceService::STATUSES, true)) {
                return response()->json(['ok' => false, 'error' => __('Invalid attendance status.')], 422);
            }

            $result = $this->bookingAttendance->mark(
                $seasonEventId,
                (int) $data['booking_id'],
                $data['status'],
                $meId
            );

            return response()->json($result, $result['ok'] ? 200 : 422);
        }

        $personId = (int) ($data['person_id'] ?? 0);
        if ($personId <= 0) {
            return response()->json(['ok' => false, 'error' => __('Person not found.')], 422);
        }

        $status = $data['status'];
        if (! in_array($status, ['present', 'absent', 'excused'], true)) {
            return response()->json(['ok' => false, 'error' => __('Invalid attendance status.')], 422);
        }

        $allowedQetaas = $this->allowedQetaas($meId, $seasonEventId);
        $allowed = ! empty($allowedQetaas) && DB::table('PersonQetaa')
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
                'AttendanceStatus' => $status,
                'Excuse' => null,
            ]],
            ['SeasonEventID', 'ServedID'],
            ['ServentID', 'AttendanceStatus', 'Excuse']
        );

        return response()->json([
            'ok' => true,
            'message' => __('Attendance updated successfully.'),
            'status' => $status,
        ]);
    }

    public function sendQr(Request $request, int $personId)
    {
        return $this->sendEntityQr($request, AttendanceQrService::TYPE_PERSON, $personId);
    }

    public function sendEntityQr(Request $request, string $type, int $id)
    {
        $meId = (int) (optional(Auth::user())->PersonID ?? Auth::id());
        $data = $request->validate([
            'season_event_id' => 'nullable|integer',
        ]);

        $type = strtoupper($type);
        if (! in_array($type, [
            AttendanceQrService::TYPE_PERSON,
            AttendanceQrService::TYPE_GUEST,
            AttendanceQrService::TYPE_FAMILY,
        ], true)) {
            return back()->with('error', __('Invalid QR code.'));
        }

        $seasonEventId = isset($data['season_event_id']) ? (int) $data['season_event_id'] : null;
        if ($seasonEventId && ! $this->canAccessEvent($meId, $seasonEventId)) {
            return back()->with('error', __('Not allowed to take attendance for this event'));
        }

        if ($seasonEventId && $type === AttendanceQrService::TYPE_PERSON && ! $this->qr->eventTakesReservation($seasonEventId)) {
            $allowedQetaas = $this->allowedQetaas($meId, $seasonEventId);
            $allowed = ! empty($allowedQetaas) && DB::table('PersonQetaa')
                ->where('PersonID', $id)
                ->whereIn('QetaaID', $allowedQetaas)
                ->exists();
            if (! $allowed) {
                return back()->with('error', __('This person is not in your authorized sectors for this event.'));
            }
        }

        $eventName = $seasonEventId ? $this->qr->eventName($seasonEventId) : null;

        try {
            $this->qr->sendEntityQrViaWhatsApp($type, $id, $eventName);
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
        if (! $this->canAccessEvent($meId, $seasonEventId)) {
            return back()->with('error', __('Not allowed to take attendance for this event'));
        }

        $eventName = $this->qr->eventName($seasonEventId);
        $queued = 0;

        if ($this->qr->eventTakesReservation($seasonEventId)) {
            $bookings = DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventID', $seasonEventId)
                ->where('IsRefunded', 0)
                ->get(['PersonID', 'GuestID', 'FamilyID']);

            foreach ($bookings as $booking) {
                $entity = $this->qr->entityFromBooking($booking);
                if (! $entity) {
                    continue;
                }
                $card = $this->qr->entityCard($entity['type'], $entity['id']);
                if (! $card || $card['PhoneNumber'] === '') {
                    continue;
                }
                SendAttendanceQrWhatsApp::dispatch($entity['type'], $entity['id'], $eventName);
                $queued++;
            }
        } else {
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

            foreach ($personIds as $personId) {
                $phone = DB::table('PersonPhoneNumbers')
                    ->where('PersonID', $personId)
                    ->value('PersonPersonalMobileNumber');
                if (! $phone) {
                    continue;
                }
                SendAttendanceQrWhatsApp::dispatch(
                    AttendanceQrService::TYPE_PERSON,
                    $personId,
                    $eventName
                );
                $queued++;
            }
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

        if ($this->qr->eventTakesReservation((int) $seasonEventId)) {
            return redirect()->route('attendance.scan', [
                'season_id' => $request->season_id,
                'season_event_id' => $seasonEventId,
            ])->with('error', __('Use scan attendance for reservation events.'));
        }

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

    private function lookupReservation(int $seasonEventId, string $code): JsonResponse
    {
        $result = $this->bookingAttendance->lookup($seasonEventId, $code);
        if (! $result) {
            $parsed = $this->qr->parseCode($code);
            if (! $parsed) {
                return response()->json(['ok' => false, 'error' => __('Invalid QR code.')], 422);
            }

            return response()->json(['ok' => false, 'error' => __('No active booking found for this QR code.')], 404);
        }

        $card = $result['card'];

        return response()->json([
            'ok' => true,
            'mode' => 'reservation',
            'booking_id' => (int) $result['booking']->SeasonEventParticipantFinanceID,
            'person' => [
                'PersonID' => $card['EntityID'],
                'PersonName' => $card['EntityName'],
                'PhoneNumber' => $card['PhoneNumber'],
                'QetaaName' => $card['QetaaName'],
                'SanaMarhalaName' => $card['SanaMarhalaName'],
                'EntityType' => $card['EntityType'],
                'BookingTypeLabel' => $card['BookingTypeLabel'],
            ],
            'status' => $result['status'],
        ]);
    }

    private function lookupAttendanceOnly(int $meId, int $seasonEventId, string $code): JsonResponse
    {
        $personId = $this->qr->parsePersonId($code);
        if (! $personId) {
            return response()->json(['ok' => false, 'error' => __('Invalid QR code.')], 422);
        }

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

        $card = $this->qr->personCard($personId);
        if (! $card) {
            return response()->json(['ok' => false, 'error' => __('Person not found.')], 404);
        }

        $status = DB::table('Attendance')
            ->where('SeasonEventID', $seasonEventId)
            ->where('ServedID', $personId)
            ->value('AttendanceStatus');

        return response()->json([
            'ok' => true,
            'mode' => 'attendance',
            'person' => array_merge($card, [
                'EntityType' => AttendanceQrService::TYPE_PERSON,
                'BookingTypeLabel' => __('Person'),
            ]),
            'status' => $status ?: null,
        ]);
    }

    /**
     * @return array{seasons: \Illuminate\Support\Collection, events: \Illuminate\Support\Collection, seasonId: mixed, seasonEventId: mixed, allowedQetaas: array<int>, takesReservation: bool}
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
                ->leftJoin('EventType as et', 'et.EventTypeID', '=', 'e.EventTypeID')
                ->where('se.SeasonID', $seasonId)
                ->whereExists(function ($q) use ($myGroups) {
                    $q->select(DB::raw(1))
                        ->from('EventQetaa as eq')
                        ->join('GroupQetaa as gq', 'gq.QetaaID', '=', 'eq.QetaaID')
                        ->whereColumn('eq.EventID', 'se.EventID')
                        ->whereIn('gq.GroupID', $myGroups)
                        ->limit(1);
                })
                ->select(
                    'se.SeasonEventID',
                    'se.SeasonID',
                    'e.EventID',
                    'e.EventName',
                    'e.EventStartDate',
                    'e.EventEndDate',
                    DB::raw('COALESCE(et.TakesReservation, 0) as TakesReservation')
                )
                ->orderBy('e.EventStartDate', 'asc')
                ->get();
        }

        $allowedQetaas = [];
        $takesReservation = false;
        if ($seasonEventId && ! empty($myGroups)) {
            $allowedQetaas = $this->allowedQetaas($meId, (int) $seasonEventId);
            $takesReservation = $this->qr->eventTakesReservation((int) $seasonEventId);
        }

        return [
            'seasons' => $seasons,
            'events' => $events,
            'seasonId' => $seasonId,
            'seasonEventId' => $seasonEventId,
            'allowedQetaas' => $allowedQetaas,
            'takesReservation' => $takesReservation,
        ];
    }

    private function canAccessEvent(int $serventId, int $seasonEventId): bool
    {
        return ! empty($this->allowedQetaas($serventId, $seasonEventId));
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
