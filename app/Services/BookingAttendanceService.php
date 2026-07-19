<?php

namespace App\Services;

use App\Events\AttendanceMarked;
use Illuminate\Support\Facades\DB;

class BookingAttendanceService
{
    public const STATUSES = ['present', 'absent', 'outside'];

    public function __construct(
        private readonly AttendanceQrService $qr,
    ) {}

    public function findActiveBooking(int $seasonEventId, string $entityType, int $entityId): ?object
    {
        $query = DB::table('SeasonEventParticipantFinance as b')
            ->where('b.SeasonEventID', $seasonEventId)
            ->where('b.IsRefunded', 0);

        match (strtoupper($entityType)) {
            AttendanceQrService::TYPE_GUEST => $query->where('b.GuestID', $entityId),
            AttendanceQrService::TYPE_FAMILY => $query->where('b.FamilyID', $entityId),
            default => $query->where('b.PersonID', $entityId),
        };

        return $query->orderByDesc('b.SeasonEventParticipantFinanceID')->first();
    }

    /**
     * @return array{booking: object, card: array, status: string|null}|null
     */
    public function lookup(int $seasonEventId, string $code): ?array
    {
        $parsed = $this->qr->parseCode($code);
        if (! $parsed) {
            return null;
        }

        $booking = $this->findActiveBooking($seasonEventId, $parsed['type'], $parsed['id']);
        if (! $booking) {
            return null;
        }

        $card = $this->qr->entityCard($parsed['type'], $parsed['id']);
        if (! $card) {
            return null;
        }

        $status = DB::table('SeasonEventBookingAttendance')
            ->where('SeasonEventParticipantFinanceID', $booking->SeasonEventParticipantFinanceID)
            ->value('AttendanceStatus');

        return [
            'booking' => $booking,
            'card' => $card,
            'status' => $status ?: null,
        ];
    }

    /**
     * @return array{ok: bool, status?: string, message?: string, error?: string, payload?: array}
     */
    public function mark(int $seasonEventId, int $bookingId, string $status, int $serventId): array
    {
        if (! in_array($status, self::STATUSES, true)) {
            return ['ok' => false, 'error' => __('Invalid attendance status.')];
        }

        $booking = DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventParticipantFinanceID', $bookingId)
            ->where('SeasonEventID', $seasonEventId)
            ->where('IsRefunded', 0)
            ->first();

        if (! $booking) {
            return ['ok' => false, 'error' => __('No active booking found for this QR code.')];
        }

        $now = now();
        $existing = DB::table('SeasonEventBookingAttendance')
            ->where('SeasonEventParticipantFinanceID', $bookingId)
            ->first();

        if ($existing) {
            DB::table('SeasonEventBookingAttendance')
                ->where('SeasonEventBookingAttendanceID', $existing->SeasonEventBookingAttendanceID)
                ->update([
                    'AttendanceStatus' => $status,
                    'ServentID' => $serventId,
                    'UpdatedAt' => $now,
                ]);
        } else {
            DB::table('SeasonEventBookingAttendance')->insert([
                'SeasonEventParticipantFinanceID' => $bookingId,
                'SeasonEventID' => $seasonEventId,
                'AttendanceStatus' => $status,
                'ServentID' => $serventId,
                'CreatedAt' => $now,
                'UpdatedAt' => $now,
            ]);
        }

        $entity = $this->qr->entityFromBooking($booking);
        $card = $entity ? $this->qr->entityCard($entity['type'], $entity['id']) : null;

        $payload = [
            'season_event_id' => $seasonEventId,
            'booking_id' => $bookingId,
            'status' => $status,
            'entity_type' => $entity['type'] ?? null,
            'entity_id' => $entity['id'] ?? null,
            'name' => $card['EntityName'] ?? '',
            'booking_type_label' => $card['BookingTypeLabel'] ?? '',
            'updated_at' => $now->toIso8601String(),
            'counts' => $this->counts($seasonEventId),
        ];

        event(new AttendanceMarked($seasonEventId, $payload));

        return [
            'ok' => true,
            'status' => $status,
            'message' => __('Attendance updated successfully.'),
            'payload' => $payload,
        ];
    }

    /**
     * @return array{total: int, present: int, absent: int, outside: int, unmarked: int}
     */
    public function counts(int $seasonEventId): array
    {
        $total = (int) DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventID', $seasonEventId)
            ->where('IsRefunded', 0)
            ->count();

        $rows = DB::table('SeasonEventBookingAttendance')
            ->where('SeasonEventID', $seasonEventId)
            ->select('AttendanceStatus', DB::raw('COUNT(*) as c'))
            ->groupBy('AttendanceStatus')
            ->pluck('c', 'AttendanceStatus');

        $present = (int) ($rows['present'] ?? 0);
        $absent = (int) ($rows['absent'] ?? 0);
        $outside = (int) ($rows['outside'] ?? 0);
        $marked = $present + $absent + $outside;

        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'outside' => $outside,
            'unmarked' => max(0, $total - $marked),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recentFeed(int $seasonEventId, int $limit = 40): array
    {
        $rows = DB::table('SeasonEventBookingAttendance as a')
            ->join('SeasonEventParticipantFinance as b', 'b.SeasonEventParticipantFinanceID', '=', 'a.SeasonEventParticipantFinanceID')
            ->leftJoin('PersonInformation as p', 'b.PersonID', '=', 'p.PersonID')
            ->leftJoin('Guests as g', 'b.GuestID', '=', 'g.GuestID')
            ->leftJoin('FamilyMembers as f', 'b.FamilyID', '=', 'f.FamilyID')
            ->where('a.SeasonEventID', $seasonEventId)
            ->orderByDesc('a.UpdatedAt')
            ->limit($limit)
            ->select(
                'a.AttendanceStatus',
                'a.UpdatedAt',
                'b.SeasonEventParticipantFinanceID',
                'b.PersonID',
                'b.GuestID',
                'b.FamilyID',
                DB::raw("
                    TRIM(CONCAT(
                        COALESCE(p.FirstName, g.FirstName, f.FirstName, ''), ' ',
                        COALESCE(p.SecondName, g.SecondName, f.SecondName, ''), ' ',
                        COALESCE(p.ThirdName, g.ThirdName, f.ThirdName, ''), ' ',
                        COALESCE(p.FourthName, g.FourthName, f.FourthName, '')
                    )) as EntityName
                "),
                DB::raw("
                    CASE
                        WHEN b.FamilyID IS NOT NULL THEN 'FAMILY'
                        WHEN b.GuestID IS NOT NULL THEN 'GUEST'
                        ELSE 'PERSON'
                    END as EntityType
                ")
            )
            ->get();

        return $rows->map(function ($row) {
            $typeLabel = match ($row->EntityType) {
                'GUEST' => __('Guests'),
                'FAMILY' => __('Families'),
                default => __('Person'),
            };

            return [
                'booking_id' => (int) $row->SeasonEventParticipantFinanceID,
                'name' => trim((string) $row->EntityName),
                'entity_type' => $row->EntityType,
                'booking_type_label' => $typeLabel,
                'status' => $row->AttendanceStatus,
                'updated_at' => (string) $row->UpdatedAt,
            ];
        })->all();
    }
}
