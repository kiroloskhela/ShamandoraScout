<?php

namespace App\Http\Controllers;

use App\Services\BookingAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceLiveController extends Controller
{
    public function __construct(
        private readonly BookingAttendanceService $bookingAttendance,
    ) {}

    public function index(Request $request): View
    {
        $seasons = DB::table('Season')
            ->select('SeasonID', 'SeasonName', 'SeasonYear')
            ->orderBy('SeasonYear', 'desc')
            ->get();

        $seasonId = $request->get('season_id');
        $seasonEventId = $request->get('season_event_id');

        $events = collect();
        if ($seasonId) {
            $events = DB::table('SeasonEvent as se')
                ->join('Event as e', 'e.EventID', '=', 'se.EventID')
                ->join('EventType as et', 'et.EventTypeID', '=', 'e.EventTypeID')
                ->join('SeasonEventFinance as sef', 'sef.SeasonEventID', '=', 'se.SeasonEventID')
                ->where('se.SeasonID', $seasonId)
                ->where('et.TakesReservation', 1)
                ->where('sef.SendQrWhatsApp', 1)
                ->select('se.SeasonEventID', 'e.EventName', 'e.EventStartDate', 'e.EventEndDate')
                ->orderBy('e.EventStartDate', 'asc')
                ->get();
        }

        $snapshot = null;
        if ($seasonEventId) {
            $snapshot = $this->buildSnapshot((int) $seasonEventId);
        }

        return view('attendance.live', [
            'seasons' => $seasons,
            'events' => $events,
            'seasonId' => $seasonId,
            'seasonEventId' => $seasonEventId,
            'snapshot' => $snapshot,
            'pusherKey' => config('broadcasting.connections.pusher.key'),
            'pusherCluster' => config('broadcasting.connections.pusher.options.cluster'),
            'pusherHost' => config('broadcasting.connections.pusher.options.host'),
            'pusherPort' => config('broadcasting.connections.pusher.options.port'),
            'pusherScheme' => config('broadcasting.connections.pusher.options.scheme'),
            'broadcastDriver' => config('broadcasting.default'),
        ]);
    }

    public function snapshot(Request $request): JsonResponse
    {
        $data = $request->validate([
            'season_event_id' => 'required|integer',
        ]);

        $seasonEventId = (int) $data['season_event_id'];
        if (! $this->isReservationEvent($seasonEventId)) {
            return response()->json(['ok' => false, 'error' => __('Use scan attendance for reservation events.')], 422);
        }

        return response()->json([
            'ok' => true,
            'snapshot' => $this->buildSnapshot($seasonEventId),
        ]);
    }

    /**
     * @return array{counts: array, feed: array, event_name: string|null}
     */
    private function buildSnapshot(int $seasonEventId): array
    {
        $eventName = DB::table('SeasonEvent as se')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->where('se.SeasonEventID', $seasonEventId)
            ->value('e.EventName');

        return [
            'event_name' => $eventName,
            'counts' => $this->bookingAttendance->counts($seasonEventId),
            'feed' => $this->bookingAttendance->recentFeed($seasonEventId),
            'roster' => $this->bookingAttendance->roster($seasonEventId),
        ];
    }

    private function isReservationEvent(int $seasonEventId): bool
    {
        return (bool) DB::table('SeasonEvent as se')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->join('EventType as et', 'et.EventTypeID', '=', 'e.EventTypeID')
            ->join('SeasonEventFinance as sef', 'sef.SeasonEventID', '=', 'se.SeasonEventID')
            ->where('se.SeasonEventID', $seasonEventId)
            ->where('et.TakesReservation', 1)
            ->where('sef.SendQrWhatsApp', 1)
            ->exists();
    }
}
