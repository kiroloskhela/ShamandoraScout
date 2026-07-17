<?php

namespace App\Domain\PlaceBooking;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class PlaceBookingService
{
    public function create(
        int $personId,
        int $placeId,
        ?int $qetaaId,
        string $bookingDate,
        string $timeFrom,
        string $timeTo,
        ?string $userNote
    ): int {
        return (int) DB::transaction(function () use (
            $personId,
            $placeId,
            $qetaaId,
            $bookingDate,
            $timeFrom,
            $timeTo,
            $userNote
        ) {
            return DB::table('PlaceBookings')->insertGetId([
                'PersonID' => $personId,
                'PlaceID' => $placeId,
                'QetaaID' => $qetaaId,
                'BookingDate' => $bookingDate,
                'TimeFrom' => $timeFrom,
                'TimeTo' => $timeTo,
                'UserNote' => $userNote,
                'Status' => 'pending',
                'AdminNote' => null,
                'ReviewedBy' => null,
                'ReviewedAt' => null,
                'ApprovedPlaceID' => null,
                'ApprovedTimeFrom' => null,
                'ApprovedTimeTo' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function updatePending(
        int $bookingId,
        int $personId,
        int $placeId,
        ?int $qetaaId,
        string $bookingDate,
        string $timeFrom,
        string $timeTo,
        ?string $userNote
    ): void {
        $updated = DB::table('PlaceBookings')
            ->where('BookingID', $bookingId)
            ->where('PersonID', $personId)
            ->where('Status', 'pending')
            ->update([
                'PlaceID' => $placeId,
                'QetaaID' => $qetaaId,
                'BookingDate' => $bookingDate,
                'TimeFrom' => $timeFrom,
                'TimeTo' => $timeTo,
                'UserNote' => $userNote,
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            throw new RuntimeException('Place booking not pending or not owned');
        }
    }

    public function deletePending(int $bookingId, int $personId): void
    {
        $deleted = DB::table('PlaceBookings')
            ->where('BookingID', $bookingId)
            ->where('PersonID', $personId)
            ->where('Status', 'pending')
            ->delete();

        if ($deleted === 0) {
            throw new RuntimeException('Place booking not pending or not owned');
        }
    }
}
