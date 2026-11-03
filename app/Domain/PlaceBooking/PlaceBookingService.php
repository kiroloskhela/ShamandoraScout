<?php

namespace App\Domain\PlaceBooking;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
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
            if ($this->hasOverlappingApprovedBooking($placeId, $bookingDate, $timeFrom, $timeTo)) {
                throw ValidationException::withMessages([
                    'time_from' => 'This place already has an overlapping approved booking.',
                ]);
            }

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
        $pending = DB::table('PlaceBookings')
            ->where('BookingID', $bookingId)
            ->where('PersonID', $personId)
            ->where('Status', 'pending')
            ->first();

        if (! $pending) {
            throw new RuntimeException('Place booking not pending or not owned');
        }

        DB::table('PlaceBookings')
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
    }

    public function hasOverlappingApprovedBooking(
        int $placeId,
        string $bookingDate,
        string $timeFrom,
        string $timeTo,
        ?int $exceptBookingId = null
    ): bool {
        $date = Carbon::parse($bookingDate)->toDateString();
        [$start, $end] = $this->momentsOnDate($date, $timeFrom, $timeTo);
        $hasApprovedDate = Schema::hasColumn('PlaceBookings', 'ApprovedBookingDate');

        $query = DB::table('PlaceBookings')
            ->where('Status', 'approved')
            ->where(function ($query) use ($placeId) {
                $query->where('ApprovedPlaceID', $placeId)
                    ->orWhere(function ($query) use ($placeId) {
                        $query->whereNull('ApprovedPlaceID')
                            ->where('PlaceID', $placeId);
                    });
            });

        if ($exceptBookingId) {
            $query->where('BookingID', '!=', $exceptBookingId);
        }

        if ($hasApprovedDate) {
            $query->where(function ($query) use ($date) {
                $query->whereDate('ApprovedBookingDate', $date)
                    ->orWhere(function ($query) use ($date) {
                        $query->whereNull('ApprovedBookingDate')
                            ->whereDate('BookingDate', $date);
                    });
            });
        } else {
            $query->whereDate('BookingDate', $date);
        }

        $rows = $query->lockForUpdate()->get();

        foreach ($rows as $row) {
            $rowDate = $date;
            if ($hasApprovedDate && ! empty($row->ApprovedBookingDate)) {
                $rowDate = Carbon::parse($row->ApprovedBookingDate)->toDateString();
            } elseif (! empty($row->BookingDate)) {
                $rowDate = Carbon::parse($row->BookingDate)->toDateString();
            }

            $rowFrom = $row->ApprovedTimeFrom ?: $row->TimeFrom;
            $rowTo = $row->ApprovedTimeTo ?: $row->TimeTo;
            if (! $rowFrom || ! $rowTo) {
                continue;
            }

            [$rowStart, $rowEnd] = $this->momentsOnDate($rowDate, (string) $rowFrom, (string) $rowTo);

            if ($start->lt($rowEnd) && $rowStart->lt($end)) {
                return true;
            }
        }

        return false;
    }

    public function endsAfterStart(string $date, string $timeFrom, string $timeTo): bool
    {
        [$start, $end] = $this->momentsOnDate($date, $timeFrom, $timeTo);

        return $start->lt($end);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function momentsOnDate(string $date, string $timeFrom, string $timeTo): array
    {
        return [
            $this->parseBookingMoment($date, $timeFrom),
            $this->parseBookingMoment($date, $timeTo),
        ];
    }

    private function parseBookingMoment(string $date, string $time): Carbon
    {
        $time = trim($time);
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $time)) {
            return Carbon::parse($time);
        }

        return Carbon::parse(Carbon::parse($date)->toDateString().' '.$time);
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
