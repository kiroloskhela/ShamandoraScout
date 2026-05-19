<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlaceBookingController extends Controller
{
    private function currentPersonId()
    {
        return auth()->user()->PersonID ?? null;
    }

    public function create()
    {
        $locations = DB::table('Locations')->orderBy('LocationName')->get();
        $qetaat    = DB::table('Qetaa')->orderBy('QetaaName')->get();

        return view('place_bookings.create', compact('locations', 'qetaat'));
    }

    // AJAX: Places by location
    public function placesByLocation($locationId)
    {
        $places = DB::table('Place')
            ->where('LocationID', $locationId)
            ->orderBy('PlaceName')
            ->get(['PlaceID', 'PlaceName']);

        return response()->json($places);
    }

    public function store(Request $request)
    {
        $personId = $this->currentPersonId();
        if (!$personId) {
            return back()->with('error', '❌ لا يمكن تحديد المستخدم الحالي (PersonID).')->withInput();
        }

        $request->validate([
            'location_id'  => 'required|integer',
            'place_id'     => 'required|integer',
            'qetaa_id'     => 'nullable|integer',

            'booking_date' => 'required|date',
            'time_from'    => 'required|date_format:H:i',
            'time_to'      => 'required|date_format:H:i|after:time_from',

            'user_note'    => 'nullable|string|max:500',
        ]);

        // Place exists + belongs to location
        $place = DB::table('Place')->where('PlaceID', $request->place_id)->first();
        if (!$place) return back()->with('error', '❌ المكان غير موجود.')->withInput();
        if ((int)$place->LocationID !== (int)$request->location_id) {
            return back()->with('error', '❌ المكان لا يتبع هذا الموقع.')->withInput();
        }

        // optional qetaa exists
        if ($request->filled('qetaa_id') && !DB::table('Qetaa')->where('QetaaID', $request->qetaa_id)->exists()) {
            return back()->with('error', '❌ القطاع غير صحيح.')->withInput();
        }

        // NOTE: As requested, we allow multiple pending even if same time/place.
        // So we do NOT block here. Admin will decide (approve with edit).

        DB::table('PlaceBookings')->insert([
            'PersonID'    => $personId,
            'PlaceID'     => $request->place_id,
            'QetaaID'     => $request->qetaa_id ?: null,

            'BookingDate' => $request->booking_date,
            'TimeFrom'    => $request->time_from,
            'TimeTo'      => $request->time_to,

            'UserNote'    => $request->user_note,
            'Status'      => 'pending',

            'AdminNote'   => null,
            'ReviewedBy'  => null,
            'ReviewedAt'  => null,

            'ApprovedPlaceID'  => null,
            'ApprovedTimeFrom' => null,
            'ApprovedTimeTo'   => null,

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        NotificationController::sendToRoles(
                 ['SuperAdmin', 'Secretary', 'AdminSecretary'],
                'Room Booking',
                $request->user()->FirstName . ' ' . $request->user()->SecondName . ' has requested a room booking on ' . $request->booking_date . ' from ' . $request->time_from . ' to ' . $request->time_to . '. Please review the request.'
            );
        
        return redirect()->route('place_bookings.my')
            ->with('success', '✅ تم إرسال طلب الحجز وهو الآن قيد المراجعة.');
    }

    public function my()
    {
        $personId = $this->currentPersonId();
        if (!$personId) return back()->with('error', '❌ لا يمكن تحديد المستخدم الحالي.');

        $rows = DB::table('PlaceBookings as B')
            ->join('Place as P', 'B.PlaceID', '=', 'P.PlaceID')
            ->join('Locations as L', 'P.LocationID', '=', 'L.LocationID')
            ->leftJoin('Qetaa as Q', 'B.QetaaID', '=', 'Q.QetaaID')
            ->leftJoin('PersonInformation as A', 'B.ReviewedBy', '=', 'A.PersonID')
            ->where('B.PersonID', $personId)
            ->orderByDesc('B.created_at')
            ->select([
                'B.*',
                'P.PlaceName',
                'L.LocationName as LocationName',
                'Q.QetaaName',
                DB::raw("CONCAT(A.FirstName, ' ', A.SecondName, ' ', COALESCE(A.ThirdName,'')) as ReviewerName"),
            ])
            ->get();

        return view('place_bookings.my', compact('rows'));
    }

    public function show($id)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return back()->with('error', '❌ لا يمكن تحديد المستخدم الحالي.');

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
                'L.LocationName as LocationName',
                'Q.QetaaName',
                DB::raw("CONCAT(A.FirstName, ' ', A.SecondName, ' ', COALESCE(A.ThirdName,'')) as ReviewerName"),
            ])
            ->first();

        if (!$booking) return redirect()->route('place_bookings.my')->with('error', '❌ الطلب غير موجود.');
    

        return view('place_bookings.show', compact('booking'));
    }

    public function edit($id)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return redirect()->route('place_bookings.my')->with('error', '❌ لا يمكن تحديد المستخدم الحالي.');

        $booking = DB::table('PlaceBookings as B')
            ->join('Place as P', 'B.PlaceID', '=', 'P.PlaceID')
            ->join('Locations as L', 'P.LocationID', '=', 'L.LocationID')
            ->leftJoin('Qetaa as Q', 'B.QetaaID', '=', 'Q.QetaaID')
            ->where('B.BookingID', $id)
            ->where('B.PersonID', $personId) // مهم: ملك المستخدم
            ->select([
                'B.*',
                'P.LocationID as LocationID',
                'P.PlaceName',
                'L.LocationName',
                'Q.QetaaName',
            ])
            ->first();


        if (!$booking) return redirect()->route('place_bookings.my')->with('error', '❌ الطلب غير موجود.');
        if ($booking->Status !== 'pending') return redirect()->route('place_bookings.show', $id)->with('error', '❌ لا يمكن تعديل الطلب بعد المراجعة.');

        $locations = DB::table('Locations')->orderBy('LocationName')->get();
        $qetaat    = DB::table('Qetaa')->orderBy('QetaaName')->get();

        // to pre-load places for selected location:
        $selectedPlace = DB::table('Place')->where('PlaceID', $booking->PlaceID)->first();
        $places = $selectedPlace
            ? DB::table('Place')->where('LocationID', $selectedPlace->LocationID)->orderBy('PlaceName')->get()
            : collect([]);

        return view('place_bookings.edit', compact('booking','locations','qetaat','places','selectedPlace'));
    }

    public function update(Request $request, $id)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return redirect()->route('place_bookings.my')->with('error', '❌ لا يمكن تحديد المستخدم الحالي.');

        $booking = DB::table('PlaceBookings')
            ->where('BookingID', $id)
            ->where('PersonID', $personId)
            ->first();

        if (!$booking) return redirect()->route('place_bookings.my')->with('error', '❌ الطلب غير موجود.');
        if ($booking->Status !== 'pending') return redirect()->route('place_bookings.show', $id)->with('error', '❌ لا يمكن تعديل الطلب بعد المراجعة.');

        $request->validate([
            'location_id'  => 'required|integer',
            'place_id'     => 'required|integer',
            'qetaa_id'     => 'nullable|integer',

            'booking_date' => 'required|date',
            'time_from'    => 'required|date_format:H:i',
            'time_to'      => 'required|date_format:H:i|after:time_from',

            'user_note'    => 'nullable|string|max:500',
        ]);

        // Place exists + belongs to location
        $place = DB::table('Place')->where('PlaceID', $request->place_id)->first();
        if (!$place) return back()->with('error', '❌ المكان غير موجود.')->withInput();
        if ((int)$place->LocationID !== (int)$request->location_id) {
            return back()->with('error', '❌ المكان لا يتبع هذا الموقع.')->withInput();
        }

        if ($request->filled('qetaa_id') && !DB::table('Qetaa')->where('QetaaID', $request->qetaa_id)->exists()) {
            return back()->with('error', '❌ القطاع غير صحيح.')->withInput();
        }

        // Still allow overlap with pending (as requested). We do not block.

        DB::table('PlaceBookings')
            ->where('BookingID', $id)
            ->update([
                'PlaceID'     => $request->place_id,
                'QetaaID'     => $request->qetaa_id ?: null,
                'BookingDate' => $request->booking_date,
                'TimeFrom'    => $request->time_from,
                'TimeTo'      => $request->time_to,
                'UserNote'    => $request->user_note,
                'updated_at'  => now(),
            ]);

        return redirect()->route('place_bookings.show', $id)->with('success', '✅ تم تحديث الطلب بنجاح.');
    }

    public function destroy($id)
    {
        $personId = $this->currentPersonId();
        if (!$personId) return back()->with('error', '❌ لا يمكن تحديد المستخدم الحالي.');

        $booking = DB::table('PlaceBookings')
            ->where('BookingID', $id)
            ->where('PersonID', $personId)
            ->first();

        if (!$booking) return redirect()->route('place_bookings.my')->with('error', '❌ الطلب غير موجود.');
        if ($booking->Status !== 'pending') return redirect()->route('place_bookings.show', $id)->with('error', '❌ لا يمكن حذف الطلب بعد المراجعة.');

        DB::table('PlaceBookings')->where('BookingID', $id)->delete();
        return redirect()->route('place_bookings.my')->with('success', '🗑️ تم حذف الطلب بنجاح.');
    }
}