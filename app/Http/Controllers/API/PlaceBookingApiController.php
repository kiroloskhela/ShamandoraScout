<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\NotificationController;

class PlaceBookingApiController extends Controller
{
    /**
     * @OA\Tag(
     *   name="PlaceBookings",
     *   description="Place booking endpoints"
     * )
     *
     * @OA\Get(
     *   path="/api/place_bookings/meta",
     *   operationId="placeBookingMeta",
     *   tags={"PlaceBookings"},
     *   summary="Get place booking metadata",
     *   description="Returns locations, qetaat, and optional places list for dropdowns.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success", @OA\JsonContent(type="object")),
     *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object"))
     * )
     *
     * @OA\Get(
     *   path="/api/place_bookings/places/{locationId}",
     *   operationId="placeBookingPlacesByLocation",
     *   tags={"PlaceBookings"},
     *   summary="Get places by location",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="locationId",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer", example=1)
     *   ),
     *   @OA\Response(response=200, description="Success", @OA\JsonContent(type="object")),
     *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object"))
     * )
     *
     * @OA\Post(
     *   path="/api/place_bookings",
     *   operationId="placeBookingCreate",
     *   tags={"PlaceBookings"},
     *   summary="Create place booking request",
     *   description="Creates a booking request (pending).",
     *   security={{"bearerAuth":{}}},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"place_id","booking_date","time_from","time_to"},
     *       @OA\Property(property="place_id", type="integer", example=10),
     *       @OA\Property(property="qetaa_id", type="integer", nullable=true, example=2),
     *       @OA\Property(property="booking_date", type="string", format="date", example="2026-01-28"),
     *       @OA\Property(property="time_from", type="string", example="08:00"),
     *       @OA\Property(property="time_to", type="string", example="09:00"),
     *       @OA\Property(property="user_note", type="string", nullable=true, example="Meeting with team")
     *     )
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="Created",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="ok", type="boolean", example=true),
     *       @OA\Property(property="message", type="string", example="Place booking created"),
     *       @OA\Property(property="BookingID", type="integer", example=123)
     *     )
     *   ),
     *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object")),
     *   @OA\Response(response=422, description="Validation error", @OA\JsonContent(type="object")),
     *   @OA\Response(response=500, description="Server error", @OA\JsonContent(type="object"))
     * )
     *
     * @OA\Get(
     *   path="/api/place_bookings",
     *   operationId="placeBookingListMine",
     *   tags={"PlaceBookings"},
     *   summary="List my place bookings",
     *   security={{"bearerAuth":{}}},
     *   @OA\Response(response=200, description="Success", @OA\JsonContent(type="object")),
     *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object"))
     * )
     *
     * @OA\Get(
     *   path="/api/place_bookings/{id}",
     *   operationId="placeBookingShowMine",
     *   tags={"PlaceBookings"},
     *   summary="Show one booking (must be mine)",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer", example=123)
     *   ),
     *   @OA\Response(response=200, description="Success", @OA\JsonContent(type="object")),
     *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object")),
     *   @OA\Response(response=404, description="Not found", @OA\JsonContent(type="object"))
     * )
     *
     * @OA\Put(
     *   path="/api/place_bookings/{id}",
     *   operationId="placeBookingUpdateMine",
     *   tags={"PlaceBookings"},
     *   summary="Update pending booking (must be mine)",
     *   description="Only allowed while status is pending.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer", example=123)
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"place_id","booking_date","time_from","time_to"},
     *       @OA\Property(property="place_id", type="integer", example=11),
     *       @OA\Property(property="qetaa_id", type="integer", nullable=true, example=2),
     *       @OA\Property(property="booking_date", type="string", format="date", example="2026-01-29"),
     *       @OA\Property(property="time_from", type="string", example="10:00"),
     *       @OA\Property(property="time_to", type="string", example="11:00"),
     *       @OA\Property(property="user_note", type="string", nullable=true, example="Updated note")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Updated", @OA\JsonContent(type="object")),
     *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object")),
     *   @OA\Response(response=403, description="Forbidden", @OA\JsonContent(type="object")),
     *   @OA\Response(response=404, description="Not found", @OA\JsonContent(type="object")),
     *   @OA\Response(response=422, description="Validation error", @OA\JsonContent(type="object")),
     *   @OA\Response(response=500, description="Server error", @OA\JsonContent(type="object"))
     * )
     *
     * @OA\Delete(
     *   path="/api/place_bookings/{id}",
     *   operationId="placeBookingDeleteMine",
     *   tags={"PlaceBookings"},
     *   summary="Delete pending booking (must be mine)",
     *   description="Only allowed while status is pending.",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer", example=123)
     *   ),
     *   @OA\Response(response=200, description="Deleted", @OA\JsonContent(type="object")),
     *   @OA\Response(response=401, description="Unauthorized", @OA\JsonContent(type="object")),
     *   @OA\Response(response=403, description="Forbidden", @OA\JsonContent(type="object")),
     *   @OA\Response(response=404, description="Not found", @OA\JsonContent(type="object")),
     *   @OA\Response(response=500, description="Server error", @OA\JsonContent(type="object"))
     * )
     */

    // ---------------- helpers ----------------

    private function currentPersonId(): ?int
    {
        return auth()->user()->PersonID ?? null;
    }

    private function jsonUnauthorized()
    {
        return response()->json(['ok' => false, 'message' => 'Unauthorized (missing PersonID)'], 401);
    }

    private function ensureOptionalFksExist(Request $request)
    {
        if ($request->filled('qetaa_id')) {
            $ok = DB::table('Qetaa')->where('QetaaID', (int)$request->qetaa_id)->exists();
            if (!$ok) return response()->json(['ok' => false, 'message' => 'Invalid QetaaID'], 422);
        }
        return null;
    }

    private function validateTimeOrder(string $from, string $to)
    {
        if ($from >= $to) {
            return response()->json(['ok' => false, 'message' => 'time_to must be after time_from'], 422);
        }
        return null;
    }

    // ---------------- endpoints ----------------

    /**
     * GET /api/place_bookings/meta
     */
    public function meta()
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $locations = DB::table('Locations')
            ->select('LocationID', 'LocationName')
            ->orderBy('LocationName')
            ->get();

        $qetaat = DB::table('Qetaa')
            ->select('QetaaID', 'QetaaName')
            ->orderBy('QetaaName')
            ->get();

        return response()->json([
            'ok'        => true,
            'locations' => $locations,
            'qetaat'    => $qetaat,
        ]);
    }

    /**
     * GET /api/place_bookings/places/{locationId}
     */
    public function placesByLocation(int $locationId)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        // If you have Locations table FK, you can optionally verify location exists
        $places = DB::table('Place as P')
            ->where('P.LocationID', $locationId)
            ->orderBy('P.PlaceName')
            ->select('P.PlaceID', 'P.PlaceName')
            ->get();

        return response()->json(['ok' => true, 'count' => $places->count(), 'places' => $places]);
    }

    /**
     * POST /api/place_bookings
     */
    public function store(Request $request)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $request->validate([
            'place_id'      => 'required|integer',
            'qetaa_id'      => 'nullable|integer',
            'booking_date'  => 'required|date',
            'time_from'     => 'required|date_format:H:i',
            'time_to'       => 'required|date_format:H:i',
            'user_note'     => 'nullable|string|max:500',
        ]);

        if ($err = $this->ensureOptionalFksExist($request)) return $err;
        if ($err = $this->validateTimeOrder($request->time_from, $request->time_to)) return $err;

        // Verify place exists
        $okPlace = DB::table('Place')->where('PlaceID', (int)$request->place_id)->exists();
        if (!$okPlace) {
            return response()->json(['ok' => false, 'message' => 'Invalid PlaceID'], 422);
        }

        DB::beginTransaction();
        try {
            $bookingId = DB::table('PlaceBookings')->insertGetId([
                'PersonID'    => $personId,
                'PlaceID'     => (int)$request->place_id,
                'QetaaID'     => $request->qetaa_id ?: null,

                'BookingDate' => $request->booking_date,
                'TimeFrom'    => $request->time_from,
                'TimeTo'      => $request->time_to,

                'UserNote'    => $request->user_note,

                'Status'      => 'pending',

                'AdminNote'   => null,
                'ReviewedBy'  => null,
                'ReviewedAt'  => null,

                // approved fields are null until admin reviews
                'ApprovedPlaceID'  => null,
                'ApprovedTimeFrom' => null,
                'ApprovedTimeTo'   => null,

                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            NotificationController::sendToRoles(
                ['SuperAdmin'],
                'Room Booking',
                $request->user()->FirstName . ' ' . $request->user()->SecondName . ' has requested a room booking on ' . $request->booking_date . ' from ' . $request->time_from . ' to ' . $request->time_to . '. Please review the request.'
            );
            DB::commit();

            return response()->json([
                'ok'        => true,
                'message'   => 'Place booking created',
                'BookingID' => (int)$bookingId,
            ], 201);
            

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PlaceBooking API: store failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to create booking'], 500);
        }
    }

    /**
     * GET /api/place_bookings
     */
    public function index()
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $bookings = DB::table('PlaceBookings as B')
            ->join('Place as P', 'B.PlaceID', '=', 'P.PlaceID')
            ->join('Locations as L', 'P.LocationID', '=', 'L.LocationID')
            ->leftJoin('Qetaa as Q', 'B.QetaaID', '=', 'Q.QetaaID')
            ->leftJoin('PersonInformation as A', 'B.ReviewedBy', '=', 'A.PersonID')
            ->where('B.PersonID', $personId)
            ->orderByDesc('B.created_at')
            ->select([
                'B.BookingID',
                'B.PersonID',
                'B.PlaceID',
                'B.QetaaID',
                'B.BookingDate',
                'B.TimeFrom',
                'B.TimeTo',
                'B.Status',
                'B.UserNote',
                'B.AdminNote',
                'B.ReviewedBy',
                'B.ReviewedAt',
                'B.ApprovedPlaceID',
                'B.ApprovedTimeFrom',
                'B.ApprovedTimeTo',
                'B.created_at',
                'B.updated_at',

                'P.PlaceName',
                'L.LocationID',
                'L.LocationName',
                'Q.QetaaName',
                DB::raw("CONCAT(A.FirstName, ' ', A.SecondName) as ReviewerName"),
            ])
            ->get();

        return response()->json(['ok' => true, 'count' => $bookings->count(), 'bookings' => $bookings]);
    }

    /**
     * GET /api/place_bookings/{id}
     */
    public function show(int $id)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $booking = DB::table('PlaceBookings as B')
            ->join('Place as P', 'B.PlaceID', '=', 'P.PlaceID')
            ->join('Locations as L', 'P.LocationID', '=', 'L.LocationID')
            ->leftJoin('Qetaa as Q', 'B.QetaaID', '=', 'Q.QetaaID')
            ->leftJoin('PersonInformation as A', 'B.ReviewedBy', '=', 'A.PersonID')
            ->where('B.BookingID', $id)
            ->where('B.PersonID', $personId)
            ->select([
                'B.*',
                'P.PlaceName',
                'L.LocationID',
                'L.LocationName',
                'Q.QetaaName',
                DB::raw("CONCAT(A.FirstName, ' ', A.SecondName) as ReviewerName"),
            ])
            ->first();

        if (!$booking) {
            return response()->json(['ok' => false, 'message' => 'Booking not found'], 404);
        }

        // Optional: also return approved place info if ApprovedPlaceID exists
        $approved = null;
        if (!empty($booking->ApprovedPlaceID)) {
            $approved = DB::table('Place as P')
                ->join('Locations as L', 'P.LocationID', '=', 'L.LocationID')
                ->where('P.PlaceID', (int)$booking->ApprovedPlaceID)
                ->select([
                    'P.PlaceID',
                    'P.PlaceName',
                    'L.LocationID',
                    'L.LocationName',
                ])
                ->first();
        }

        return response()->json([
            'ok'       => true,
            'booking'  => $booking,
            'approved' => $approved,
        ]);
    }

    /**
     * PUT /api/place_bookings/{id}
     * Update pending-only (must be mine).
     */
    public function update(Request $request, int $id)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $row = DB::table('PlaceBookings')
            ->where('BookingID', $id)
            ->where('PersonID', $personId)
            ->first();

        if (!$row) return response()->json(['ok' => false, 'message' => 'Booking not found'], 404);
        if ($row->Status !== 'pending') {
            return response()->json(['ok' => false, 'message' => 'Cannot update after review'], 403);
        }

        $request->validate([
            'place_id'      => 'required|integer',
            'qetaa_id'      => 'nullable|integer',
            'booking_date'  => 'required|date',
            'time_from'     => 'required|date_format:H:i',
            'time_to'       => 'required|date_format:H:i',
            'user_note'     => 'nullable|string|max:500',
        ]);

        if ($err = $this->ensureOptionalFksExist($request)) return $err;
        if ($err = $this->validateTimeOrder($request->time_from, $request->time_to)) return $err;

        $okPlace = DB::table('Place')->where('PlaceID', (int)$request->place_id)->exists();
        if (!$okPlace) {
            return response()->json(['ok' => false, 'message' => 'Invalid PlaceID'], 422);
        }

        DB::beginTransaction();
        try {
            DB::table('PlaceBookings')
                ->where('BookingID', $id)
                ->where('Status', 'pending') // atomic guard
                ->update([
                    'PlaceID'     => (int)$request->place_id,
                    'QetaaID'     => $request->qetaa_id ?: null,
                    'BookingDate' => $request->booking_date,
                    'TimeFrom'    => $request->time_from,
                    'TimeTo'      => $request->time_to,
                    'UserNote'    => $request->user_note,
                    'updated_at'  => now(),
                ]);

            DB::commit();
            return response()->json(['ok' => true, 'message' => 'Booking updated']);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('PlaceBooking API: update failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to update booking'], 500);
        }
    }

    /**
     * DELETE /api/place_bookings/{id}
     * Delete pending-only (must be mine).
     */
    public function destroy(int $id)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return $this->jsonUnauthorized();

        $row = DB::table('PlaceBookings')
            ->where('BookingID', $id)
            ->where('PersonID', $personId)
            ->first();

        if (!$row) return response()->json(['ok' => false, 'message' => 'Booking not found'], 404);
        if ($row->Status !== 'pending') {
            return response()->json(['ok' => false, 'message' => 'Cannot delete after review'], 403);
        }

        try {
            DB::table('PlaceBookings')->where('BookingID', $id)->delete();
            return response()->json(['ok' => true, 'message' => 'Booking deleted']);
        } catch (\Throwable $e) {
            Log::error('PlaceBooking API: delete failed', ['error' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Failed to delete booking'], 500);
        }
    }
}