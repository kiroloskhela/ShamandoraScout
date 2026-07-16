<?php

namespace App\Domain\PlaceBooking;

use Illuminate\Support\Facades\DB;

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
}
