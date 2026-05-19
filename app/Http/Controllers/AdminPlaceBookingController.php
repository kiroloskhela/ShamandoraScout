<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\FcmService;


class AdminPlaceBookingController extends Controller
{
  
    
    private function currentAdminPersonId()
    {
        $user = auth()->user();
        if (!$user) return null;

        // Similar check style as your custody admin controller
        $roles = $user->role ?? null;
        if (
            !$roles ||
            (!collect($roles)->contains('RoleName', 'Admin') && !collect($roles)->contains('RoleName', 'SuperAdmin'))
        ) {
            return null;
        }

        return $user->PersonID ?? null;
    }

    public function index()
    {
        $bookings = DB::table('PlaceBookings as B')
            ->join('Place as P', 'B.PlaceID', '=', 'P.PlaceID')
            ->join('Locations as L', 'P.LocationID', '=', 'L.LocationID')
            ->leftJoin('Qetaa as Q', 'B.QetaaID', '=', 'Q.QetaaID')

            // user who created booking
            ->join('PersonInformation as U', 'B.PersonID', '=', 'U.PersonID')

            // admin reviewer
            ->leftJoin('PersonInformation as A', 'B.ReviewedBy', '=', 'A.PersonID')

            ->select([
                'B.*',
                'P.PlaceName',
                'L.LocationName as LocationName',
                'Q.QetaaName',

                DB::raw("CONCAT(U.FirstName, ' ', U.SecondName, ' ', COALESCE(U.ThirdName,'')) as UserName"),
                DB::raw("CONCAT(A.FirstName, ' ', A.SecondName, ' ', COALESCE(A.ThirdName,'')) as ReviewerName"),
            ])
            ->orderByRaw("FIELD(B.Status,'pending','approved','rejected')")
            ->orderByDesc('B.created_at')
            ->get();

        return view('admin.place_bookings.index', compact('bookings'));
    }

    public function show($id)
    {
        $booking = DB::table('PlaceBookings as B')
            ->join('Place as P', 'B.PlaceID', '=', 'P.PlaceID')
            ->join('Locations as L', 'P.LocationID', '=', 'L.LocationID')
            ->leftJoin('Qetaa as Q', 'B.QetaaID', '=', 'Q.QetaaID')

            ->join('PersonInformation as U', 'B.PersonID', '=', 'U.PersonID')
            ->leftJoin('PersonInformation as A', 'B.ReviewedBy', '=', 'A.PersonID')

            ->where('B.BookingID', $id)
            ->select([
                'B.*',
                'P.PlaceName',
                'L.LocationName as LocationName',
                'Q.QetaaName',

                DB::raw("CONCAT(U.FirstName, ' ', U.SecondName, ' ', COALESCE(U.ThirdName,'')) as UserName"),
                DB::raw("CONCAT(A.FirstName, ' ', A.SecondName, ' ', COALESCE(A.ThirdName,'')) as ReviewerName"),
            ])
            ->first();

        if (!$booking) {
            return redirect()->route('admin.place_bookings.index')->with('error', '❌ الطلب غير موجود.');
        }

        // For approve-with-edit form
        $locations = DB::table('Locations')->orderBy('LocationName')->get();
        $places = DB::table('Place as P')
                    ->join('Locations as L', 'P.LocationID', '=', 'L.LocationID')
                    ->select([
                        'P.PlaceID',
                        'P.PlaceName',
                        'P.LocationID',
                        'L.LocationName as LocationName',
                    ])
                    ->orderBy('L.LocationName')
                    ->orderBy('P.PlaceName')
                    ->get();




        $qetaat    = DB::table('Qetaa')->orderBy('QetaaName')->get();

        return view('admin.place_bookings.show', compact('booking', 'locations', 'places', 'qetaat'));
    }


    public function approve(Request $request, $id)
    {
        $adminPersonId = $this->currentAdminPersonId();
        if (!$adminPersonId) {
            return back()->with('error', '❌ لا يمكن تحديد الأدمن الحالي (PersonID).');
        }

        // Load booking (current values)
        $booking = DB::table('PlaceBookings as B')
            ->leftJoin('Place as P', 'B.PlaceID', '=', 'P.PlaceID')
            ->leftJoin('Locations as L', 'P.LocationID', '=', 'L.LocationID')
            ->where('B.BookingID', $id)
            ->select([
                'B.*',
                'P.PlaceName as CurrentPlaceName',
                'L.LocationName as CurrentLocationName',
            ])
            ->first();

        if (!$booking) {
            return redirect()->route('admin.place_bookings.index')->with('error', '❌ الطلب غير موجود.');
        }

        if ($booking->Status !== 'pending') {
            return back()->with('error', '❌ لا يمكن اعتماد طلب تم مراجعته بالفعل.');
        }

        $validated = $request->validate([
            'approved_place_id'      => 'required|integer',
            'approved_booking_date'  => 'required|date',
            'approved_time_from'     => 'required',
            'approved_time_to'       => 'required',
            'admin_note'             => 'nullable|string|max:2000',
        ]);

        // Validate time order
        if ($validated['approved_time_from'] >= $validated['approved_time_to']) {
            return back()->with('error', '❌ وقت (إلى) يجب أن يكون بعد وقت (من).')->withInput();
        }

        // Load approved place name (for change log)
        $approvedPlace = DB::table('Place as P')
            ->leftJoin('Locations as L', 'P.LocationID', '=', 'L.LocationID')
            ->where('P.PlaceID', $validated['approved_place_id'])
            ->select([
                'P.PlaceID',
                'P.PlaceName',
                'L.LocationName',
            ])
            ->first();

        if (!$approvedPlace) {
            return back()->with('error', '❌ المكان المختار غير صحيح.')->withInput();
        }

        // ===== Build automatic change log =====
        $changes = [];

        // Place change
        if ((string)$booking->PlaceID !== (string)$approvedPlace->PlaceID) {
            $from = trim(($booking->CurrentPlaceName ?? '—') . ' (' . ($booking->CurrentLocationName ?? '—') . ')');
            $to   = trim(($approvedPlace->PlaceName ?? '—') . ' (' . ($approvedPlace->LocationName ?? '—') . ')');
            $changes[] = "تم تغيير المكان: {$from} → {$to}";
        }

        // Date change
        if ((string)$booking->BookingDate !== (string)$validated['approved_booking_date']) {
            $changes[] = "تم تغيير التاريخ: {$booking->BookingDate} → {$validated['approved_booking_date']}";
        }

        // Time change
        if ((string)$booking->TimeFrom !== (string)$validated['approved_time_from'] ||
            (string)$booking->TimeTo !== (string)$validated['approved_time_to']) {
            $changes[] = "تم تغيير الوقت: {$booking->TimeFrom}-{$booking->TimeTo} → {$validated['approved_time_from']}-{$validated['approved_time_to']}";
        }

        // If nothing changed, still add a friendly line (optional)
        if (empty($changes)) {
            $changes[] = "تم اعتماد الطلب كما هو بدون تعديل.";
        }

        $autoNote = "تعديلات الإدارة:\n- " . implode("\n- ", $changes);

        // Merge with admin note
        $adminNote = trim((string)($validated['admin_note'] ?? ''));
        if ($adminNote !== '') {
            $finalAdminNote = $adminNote . "\n\n" . $autoNote;
        } else {
            $finalAdminNote = $autoNote;
        }

        // ===== Save =====
        DB::beginTransaction();
        try {
            $affected = DB::table('PlaceBookings')
                ->where('BookingID', $id)
                ->where('Status', 'pending') // atomic guard
                ->update([
                    'Status'        => 'approved',

                    // approved values (keep your column names!)
                    'ApprovedPlaceID'   => $approvedPlace->PlaceID,
                    'ApprovedBookingDate' => $validated['approved_booking_date'],
                    'ApprovedTimeFrom'  => $validated['approved_time_from'],
                    'ApprovedTimeTo'    => $validated['approved_time_to'],

                    'AdminNote'     => $finalAdminNote,
                    'ReviewedBy'    => $adminPersonId,
                    'ReviewedAt'    => now(),
                    'updated_at'    => now(),
                ]);

            if ($affected === 0) {
                DB::rollBack();
                return back()->with('error', '❌ لا يمكن اعتماد طلب تم مراجعته بالفعل.')->withInput();
            }

            DB::commit();
            // 🔔 Send Notification
       
            NotificationController::sendToUserId(
                $booking->PersonID,
                '✅ تم قبول طلب الحجز الغرفه',
                "تم قبول طلبك بتاريخ {$validated['approved_booking_date']} من {$validated['approved_time_from']} إلى {$validated['approved_time_to']}"
            );

    return redirect()->route('admin.place_bookings.show', $id)
        ->with('success', '✅ تم اعتماد الطلب بنجاح.');
        
        } catch (\Throwable $e) {
        DB::rollBack();

        Log::error('Approve place booking failed', [
            'bookingId' => $id,
            'error' => $e->getMessage(),
        ]);

        return back()->with('error', '❌ ' . $e->getMessage())->withInput();
    }

    }

    public function reject(Request $request, $id)
    {
        $adminPersonId = $this->currentAdminPersonId();
        if (!$adminPersonId) {
            return back()->with('error', '❌ لا يمكن تحديد الأدمن الحالي (PersonID).');
        }

        $booking = DB::table('PlaceBookings')->where('BookingID', $id)->first();
        if (!$booking) {
            return redirect()->route('admin.place_bookings.index')->with('error', '❌ الطلب غير موجود.');
        }

        if ($booking->Status !== 'pending') {
            return back()->with('error', '❌ لا يمكن رفض طلب تم مراجعته بالفعل.');
        }

        $validated = $request->validate([
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $adminNote = trim((string)$validated['admin_note']);
        if (!$adminNote) $adminNote = 'تم رفض الحجز.';

        try {
            $affected = DB::table('PlaceBookings')
                ->where('BookingID', $id)
                ->where('Status', 'pending')
                ->update([
                    'Status'     => 'rejected',
                    'AdminNote'  => $adminNote,
                    'ReviewedBy' => $adminPersonId,
                    'ReviewedAt' => now(),
                    'updated_at' => now(),
                ]);

            if ($affected === 0) {
                return back()->with('error', '❌ لا يمكن رفض طلب تم مراجعته بالفعل.');
            }

            // 🔔 Send Notification
          NotificationController::sendToUserId(
                $booking->PersonID,
                '❌ تم رفض الحجز',
                "تم رفض طلبك بتاريخ {$booking->BookingDate} من {$booking->TimeFrom} إلى {$booking->TimeTo}. السبب: {$adminNote}"
            );

            return redirect()->route('admin.place_bookings.show', $id)->with('success', '✅ تم رفض الحجز.');
        } catch (\Throwable $e) {
            Log::error('Error rejecting place booking', ['exception' => $e, 'bookingId' => $id]);
            return back()->with('error', '❌ حدث خطأ أثناء رفض الحجز.')->withInput();
        }
    }

    public function approveWithEdit(Request $request, $id)
    {
        $adminPersonId = $this->currentAdminPersonId();
        if (!$adminPersonId) {
            return back()->with('error', '❌ لا يمكن تحديد الأدمن الحالي (PersonID).');
        }

        $booking = DB::table('PlaceBookings')->where('BookingID', $id)->first();
        if (!$booking) {
            return redirect()->route('admin.place_bookings.index')->with('error', '❌ الطلب غير موجود.');
        }

        if ($booking->Status !== 'pending') {
            return back()->with('error', '❌ لا يمكن اعتماد طلب تم مراجعته بالفعل.');
        }

        $validated = $request->validate([
            'approved_place_id'  => 'required|integer',
            'approved_time_from' => 'required|date_format:H:i',
            'approved_time_to'   => 'required|date_format:H:i|after:approved_time_from',
            'admin_note'         => 'nullable|string|max:2000',
        ]);

        // Ensure approved place exists
        $okPlace = DB::table('Place')->where('PlaceID', $validated['approved_place_id'])->exists();
        if (!$okPlace) {
            return back()->with('error', '❌ المكان المُعتمد غير صحيح.')->withInput();
        }

        $adminNote = trim((string)$validated['admin_note']);
        if (!$adminNote) $adminNote = 'تمت الموافقة على الحجز مع تعديل.';

        try {
            $affected = DB::table('PlaceBookings')
                ->where('BookingID', $id)
                ->where('Status', 'pending')
                ->update([
                    'Status'     => 'approved',
                    'AdminNote'  => $adminNote,
                    'ReviewedBy' => $adminPersonId,
                    'ReviewedAt' => now(),
                    'updated_at' => now(),

                    'ApprovedPlaceID'  => $validated['approved_place_id'],
                    'ApprovedTimeFrom' => $validated['approved_time_from'],
                    'ApprovedTimeTo'   => $validated['approved_time_to'],
                ]);

            if ($affected === 0) {
                return back()->with('error', '❌ لا يمكن اعتماد طلب تم مراجعته بالفعل.');
            }
            NotificationController::sendToUserId(
                $booking->PersonID,
                '✅ تم قبول الحجز مع تعديل',
                "تم تعديل الحجز الخاص بك من {$validated['approved_time_from']} إلى {$validated['approved_time_to']}"
            );
            return redirect()->route('admin.place_bookings.show', $id)->with('success', '✅ تم اعتماد الحجز مع تعديل.');
        } catch (\Throwable $e) {
            Log::error('Error approving place booking with edit', ['exception' => $e, 'bookingId' => $id]);
            return back()->with('error', '❌ حدث خطأ أثناء اعتماد الحجز.')->withInput();
        }
    }
}