<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SeasonEventBookingFinanceController extends Controller
{
    public function selector()
    {
        $seasons = DB::table('Season')->orderBy('SeasonYear', 'desc')->get();
        return view('event_booking_finance.selector', compact('seasons'));
    }
    
private function shouldBypassLastInstallmentCompletion($personID = null, $specialCaseType = null): bool
{
    return ($personID ? $this->isSpecialCase($personID) : false)
        || $specialCaseType === 'AKHOH_RAB';
}

    public function getEventsWithPlan(Request $request)
    {
        $seasonID = $request->query('seasonID');

        if (!$seasonID) {
            return response()->json([]);
        }

        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->join('SeasonEventFinance as sef', 'se.SeasonEventID', '=', 'sef.SeasonEventID')
            ->where('se.SeasonID', $seasonID)
            ->select(
                'se.SeasonEventID',
                'e.EventName',
                'et.EventTypeName',
                'e.EventStartDate',
                'e.EventEndDate'
            )
            ->orderBy('e.EventStartDate')
            ->get();

        return response()->json($events);
    }

public function index(Request $request, $seasonEventID)
{
    $event = $this->getSeasonEventFullInfo($seasonEventID);
    if (!$event) {
        abort(404);
    }

    $paymentDays = DB::table('SeasonEventParticipantFinancePayment as p')
        ->join('SeasonEventParticipantFinance as b', 'p.SeasonEventParticipantFinanceID', '=', 'b.SeasonEventParticipantFinanceID')
        ->where('b.SeasonEventID', $seasonEventID)
        ->selectRaw('DATE(p.PaymentDate) as payment_day')
        ->distinct()
        ->orderByDesc('payment_day')
        ->pluck('payment_day');

    $selectedSummaryDate = $request->summary_date;
    if (!$selectedSummaryDate || !$paymentDays->contains($selectedSummaryDate)) {
        $selectedSummaryDate = $paymentDays->first() ?: Carbon::today()->toDateString();
    }

    $selectedDaySummary = [
        'people_count' => DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventID', $seasonEventID)
            ->whereDate('FirstPaymentDate', $selectedSummaryDate)
            ->count(),

        'payments_amount' => (float) DB::table('SeasonEventParticipantFinancePayment as p')
            ->join('SeasonEventParticipantFinance as b', 'p.SeasonEventParticipantFinanceID', '=', 'b.SeasonEventParticipantFinanceID')
            ->where('b.SeasonEventID', $seasonEventID)
            ->where('p.PaymentType', 'PAYMENT')
            ->whereDate('p.PaymentDate', $selectedSummaryDate)
            ->sum('p.Amount'),

        'refund_amount' => (float) DB::table('SeasonEventParticipantFinancePayment as p')
            ->join('SeasonEventParticipantFinance as b', 'p.SeasonEventParticipantFinanceID', '=', 'b.SeasonEventParticipantFinanceID')
            ->where('b.SeasonEventID', $seasonEventID)
            ->where('p.PaymentType', 'REFUND')
            ->whereDate('p.PaymentDate', $selectedSummaryDate)
            ->sum('p.Amount'),
    ];

    $totalSummary = [
        'people_count' => DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventID', $seasonEventID)
            ->count(),

        'payments_amount' => (float) DB::table('SeasonEventParticipantFinancePayment as p')
            ->join('SeasonEventParticipantFinance as b', 'p.SeasonEventParticipantFinanceID', '=', 'b.SeasonEventParticipantFinanceID')
            ->where('b.SeasonEventID', $seasonEventID)
            ->where('p.PaymentType', 'PAYMENT')
            ->sum('p.Amount'),

        'refund_amount' => (float) DB::table('SeasonEventParticipantFinancePayment as p')
            ->join('SeasonEventParticipantFinance as b', 'p.SeasonEventParticipantFinanceID', '=', 'b.SeasonEventParticipantFinanceID')
            ->where('b.SeasonEventID', $seasonEventID)
            ->where('p.PaymentType', 'REFUND')
            ->sum('p.Amount'),
    ];

    $bookings = DB::table('SeasonEventParticipantFinance as b')
        ->leftJoin('PersonInformation as p', 'b.PersonID', '=', 'p.PersonID')
        ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
        ->leftJoin('Guests as g', 'b.GuestID', '=', 'g.GuestID')
        ->leftJoin('FamilyMembers as f', 'b.FamilyID', '=', 'f.FamilyID')
        ->leftJoin('PersonInformation as s', 'b.ServentID', '=', 's.PersonID')
        ->leftJoin('PersonQetaa as pq', 'p.PersonID', '=', 'pq.PersonID')
        ->leftJoin('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
        ->where('b.SeasonEventID', $seasonEventID)
        ->select(
            'b.SeasonEventParticipantFinanceID',
            'b.PersonID',
            'b.GuestID',
            'b.FamilyID',
            'b.FirstPaymentDate',
            'b.OriginalPrice',
            'b.DiscountAmount',
            'b.FinalRequiredAmount',
            'b.AmountPaid',
            'b.RemainingAmount',
            'b.InstallmentsNumber',
            'b.SpecialCaseType',
            'b.SpecialCaseNote',
            'b.IsRefunded',
            'b.ShirtSize',
            'b.HasPersonSpecialCase',

            DB::raw("
                CASE
                    WHEN b.PersonID IS NOT NULL THEN 'PERSON'
                    WHEN b.FamilyID IS NOT NULL THEN 'FAMILY'
                    WHEN b.GuestID IS NOT NULL THEN 'GUEST'
                    ELSE 'UNKNOWN'
                END as BookingEntityType
            "),

            DB::raw("
                CASE
                    WHEN b.PersonID IS NOT NULL THEN CONCAT('SH-', b.PersonID)
                    WHEN b.FamilyID IS NOT NULL THEN CONCAT('FM-', b.FamilyID)
                    WHEN b.GuestID IS NOT NULL THEN CONCAT('GU-', b.GuestID)
                    ELSE '-'
                END as BookingCode
            "),

            DB::raw("
                TRIM(CONCAT(
                    COALESCE(p.FirstName, g.FirstName, f.FirstName, ''), ' ',
                    COALESCE(p.SecondName, g.SecondName, f.SecondName, ''), ' ',
                    COALESCE(p.ThirdName, g.ThirdName, f.ThirdName, ''), ' ',
                    COALESCE(p.FourthName, g.FourthName, f.FourthName, '')
                )) as PersonFullName
            "),

            DB::raw("COALESCE(ppn.PersonPersonalMobileNumber, g.MobileNumber, f.MobileNumber, '-') as PersonPersonalMobileNumber"),

            DB::raw("
                TRIM(CONCAT(
                    COALESCE(s.FirstName,''), ' ',
                    COALESCE(s.SecondName,''), ' ',
                    COALESCE(s.ThirdName,''), ' ',
                    COALESCE(s.FourthName,'')
                )) as ServentFullName
            "),

            DB::raw("
                CASE
                    WHEN b.FamilyID IS NOT NULL THEN 'اهالي'
                    WHEN b.GuestID IS NOT NULL THEN 'ضيوف'
                    ELSE COALESCE(GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ' , '), '-')
                END as QetaaNames
            "),

            DB::raw("(SELECT COUNT(*) FROM SeasonEventParticipantFinancePayment p2
                      WHERE p2.SeasonEventParticipantFinanceID = b.SeasonEventParticipantFinanceID
                      AND p2.PaymentType = 'PAYMENT') as PaymentsCount"),

            DB::raw("(SELECT MIN(p0.PaymentDate) FROM SeasonEventParticipantFinancePayment p0
                      WHERE p0.SeasonEventParticipantFinanceID = b.SeasonEventParticipantFinanceID
                      AND p0.PaymentType = 'PAYMENT') as FirstPaymentAt"),

            DB::raw("(SELECT MAX(p3.PaymentDate) FROM SeasonEventParticipantFinancePayment p3
                      WHERE p3.SeasonEventParticipantFinanceID = b.SeasonEventParticipantFinanceID) as LastPaymentAt"),

            DB::raw("(SELECT p4.PaymentID FROM SeasonEventParticipantFinancePayment p4
                      WHERE p4.SeasonEventParticipantFinanceID = b.SeasonEventParticipantFinanceID
                      ORDER BY p4.PaymentDate DESC, p4.PaymentID DESC
                      LIMIT 1) as LastPaymentID")
        )
        ->groupBy(
            'b.SeasonEventParticipantFinanceID',
            'b.PersonID',
            'b.GuestID',
            'b.FamilyID',
            'b.FirstPaymentDate',
            'b.OriginalPrice',
            'b.DiscountAmount',
            'b.FinalRequiredAmount',
            'b.AmountPaid',
            'b.RemainingAmount',
            'b.InstallmentsNumber',
            'b.SpecialCaseType',
            'b.SpecialCaseNote',
            'b.IsRefunded',
            'b.ShirtSize',
            'b.HasPersonSpecialCase',
            'ppn.PersonPersonalMobileNumber',
            'g.MobileNumber',
            'f.MobileNumber',
            'p.FirstName', 'p.SecondName', 'p.ThirdName', 'p.FourthName',
            'g.FirstName', 'g.SecondName', 'g.ThirdName', 'g.FourthName',
            'f.FirstName', 'f.SecondName', 'f.ThirdName', 'f.FourthName',
            's.FirstName', 's.SecondName', 's.ThirdName', 's.FourthName'
        )
        ->orderBy('PersonFullName')
        ->orderBy('b.SeasonEventParticipantFinanceID')
        ->paginate(25)
        ->appends($request->query())
        ->through(function ($booking) {
            if ($booking->SpecialCaseType === 'AKHOH_RAB' || (int) $booking->HasPersonSpecialCase === 1) {
                $booking->BookingStatusText = 'أخوه رب';
            } elseif ($booking->SpecialCaseType === 'HAS_BROTHERS') {
                $booking->BookingStatusText = 'له إخوة';
            } elseif ($booking->SpecialCaseType === 'OTHER') {
                $booking->BookingStatusText = 'أخرى';
            } else {
                $booking->BookingStatusText = 'عادي';
            }

            if ((int) $booking->IsRefunded === 1) {
                $booking->BookingStatusText .= ' - مسترد';
            }

            $booking->QetaaNames = $booking->QetaaNames ?: '-';
            $booking->ShirtSize = $booking->ShirtSize ?: '-';
            $booking->PersonPersonalMobileNumber = $booking->PersonPersonalMobileNumber ?: '-';

            $booking->FirstPaymentDateFormatted = $booking->FirstPaymentAt
                ? Carbon::parse($booking->FirstPaymentAt)->format('Y-m-d h:i A')
                : '-';

            $booking->LastPaymentDateFormatted = $booking->LastPaymentAt
                ? Carbon::parse($booking->LastPaymentAt)->format('Y-m-d h:i A')
                : '-';

            $booking->OriginalPriceFormatted = number_format((float) $booking->OriginalPrice, 2);
            $booking->DiscountAmountFormatted = number_format((float) $booking->DiscountAmount, 2);
            $booking->FinalRequiredAmountFormatted = number_format((float) $booking->FinalRequiredAmount, 2);
            $booking->AmountPaidFormatted = number_format((float) $booking->AmountPaid, 2);
            $booking->RemainingAmountFormatted = number_format((float) $booking->RemainingAmount, 2);
            $booking->PaymentsProgress = ((int) $booking->PaymentsCount) . ' / ' . ((int) $booking->InstallmentsNumber);

            $booking->CanAddInstallment =
                ((int) $booking->IsRefunded === 0
                    && (float) $booking->RemainingAmount > 0
                    && (int) $booking->PaymentsCount < (int) $booking->InstallmentsNumber) ? 1 : 0;

            $booking->CanEditLastPayment = !empty($booking->LastPaymentID) ? 1 : 0;
            $booking->CanPrintReceipt = !empty($booking->LastPaymentID) ? 1 : 0;
            $booking->CanRefund = ((int) $booking->IsRefunded === 0 && (float) $booking->AmountPaid > 0) ? 1 : 0;
            $booking->CanPartialRefund = ((int) $booking->IsRefunded === 0 && (float) $booking->AmountPaid > 0) ? 1 : 0;

            return $booking;
        });

$qetaaCounts = DB::table('SeasonEventParticipantFinance as b')
    ->leftJoin('PersonQetaa as pq', 'b.PersonID', '=', 'pq.PersonID')
    ->leftJoin('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
    ->where('b.SeasonEventID', $seasonEventID)
    ->where('b.IsRefunded', 0)
    ->select(
        DB::raw("
            CASE
                WHEN b.FamilyID IS NOT NULL THEN 'اهالي'
                WHEN b.GuestID IS NOT NULL THEN 'ضيوف'
                ELSE q.QetaaName
            END as QetaaName
        "),
        DB::raw('COUNT(*) as booked_count')
    )
    ->groupBy(DB::raw("
        CASE
            WHEN b.FamilyID IS NOT NULL THEN 'اهالي'
            WHEN b.GuestID IS NOT NULL THEN 'ضيوف'
            ELSE q.QetaaName
        END
    "))
    ->orderBy('QetaaName')
    ->get();

    return view('event_booking_finance.index', compact(
        'event',
        'bookings',
        'paymentDays',
        'selectedSummaryDate',
        'selectedDaySummary',
        'totalSummary',
        'qetaaCounts'
    ));
}

public function searchEligibleGuests(Request $request, $seasonEventID)
{
    $term = \App\Support\LikeSearch::fromRequest($request);
    $fields = \App\Support\LikeSearch::namedPartyFields('g', 'GuestID');

    $guests = DB::table('Guests as g')
        ->when($term !== null, function ($query) use ($term, $fields) {
            $query->where(function ($sub) use ($term, $fields) {
                \App\Support\LikeSearch::applyOr($sub, $term, $fields['columns'], $fields['raw']);
            });
        })
        ->select(
            'g.GuestID',
            'g.MobileNumber as PersonPersonalMobileNumber',
            DB::raw("
                TRIM(CONCAT(
                    COALESCE(g.FirstName,''), ' ',
                    COALESCE(g.SecondName,''), ' ',
                    COALESCE(g.ThirdName,''), ' ',
                    COALESCE(g.FourthName,'')
                )) as PersonFullName
            "),
            DB::raw("'ضيوف' as QetaaNames"),
            DB::raw('0 as IsBlacklisted'),
            DB::raw('0 as IsSpecialCase'),
            DB::raw("
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM SeasonEventParticipantFinance b
                    WHERE b.SeasonEventID = " . (int) $seasonEventID . "
                    AND b.GuestID = g.GuestID
                ) THEN 1 ELSE 0 END as AlreadyBooked
            ")
        )
        ->orderBy('PersonFullName')
        ->limit(20)
        ->get();

    return response()->json($guests);
}

public function searchEligibleFamilies(Request $request, $seasonEventID)
{
    $term = \App\Support\LikeSearch::fromRequest($request);
    $fields = \App\Support\LikeSearch::namedPartyFields('f', 'FamilyID');

    $families = DB::table('FamilyMembers as f')
        ->when($term !== null, function ($query) use ($term, $fields) {
            $query->where(function ($sub) use ($term, $fields) {
                \App\Support\LikeSearch::applyOr($sub, $term, $fields['columns'], $fields['raw']);
            });
        })
        ->select(
            'f.FamilyID',
            'f.MobileNumber as PersonPersonalMobileNumber',
            DB::raw("
                TRIM(CONCAT(
                    COALESCE(f.FirstName,''), ' ',
                    COALESCE(f.SecondName,''), ' ',
                    COALESCE(f.ThirdName,''), ' ',
                    COALESCE(f.FourthName,'')
                )) as PersonFullName
            "),
            DB::raw("'اهالي' as QetaaNames"),
            DB::raw('0 as IsBlacklisted'),
            DB::raw('0 as IsSpecialCase'),
            DB::raw("
                CASE WHEN EXISTS (
                    SELECT 1
                    FROM SeasonEventParticipantFinance b
                    WHERE b.SeasonEventID = " . (int) $seasonEventID . "
                    AND b.FamilyID = f.FamilyID
                ) THEN 1 ELSE 0 END as AlreadyBooked
            ")
        )
        ->orderBy('PersonFullName')
        ->limit(20)
        ->get();

    return response()->json($families);
}

    public function create($seasonEventID)
    {
        $event = $this->getSeasonEventFullInfo($seasonEventID);
        if (!$event) {
            abort(404);
        }

        $plan = DB::table('SeasonEventFinance')->where('SeasonEventID', $seasonEventID)->first();
        if (!$plan) {
            return redirect()->route('eventBookingFinance.selector')->withErrors([
                'general' => 'لا توجد خطة مالية لهذه الفعالية.'
            ]);
        }

        return view('event_booking_finance.create', compact('event', 'plan', 'seasonEventID'));
    }

    public function searchEligiblePersons(Request $request, $seasonEventID)
    {
        $term = \App\Support\LikeSearch::fromRequest($request);

        $event = DB::table('SeasonEvent')->where('SeasonEventID', $seasonEventID)->first();
        if (!$event) {
            return response()->json([]);
        }

        $eligibleQetaaIDs = DB::table('EventQetaa')
            ->where('EventID', $event->EventID)
            ->pluck('QetaaID')
            ->toArray();

        if (empty($eligibleQetaaIDs)) {
            return response()->json([]);
        }

        $persons = DB::table('PersonInformation as p')
            ->join('PersonQetaa as pq', 'p.PersonID', '=', 'pq.PersonID')
            ->join('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
            ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
            ->leftJoin('PersonBlackList as pb', 'p.PersonID', '=', 'pb.PersonID')
            ->leftJoin('PersonSpecialCase as psc', 'p.PersonID', '=', 'psc.PersonID')
            ->whereIn('pq.QetaaID', $eligibleQetaaIDs)
            ->when($term !== null, function ($query) use ($term) {
                $query->where(function ($sub) use ($term) {
                    \App\Support\LikeSearch::applyOr(
                        $sub,
                        $term,
                        ['p.PersonID', 'ppn.PersonPersonalMobileNumber'],
                        ["CONCAT_WS(' ', p.FirstName, p.SecondName, p.ThirdName, p.FourthName)"]
                    );
                });
            })
            ->select(
                'p.PersonID',
                'ppn.PersonPersonalMobileNumber',
                DB::raw("TRIM(CONCAT(
                    COALESCE(p.FirstName,''), ' ',
                    COALESCE(p.SecondName,''), ' ',
                    COALESCE(p.ThirdName,''), ' ',
                    COALESCE(p.FourthName,'')
                )) as PersonFullName"),
                DB::raw("GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ' , ') as QetaaNames"),
                DB::raw("CASE WHEN COUNT(DISTINCT pb.BlackListID) > 0 THEN 1 ELSE 0 END as IsBlacklisted"),
                DB::raw("CASE WHEN COUNT(DISTINCT psc.SpecialCaseID) > 0 THEN 1 ELSE 0 END as IsSpecialCase"),
                DB::raw("CASE WHEN EXISTS (
                    SELECT 1 FROM SeasonEventParticipantFinance b
                    WHERE b.SeasonEventID = " . (int)$seasonEventID . "
                    AND b.PersonID = p.PersonID
                ) THEN 1 ELSE 0 END as AlreadyBooked")
            )
            ->groupBy(
                'p.PersonID',
                'ppn.PersonPersonalMobileNumber',
                'p.FirstName',
                'p.SecondName',
                'p.ThirdName',
                'p.FourthName'
            )
            ->orderBy('PersonFullName')
            ->limit(20)
            ->get();

        return response()->json($persons);
    }

public function store(Request $request, $seasonEventID)
{
    $event = $this->getSeasonEventFullInfo($seasonEventID);
    $plan = DB::table('SeasonEventFinance')->where('SeasonEventID', $seasonEventID)->first();

    if (!$event || !$plan) {
        abort(404);
    }

    $validator = Validator::make($request->all(), [
        'booking_type' => 'required|in:PERSON,GUEST,FAMILY',
        'person_id' => 'nullable|integer|exists:PersonInformation,PersonID',
        'guest_id' => 'nullable|integer|exists:Guests,GuestID',
        'family_id' => 'nullable|integer|exists:FamilyMembers,FamilyID',
        'first_payment_date' => 'required|date',
                    'first_payment_amount' => 'required|numeric
                ',
        'is_not_able_to_pay_all' => 'nullable|in:0,1',
        'special_case_type' => 'nullable|in:NONE,AKHOH_RAB,HAS_BROTHERS,OTHER',
        'discount_amount' => 'nullable|numeric',
        'special_case_note' => 'nullable|string|max:500',
    ], [
        'booking_type.required' => 'يجب اختيار نوع الحجز.',
        'booking_type.in' => 'نوع الحجز غير صحيح.',
        'first_payment_date.required' => 'تاريخ أول دفعة مطلوب.',
        'first_payment_date.date' => 'تاريخ أول دفعة غير صحيح.',
        'first_payment_amount.required' => 'يجب إدخال مبلغ أول دفعة.',
        'first_payment_amount.min' => 'يجب أن يكون مبلغ أول دفعة أكبر من صفر.',
        'discount_amount.min' => 'الخصم لا يمكن أن يكون أقل من صفر.',
        'special_case_note.max' => 'الملاحظات يجب ألا تتجاوز 500 حرف.',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }


    $filledCount = 0;
$filledCount += $request->filled('person_id') ? 1 : 0;
$filledCount += $request->filled('guest_id') ? 1 : 0;
$filledCount += $request->filled('family_id') ? 1 : 0;

if ($filledCount !== 1) {
    return redirect()->back()->withErrors([
        'general' => 'يجب اختيار شخص واحد فقط من نوع واحد فقط.'
    ])->withInput();
}

    $bookingType = (string) $request->booking_type;
    $personID = null;
    $guestID = null;
    $familyID = null;
    $entityField = null;
    $entityValue = null;

    if ($bookingType === 'PERSON') {
        if (!$request->filled('person_id')) {
            return redirect()->back()->withErrors(['person_id' => 'يجب اختيار الشخص.'])->withInput();
        }
        $personID = (int) $request->person_id;
        $entityField = 'PersonID';
        $entityValue = $personID;
    } elseif ($bookingType === 'GUEST') {
        if (!$request->filled('guest_id')) {
            return redirect()->back()->withErrors(['guest_id' => 'يجب اختيار الضيف.'])->withInput();
        }
        $guestID = (int) $request->guest_id;
        $entityField = 'GuestID';
        $entityValue = $guestID;
    } else {
        if (!$request->filled('family_id')) {
            return redirect()->back()->withErrors(['family_id' => 'يجب اختيار فرد العائلة.'])->withInput();
        }
        $familyID = (int) $request->family_id;
        $entityField = 'FamilyID';
        $entityValue = $familyID;
    }

    $serventID = (int) Auth::user()->PersonID;

    if ($bookingType === 'PERSON') {
        if ($this->isBlacklisted($personID)) {
            return redirect()->back()->withErrors([
                'person_id' => 'هذا الشخص موجود في القائمة السوداء ولا يمكنه الحجز.'
            ])->withInput();
        }

        if (!$this->isEligibleByQetaa($seasonEventID, $personID)) {
            return redirect()->back()->withErrors([
                'person_id' => 'هذا الشخص غير مؤهل لهذه الفعالية.'
            ])->withInput();
        }
    }

    $alreadyBooked = DB::table('SeasonEventParticipantFinance')
        ->where('SeasonEventID', $seasonEventID)
        ->where($entityField, $entityValue)
        ->exists();

    if ($alreadyBooked) {
        return redirect()->back()->withErrors([
            'general' => 'هذا الحجز موجود بالفعل في هذه الفعالية.'
        ])->withInput();
    }

    $priceDate = Carbon::parse($request->first_payment_date)->format('Y-m-d');
    $paymentDateTime = now();

    $priceRow = DB::table('SeasonEventFinancePrice')
        ->where('SeasonEventID', $seasonEventID)
        ->where('StartDate', '<=', $priceDate)
        ->where('EndDate', '>=', $priceDate)
        ->orderBy('StartDate')
        ->first();

    if (!$priceRow) {
        return redirect()->back()->withErrors([
            'first_payment_date' => 'لا يوجد سعر صالح في هذا التاريخ.'
        ])->withInput();
    }

    $isPermanentSpecial = $bookingType === 'PERSON' ? $this->isSpecialCase($personID) : false;
    $specialCaseType = $request->filled('is_not_able_to_pay_all')
        ? ($request->special_case_type ?? 'NONE')
        : 'NONE';

    $hasPersonSpecialCase = ($isPermanentSpecial || $specialCaseType === 'AKHOH_RAB') ? 1 : 0;

    $discountAmount = (float) ($request->discount_amount ?? 0);
    $specialCaseNote = $request->special_case_note;
    $originalPrice = (float) $priceRow->Price;
    $finalRequiredAmount = max(0, $originalPrice - $discountAmount);
    $firstPaymentAmount = (float) $request->first_payment_amount;
    $installmentsNumber = (int) $plan->MaxInstallmentsNumber;

    if ($finalRequiredAmount <= 0) {
        return redirect()->back()->withErrors([
            'discount_amount' => 'المبلغ النهائي المطلوب يجب أن يكون أكبر من صفر.'
        ])->withInput();
    }

    if ($firstPaymentAmount > $finalRequiredAmount) {
        return redirect()->back()->withErrors([
            'first_payment_amount' => 'لا يمكن أن تكون أول دفعة أكبر من المبلغ المطلوب النهائي.'
        ])->withInput();
    }

    $isSpecialBehavior = $isPermanentSpecial || $specialCaseType === 'AKHOH_RAB';

    if (!$isSpecialBehavior && (int) $plan->AllowBelowMinimumDeposit === 0 && $firstPaymentAmount < (float) $plan->MinimumDeposit) {
        return redirect()->back()->withErrors([
            'first_payment_amount' => 'لا يمكن أن تكون أول دفعة أقل من الحد الأدنى للمقدم.'
        ])->withInput();
    }

    if (($specialCaseType === 'HAS_BROTHERS' || $specialCaseType === 'OTHER') && $discountAmount <= 0) {
        return redirect()->back()->withErrors([
            'discount_amount' => 'يجب إدخال مبلغ خصم أكبر من صفر.'
        ])->withInput();
    }

    if ((int) $plan->MaxInstallmentsNumber === 1 && $firstPaymentAmount != $finalRequiredAmount) {
        return redirect()->back()->withErrors([
            'first_payment_amount' => 'هذه الفعالية تحتوي على قسط واحد فقط، لذلك يجب دفع كامل المبلغ في أول دفعة.'
        ])->withInput();
    }

    DB::beginTransaction();

    try {
        $bookingID = DB::table('SeasonEventParticipantFinance')->insertGetId([
            'SeasonEventID' => $seasonEventID,
            'PersonID' => $personID,
            'GuestID' => $guestID,
            'FamilyID' => $familyID,
            'ServentID' => $serventID,
            'FirstPaymentDate' => $paymentDateTime->format('Y-m-d H:i:s'),
            'OriginalPrice' => $originalPrice,
            'DiscountAmount' => $discountAmount,
            'FinalRequiredAmount' => $finalRequiredAmount,
            'SpecialCaseType' => $specialCaseType,
            'SpecialCaseNote' => $specialCaseNote,
            'HasPersonSpecialCase' => $hasPersonSpecialCase,
           'LockedPrice' => $finalRequiredAmount,
           'IsRefunded' => 0,
'RefundDate' => null,
            'InstallmentsNumber' => $installmentsNumber,
            'AmountPaid' => $firstPaymentAmount,
            'RemainingAmount' => max(0, $finalRequiredAmount - $firstPaymentAmount),
            'ShirtSize' => $request->shirt_size,
            'Notes' => $request->notes,
        ]);

        $paymentID = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
            'SeasonEventParticipantFinanceID' => $bookingID,
            'ServentID' => $serventID,
            'PaymentDate' => $paymentDateTime,
            'Amount' => $firstPaymentAmount,
            'InstallmentNumber' => 1,
            'PaymentType' => 'PAYMENT',
            'Notes' => 'أول دفعة',
        ]);

        $receiptID = DB::table('SeasonEventParticipantFinanceReceipt')->insertGetId([
            'PaymentID' => $paymentID,
            'ReceiptNumber' => 'TEMP',
            'IssuedAt' => now(),
            'IssuedByServentID' => $serventID,
        ]);

        DB::table('SeasonEventParticipantFinanceReceipt')
            ->where('ReceiptID', $receiptID)
            ->update([
                'ReceiptNumber' => 'REC-' . now()->format('i-H-d-m-y') . '-' . $receiptID,
            ]);

        DB::commit();

        return redirect()->route('eventBookingFinance.printReceipt', $paymentID)
            ->with('success', 'تم إنشاء الحجز بنجاح.');
    } catch (Exception $e) {
        DB::rollBack();

        return redirect()->back()->withErrors([
            'general' => 'حدث خطأ أثناء إنشاء الحجز.'
        ])->withInput();
    }
}


public function createGuestFamily($seasonEventID)
{
    $event = $this->getSeasonEventFullInfo($seasonEventID);
    if (!$event) {
        abort(404);
    }

    $plan = DB::table('SeasonEventFinance')->where('SeasonEventID', $seasonEventID)->first();
    if (!$plan) {
        return redirect()->route('eventBookingFinance.selector')->withErrors([
            'general' => 'لا توجد خطة مالية لهذه الفعالية.'
        ]);
    }

    return view('event_booking_finance.create_guest_family', compact('event', 'plan', 'seasonEventID'));
}

public function createInstallment($bookingID)
{
    $booking = $this->getBookingDetails($bookingID);
    if (!$booking) {
        abort(404);
    }

    if ((int)$booking->IsRefunded === 1) {
        return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
            ->withErrors(['general' => 'لا يمكن إضافة دفعة لحجز تم استرداده.']);
    }

    if ((float)$booking->RemainingAmount <= 0) {
        return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
            ->withErrors(['general' => 'لا يوجد مبلغ متبقٍ لإضافة دفعة جديدة.']);
    }

    $paymentsCount = $this->getPaymentsCount($bookingID);
    $nextInstallmentNumber = $paymentsCount + 1;

    $isLastInstallment = ($nextInstallmentNumber >= (int)$booking->InstallmentsNumber);

    $previousPayments = DB::table('SeasonEventParticipantFinancePayment as p')
        ->leftJoin('SeasonEventParticipantFinanceReceipt as r', 'p.PaymentID', '=', 'r.PaymentID')
        ->where('p.SeasonEventParticipantFinanceID', $bookingID)
        ->where('p.PaymentType', 'PAYMENT')
        ->select(
            'p.PaymentID',
            'p.PaymentDate',
            'p.Amount',
            'p.InstallmentNumber',
            'p.Notes',
            'r.ReceiptNumber'
        )
        ->orderBy('p.InstallmentNumber')
        ->get();

$isSpecialBehavior = $this->shouldBypassLastInstallmentCompletion(
    (int) $booking->PersonID,
    $booking->SpecialCaseType
);

$forceFullLastInstallment = $isLastInstallment && !$isSpecialBehavior;

return view('event_booking_finance.create_installment', compact(
    'booking',
    'nextInstallmentNumber',
    'isLastInstallment',
    'forceFullLastInstallment',
    'previousPayments'
));
}
public function storeInstallment(Request $request, $bookingID)
{
    $booking = $this->getBookingDetails($bookingID);
    if (!$booking) {
        abort(404);
    }

    if ((int)$booking->IsRefunded === 1) {
        return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
            ->withErrors(['general' => 'لا يمكن إضافة دفعة لحجز تم استرداده.']);
    }

    if ((float)$booking->RemainingAmount <= 0) {
        return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
            ->withErrors(['general' => 'لا يوجد مبلغ متبقٍ لإضافة دفعة جديدة.']);
    }

    $paymentsCount = $this->getPaymentsCount($bookingID);
    $nextInstallmentNumber = $paymentsCount + 1;

    $isLastInstallment = ($nextInstallmentNumber >= (int)$booking->InstallmentsNumber);

    $validator = Validator::make($request->all(), [
        'amount' => 'required|numeric',
        'notes' => 'nullable|string|max:500'
    ], [
        'amount.required' => 'يجب إدخال مبلغ الدفعة.',
        'amount.min' => 'يجب أن يكون مبلغ الدفعة أكبر من صفر.'
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

 $remaining = (float) $booking->RemainingAmount;

$isSpecialBehavior = $this->shouldBypassLastInstallmentCompletion(
    (int) $booking->PersonID,
    $booking->SpecialCaseType
);

$forceFullLastInstallment = $isLastInstallment && !$isSpecialBehavior;

$amount = $forceFullLastInstallment ? $remaining : (float) $request->amount;

    if ($amount > $remaining) {
        return redirect()->back()->withErrors([
            'amount' => 'لا يمكن أن تكون الدفعة أكبر من المبلغ المتبقي.'
        ])->withInput();
    }

    DB::beginTransaction();

    try {
        $paymentID = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
            'SeasonEventParticipantFinanceID' => $bookingID,
            'ServentID' => (int)Auth::user()->PersonID,
            'PaymentDate' => now(),
            'Amount' => $amount,
            'InstallmentNumber' => $nextInstallmentNumber,
            'PaymentType' => 'PAYMENT',
            'Notes' => $forceFullLastInstallment
                ? trim(($request->notes ? $request->notes . ' | ' : '') . 'آخر قسط - تم تحصيل كامل المتبقي تلقائيًا')
                : $request->notes,
        ]);

        $newPaid = (float)$booking->AmountPaid + $amount;
        $newRemaining = max(0, (float)$booking->FinalRequiredAmount - $newPaid);

        DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventParticipantFinanceID', $bookingID)
            ->update([
                'AmountPaid' => $newPaid,
                'RemainingAmount' => $newRemaining,
            ]);

        $receiptID = DB::table('SeasonEventParticipantFinanceReceipt')->insertGetId([
            'PaymentID' => $paymentID,
            'ReceiptNumber' => 'TEMP',
            'IssuedAt' => now(),
            'IssuedByServentID' => (int)Auth::user()->PersonID,
        ]);

        $receiptNumber = 'REC-' . now()->format('i-H-d-m-y') . '-' . $receiptID;

        DB::table('SeasonEventParticipantFinanceReceipt')
            ->where('ReceiptID', $receiptID)
            ->update(['ReceiptNumber' => $receiptNumber]);

        DB::commit();

        return redirect()->route('eventBookingFinance.printReceipt', $paymentID)
            ->with('success', 'تم تسجيل الدفعة وإصدار الإيصال بنجاح.');
    } catch (Exception $e) {
        DB::rollBack();

        return redirect()->back()->withErrors([
            'general' => 'حدث خطأ أثناء تسجيل الدفعة.'
        ])->withInput();
    }
}

    public function editLastPayment($paymentID)
    {
        $payment = $this->getPaymentWithBooking($paymentID);
        if (!$payment) {
            abort(404);
        }

        if (!$this->isLastPayment($payment->SeasonEventParticipantFinanceID, $paymentID)) {
            return redirect()->route('eventBookingFinance.index', $payment->SeasonEventID)
                ->withErrors(['general' => 'يمكن تعديل آخر دفعة فقط.']);
        }

        return view('event_booking_finance.edit_last_payment', compact('payment'));
    }

    public function updateLastPayment(Request $request, $paymentID)
    {
        $payment = $this->getPaymentWithBooking($paymentID);
        if (!$payment) {
            abort(404);
        }

        if (!$this->isLastPayment($payment->SeasonEventParticipantFinanceID, $paymentID)) {
            return redirect()->route('eventBookingFinance.index', $payment->SeasonEventID)
                ->withErrors(['general' => 'يمكن تعديل آخر دفعة فقط.']);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $newAmount = (float)$request->amount;
        $bookingID = (int)$payment->SeasonEventParticipantFinanceID;
        $booking = $this->getBookingDetails($bookingID);
            if (!$booking) {
                abort(404);
            }

            $isSpecialBehavior = $this->shouldBypassLastInstallmentCompletion(
                (int) $booking->PersonID,
                $booking->SpecialCaseType
            );

        $otherPaymentsTotal = (float)DB::table('SeasonEventParticipantFinancePayment')
            ->where('SeasonEventParticipantFinanceID', $bookingID)
            ->where('PaymentType', 'PAYMENT')
            ->where('PaymentID', '<>', $paymentID)
            ->sum('Amount');

        $paymentsCount = $this->getPaymentsCount($bookingID);
        $isLastInstallment = ((int)$payment->InstallmentNumber === (int)$payment->InstallmentsNumber);

        $maxAllowed = max(0, (float)$payment->FinalRequiredAmount - $otherPaymentsTotal);

        if ($newAmount > $maxAllowed) {
            return redirect()->back()->withErrors([
                'amount' => 'المبلغ الجديد أكبر من المتبقي المسموح.'
            ])->withInput();
        }

        if (!$isSpecialBehavior && $isLastInstallment && abs($newAmount - $maxAllowed) > 0.009) {
            return redirect()->back()->withErrors([
                'amount' => 'لأنها آخر دفعة، يجب أن تساوي كل المتبقي.'
            ])->withInput();
        }

        DB::beginTransaction();

        try {
            DB::table('SeasonEventParticipantFinancePayment')
                ->where('PaymentID', $paymentID)
                ->update([
                    'Amount' => $newAmount,
                    'Notes' => trim(($payment->Notes ? $payment->Notes . ' | ' : '') . 'تم تعديل مبلغ آخر دفعة')
                ]);

            $newPaid = $otherPaymentsTotal + $newAmount;
            $newRemaining = max(0, (float)$payment->FinalRequiredAmount - $newPaid);

            DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventParticipantFinanceID', $bookingID)
                ->update([
                    'AmountPaid' => $newPaid,
                    'RemainingAmount' => $newRemaining,
                ]);

            DB::commit();

            return redirect()->route('eventBookingFinance.printReceipt', $paymentID)
                ->with('success', 'تم تعديل مبلغ آخر دفعة بنجاح.');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors([
                'general' => 'حدث خطأ أثناء تعديل آخر دفعة.'
            ])->withInput();
        }
    }

    public function refundPage($bookingID)
    {
        $booking = $this->getBookingDetails($bookingID);
        if (!$booking) {
            abort(404);
        }

        return view('event_booking_finance.refund', compact('booking'));
    }

    public function refundStore(Request $request, $bookingID)
    {
        $booking = $this->getBookingDetails($bookingID);
        if (!$booking) {
            abort(404);
        }

        if ((int)$booking->IsRefunded === 1) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => 'تم استرداد هذا الحجز مسبقًا.']);
        }

        if ((float)$booking->AmountPaid <= 0) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => 'لا يوجد مبلغ مدفوع لاسترداده.']);
        }

        DB::beginTransaction();

        try {
            $paymentID = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
                'SeasonEventParticipantFinanceID' => $bookingID,
                'ServentID' => (int)Auth::user()->PersonID,
                'PaymentDate' => now(),
                'Amount' => (float)$booking->AmountPaid,
                'InstallmentNumber' => $this->getPaymentsCount($bookingID) + 1,
                'PaymentType' => 'REFUND',
                'Notes' => 'استرداد كامل لكل المبلغ المدفوع',
            ]);

            DB::table('SeasonEventParticipantFinance')
                ->where('SeasonEventParticipantFinanceID', $bookingID)
                ->update([
                    'IsRefunded' => 1,
                    'RefundDate' => now(),
                    'RemainingAmount' => (float)$booking->FinalRequiredAmount,
                    'AmountPaid' => 0,
                ]);

            $receiptID = DB::table('SeasonEventParticipantFinanceReceipt')->insertGetId([
                'PaymentID' => $paymentID,
                'ReceiptNumber' => 'TEMP',
                'IssuedAt' => now(),
                'IssuedByServentID' => (int)Auth::user()->PersonID,
            ]);

            $receiptNumber = 'REC-' . now()->format('i-H-d-m-y') . '-' . $receiptID;

            DB::table('SeasonEventParticipantFinanceReceipt')
                ->where('ReceiptID', $receiptID)
                ->update(['ReceiptNumber' => $receiptNumber]);

            DB::commit();

            return redirect()->route('eventBookingFinance.printReceipt', $paymentID)
                ->with('success', 'تم استرداد كل المبلغ المدفوع بنجاح.');
        } catch (Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors([
                'general' => 'حدث خطأ أثناء الاسترداد.'
            ]);
        }
    }

public function printReceipt($paymentID)
{
    $receipt = DB::table('SeasonEventParticipantFinanceReceipt as r')
        ->join('SeasonEventParticipantFinancePayment as p', 'r.PaymentID', '=', 'p.PaymentID')
        ->join('SeasonEventParticipantFinance as b', 'p.SeasonEventParticipantFinanceID', '=', 'b.SeasonEventParticipantFinanceID')
        ->join('SeasonEvent as se', 'b.SeasonEventID', '=', 'se.SeasonEventID')
        ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
        ->join('Event as e', 'se.EventID', '=', 'e.EventID')
        ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
        ->leftJoin('PersonInformation as person', 'b.PersonID', '=', 'person.PersonID')
        ->leftJoin('PersonPhoneNumbers as ppn', 'person.PersonID', '=', 'ppn.PersonID')
        ->leftJoin('Guests as guest', 'b.GuestID', '=', 'guest.GuestID')
        ->leftJoin('FamilyMembers as family', 'b.FamilyID', '=', 'family.FamilyID')
        ->join('PersonInformation as servant', 'p.ServentID', '=', 'servant.PersonID')
        ->where('p.PaymentID', $paymentID)
        ->select(
            'r.ReceiptID',
            'r.ReceiptNumber',
            'r.IssuedAt',
            'p.PaymentID',
            'p.PaymentDate',
            'p.Amount',
            'p.PaymentType',
            'p.InstallmentNumber',
            'b.SeasonEventParticipantFinanceID',
            'b.PersonID',
            'b.GuestID',
            'b.FamilyID',
            'b.OriginalPrice',
            'b.DiscountAmount',
            'b.FinalRequiredAmount',
            'b.AmountPaid',
            'b.RemainingAmount',
            'b.InstallmentsNumber',
            's.SeasonName',
            's.SeasonYear',
            'e.EventName',
            'et.EventTypeName',
            DB::raw("COALESCE(ppn.PersonPersonalMobileNumber, guest.MobileNumber, family.MobileNumber, '-') as PersonPersonalMobileNumber"),
            DB::raw("
                CASE
                    WHEN b.PersonID IS NOT NULL THEN CONCAT('SH-', b.PersonID)
                    WHEN b.FamilyID IS NOT NULL THEN CONCAT('FM-', b.FamilyID)
                    WHEN b.GuestID IS NOT NULL THEN CONCAT('GU-', b.GuestID)
                    ELSE '-'
                END as BookingCode
            "),
            DB::raw("
                TRIM(CONCAT(
                    COALESCE(person.FirstName, guest.FirstName, family.FirstName, ''), ' ',
                    COALESCE(person.SecondName, guest.SecondName, family.SecondName, ''), ' ',
                    COALESCE(person.ThirdName, guest.ThirdName, family.ThirdName, ''), ' ',
                    COALESCE(person.FourthName, guest.FourthName, family.FourthName, '')
                )) as PersonFullName
            "),
            DB::raw("
                TRIM(CONCAT(
                    COALESCE(servant.FirstName,''), ' ',
                    COALESCE(servant.SecondName,''), ' ',
                    COALESCE(servant.ThirdName,''), ' ',
                    COALESCE(servant.FourthName,'')
                )) as ServentFullName
            ")
        )
        ->first();

    if (!$receipt) {
        abort(404);
    }

    $safePersonName = preg_replace('/[^A-Za-z0-9\-]+/', '-', $receipt->PersonFullName);
    $safePersonName = trim($safePersonName, '-');
    $fileName = 'Receipt-' . $receipt->ReceiptNumber . '-' . $safePersonName . '-' . date('Y') . '.pdf';

    return view('event_booking_finance.print_receipt', compact('receipt', 'fileName'));
}


    private function getSeasonEventFullInfo($seasonEventID)
    {
        return DB::table('SeasonEvent as se')
            ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->where('se.SeasonEventID', $seasonEventID)
            ->select(
                'se.SeasonEventID',
                'se.EventID',
                's.SeasonName',
                's.SeasonYear',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate',
                'et.EventTypeName'
            )
            ->first();
    }

    private function isEligibleByQetaa($seasonEventID, $personID)
    {
        $event = DB::table('SeasonEvent')->where('SeasonEventID', $seasonEventID)->first();
        if (!$event) {
            return false;
        }

        return DB::table('EventQetaa as eq')
            ->join('PersonQetaa as pq', 'eq.QetaaID', '=', 'pq.QetaaID')
            ->where('eq.EventID', $event->EventID)
            ->where('pq.PersonID', $personID)
            ->exists();
    }

    private function isBlacklisted($personID)
    {
        return DB::table('PersonBlackList')->where('PersonID', $personID)->exists();
    }

    private function isSpecialCase($personID)
    {
        return DB::table('PersonSpecialCase')->where('PersonID', $personID)->exists();
    }

private function getBookingDetails($bookingID)
{
    return DB::table('SeasonEventParticipantFinance as b')
        ->join('SeasonEvent as se', 'b.SeasonEventID', '=', 'se.SeasonEventID')
        ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
        ->join('Event as e', 'se.EventID', '=', 'e.EventID')
        ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
        ->leftJoin('PersonInformation as p', 'b.PersonID', '=', 'p.PersonID')
        ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
        ->leftJoin('Guests as g', 'b.GuestID', '=', 'g.GuestID')
        ->leftJoin('FamilyMembers as f', 'b.FamilyID', '=', 'f.FamilyID')
        ->where('b.SeasonEventParticipantFinanceID', $bookingID)
        ->select(
            'b.*',
            DB::raw("
                TRIM(CONCAT(
                    COALESCE(p.FirstName, g.FirstName, f.FirstName, ''), ' ',
                    COALESCE(p.SecondName, g.SecondName, f.SecondName, ''), ' ',
                    COALESCE(p.ThirdName, g.ThirdName, f.ThirdName, ''), ' ',
                    COALESCE(p.FourthName, g.FourthName, f.FourthName, '')
                )) as PersonFullName
            "),
            DB::raw("COALESCE(ppn.PersonPersonalMobileNumber, g.MobileNumber, f.MobileNumber, '-') as PersonPersonalMobileNumber"),
            DB::raw("
                CASE
                    WHEN b.PersonID IS NOT NULL THEN CONCAT('SH-', b.PersonID)
                    WHEN b.FamilyID IS NOT NULL THEN CONCAT('FM-', b.FamilyID)
                    WHEN b.GuestID IS NOT NULL THEN CONCAT('GU-', b.GuestID)
                    ELSE '-'
                END as BookingCode
            "),
            DB::raw("
                CASE
                    WHEN b.PersonID IS NOT NULL THEN 'شخص'
                    WHEN b.FamilyID IS NOT NULL THEN 'اهالي'
                    WHEN b.GuestID IS NOT NULL THEN 'ضيوف'
                    ELSE '-'
                END as BookingEntityLabel
            "),
            's.SeasonName',
            's.SeasonYear',
            'e.EventName',
            'et.EventTypeName'
        )
        ->first();
}


    private function getPaymentsCount($bookingID)
    {
        return (int)DB::table('SeasonEventParticipantFinancePayment')
            ->where('SeasonEventParticipantFinanceID', $bookingID)
            ->where('PaymentType', 'PAYMENT')
            ->count();
    }

private function getPaymentWithBooking($paymentID)
{
    return DB::table('SeasonEventParticipantFinancePayment as p')
        ->join('SeasonEventParticipantFinance as b', 'p.SeasonEventParticipantFinanceID', '=', 'b.SeasonEventParticipantFinanceID')
        ->where('p.PaymentID', $paymentID)
        ->select(
            'p.*',
            'b.SeasonEventID',
            'b.FinalRequiredAmount',
            'b.InstallmentsNumber',
            'b.PersonID',
            'b.GuestID',
            'b.FamilyID'
        )
        ->first();
}


    private function isLastPayment($bookingID, $paymentID)
    {
        $lastPayment = DB::table('SeasonEventParticipantFinancePayment')
            ->where('SeasonEventParticipantFinanceID', $bookingID)
            ->orderByDesc('PaymentDate')
            ->orderByDesc('PaymentID')
            ->first();

        return $lastPayment && (int)$lastPayment->PaymentID === (int)$paymentID;
    }

    public function partialRefundPage($bookingID)
{
    $booking = $this->getBookingDetails($bookingID);
    if (!$booking) {
        abort(404);
    }

    if ((int)$booking->IsRefunded === 1) {
        return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
            ->withErrors(['general' => 'تم استرداد هذا الحجز مسبقًا.']);
    }

    if ((float)$booking->AmountPaid <= 0) {
        return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
            ->withErrors(['general' => 'لا يوجد مبلغ مدفوع لاسترداده.']);
    }

    return view('event_booking_finance.partial_refund', compact('booking'));
}

public function partialRefundStore(Request $request, $bookingID)
{
    $booking = $this->getBookingDetails($bookingID);
    if (!$booking) {
        abort(404);
    }

    if ((int)$booking->IsRefunded === 1) {
        return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
            ->withErrors(['general' => 'تم استرداد هذا الحجز مسبقًا.']);
    }

    if ((float)$booking->AmountPaid <= 0) {
        return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
            ->withErrors(['general' => 'لا يوجد مبلغ مدفوع لاسترداده.']);
    }

    $validator = Validator::make($request->all(), [
        'deduction_amount' => 'required|numeric',
        'notes' => 'nullable|string|max:500',
    ], [
        'deduction_amount.required' => 'يجب إدخال مبلغ الجزء المخصوم.',
        'deduction_amount.numeric' => 'مبلغ الجزء المخصوم يجب أن يكون رقمًا.',
        'deduction_amount.min' => 'مبلغ الجزء المخصوم لا يمكن أن يكون أقل من صفر.',
        'notes.max' => 'الملاحظات يجب ألا تتجاوز 500 حرف.',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $amountPaid = (float)$booking->AmountPaid;
    $deductionAmount = (float)$request->deduction_amount;
    $refundAmount = $amountPaid - $deductionAmount;

    if ($deductionAmount > $amountPaid) {
        return redirect()->back()->withErrors([
            'deduction_amount' => 'الجزء المخصوم يجب أن يكون أقل من المبلغ المدفوع.'
        ])->withInput();
    }

    if ($refundAmount < 0) {
        return redirect()->back()->withErrors([
            'deduction_amount' => 'المبلغ المسترد يجب أن يكون أكبر من صفر.'
        ])->withInput();
    }

    DB::beginTransaction();

    try {
        $paymentID = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
            'SeasonEventParticipantFinanceID' => $bookingID,
            'ServentID' => (int)Auth::user()->PersonID,
            'PaymentDate' => now(),
            'Amount' => $refundAmount,
            'InstallmentNumber' => $this->getPaymentsCount($bookingID) + 1,
            'PaymentType' => 'REFUND',
            'Notes' => 'استرداد مع خصم جزء | المدفوع: ' . number_format($amountPaid, 2) .
                ' | المخصوم: ' . number_format($deductionAmount, 2) .
                ' | المسترد: ' . number_format($refundAmount, 2) .
                ($request->filled('notes') ? ' | ' . $request->notes : ''),
        ]);

        DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventParticipantFinanceID', $bookingID)
            ->update([
                'IsRefunded' => 1,
                'RefundDate' => now(),
                'AmountPaid' => $deductionAmount,
                'RemainingAmount' => 0,
            ]);

        $receiptID = DB::table('SeasonEventParticipantFinanceReceipt')->insertGetId([
            'PaymentID' => $paymentID,
            'ReceiptNumber' => 'TEMP',
            'IssuedAt' => now(),
            'IssuedByServentID' => (int)Auth::user()->PersonID,
        ]);

        $receiptNumber = 'REC-' . now()->format('i-H-d-m-y') . '-' . $receiptID;

        DB::table('SeasonEventParticipantFinanceReceipt')
            ->where('ReceiptID', $receiptID)
            ->update(['ReceiptNumber' => $receiptNumber]);

        DB::commit();

        return redirect()->route('eventBookingFinance.printReceipt', $paymentID)
            ->with('success', 'تم استرداد المبلغ بعد خصم جزء منه بنجاح.');
    } catch (Exception $e) {
        DB::rollBack();

        return redirect()->back()->withErrors([
            'general' => 'حدث خطأ أثناء تنفيذ الاسترداد مع خصم جزء.'
        ])->withInput();
    }
}


    public function exportToday(Request $request, $seasonEventID)
    {
        return $this->downloadBookingsCsv($seasonEventID, true, $request->summary_date);
    }

    public function exportAll($seasonEventID)
    {
        return $this->downloadBookingsCsv($seasonEventID, false);
    }

    private function downloadBookingsCsv($seasonEventID, $filteredDayOnly = false, $summaryDate = null)
{
    $event = $this->getSeasonEventFullInfo($seasonEventID);
    if (!$event) {
        abort(404);
    }

    $query = DB::table('SeasonEventParticipantFinance as b')
        ->leftJoin('PersonInformation as p', 'b.PersonID', '=', 'p.PersonID')
        ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
        ->leftJoin('PersonQetaa as pq', 'p.PersonID', '=', 'pq.PersonID')
        ->leftJoin('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
        ->leftJoin('Guests as g', 'b.GuestID', '=', 'g.GuestID')
        ->leftJoin('FamilyMembers as f', 'b.FamilyID', '=', 'f.FamilyID')
        ->where('b.SeasonEventID', $seasonEventID)
        ->select(
            'b.PersonID',
            'b.GuestID',
            'b.FamilyID',
            'b.FirstPaymentDate',
            'b.OriginalPrice',
            'b.DiscountAmount',
            'b.FinalRequiredAmount',
            'b.AmountPaid',
            'b.RemainingAmount',
            'b.InstallmentsNumber',
            'b.SpecialCaseType',
            'b.IsRefunded',
            'b.ShirtSize',
            'b.HasPersonSpecialCase',
            DB::raw("CASE WHEN b.HasPersonSpecialCase = 1 THEN 'نعم' ELSE 'لا' END as HasPersonSpecialCaseText"),
            DB::raw("
                CASE
                    WHEN b.PersonID IS NOT NULL THEN CONCAT('SH-', b.PersonID)
                    WHEN b.FamilyID IS NOT NULL THEN CONCAT('FM-', b.FamilyID)
                    WHEN b.GuestID IS NOT NULL THEN CONCAT('GU-', b.GuestID)
                    ELSE '-'
                END as BookingCode
            "),
            DB::raw("
                TRIM(CONCAT(
                    COALESCE(p.FirstName, g.FirstName, f.FirstName, ''), ' ',
                    COALESCE(p.SecondName, g.SecondName, f.SecondName, ''), ' ',
                    COALESCE(p.ThirdName, g.ThirdName, f.ThirdName, ''), ' ',
                    COALESCE(p.FourthName, g.FourthName, f.FourthName, '')
                )) as PersonFullName
            "),
            DB::raw("COALESCE(ppn.PersonPersonalMobileNumber, g.MobileNumber, f.MobileNumber, '-') as PersonPersonalMobileNumber"),
            DB::raw("
                CASE
                    WHEN b.FamilyID IS NOT NULL THEN 'اهالي'
                    WHEN b.GuestID IS NOT NULL THEN 'ضيوف'
                    ELSE COALESCE(GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ' , '), '-')
                END as QetaaNames
            ")
        )
        ->groupBy(
            'b.PersonID',
            'b.GuestID',
            'b.FamilyID',
            'b.FirstPaymentDate',
            'b.OriginalPrice',
            'b.DiscountAmount',
            'b.FinalRequiredAmount',
            'b.AmountPaid',
            'b.RemainingAmount',
            'b.InstallmentsNumber',
            'b.SpecialCaseType',
            'b.IsRefunded',
            'b.ShirtSize',
            'b.HasPersonSpecialCase',
            'ppn.PersonPersonalMobileNumber',
            'g.MobileNumber',
            'f.MobileNumber',
            'p.FirstName', 'p.SecondName', 'p.ThirdName', 'p.FourthName',
            'g.FirstName', 'g.SecondName', 'g.ThirdName', 'g.FourthName',
            'f.FirstName', 'f.SecondName', 'f.ThirdName', 'f.FourthName'
        );

    if ($filteredDayOnly) {
        $dateToUse = $summaryDate ?: Carbon::today()->toDateString();
        $query->whereDate('b.FirstPaymentDate', $dateToUse);
    }

    $rows = $query->orderBy('PersonFullName')->get();

    $fileName = $filteredDayOnly
        ? 'event-bookings-today-' . $seasonEventID . '.csv'
        : 'event-bookings-all-' . $seasonEventID . '.csv';

    return response()->streamDownload(function () use ($rows) {
        $handle = fopen('php://output', 'w');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($handle, [
            'الكود',
            'الاسم',
            'الموبايل',
            'القطاع',
            'حجم القميص',
            'أخوه رب / PersonSpecialCase',
            'الحالة',
            'تاريخ أول دفعة',
            'السعر الأصلي',
            'الخصم',
            'المطلوب النهائي',
            'المدفوع',
            'المتبقي',
            'عدد الأقساط',
            'مسترد؟',
        ]);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row->BookingCode,
                $row->PersonFullName,
                $row->PersonPersonalMobileNumber,
                $row->QetaaNames,
                $row->ShirtSize,
                $row->HasPersonSpecialCaseText,
                $row->SpecialCaseType,
                $row->FirstPaymentDate,
                $row->OriginalPrice,
                $row->DiscountAmount,
                $row->FinalRequiredAmount,
                $row->AmountPaid,
                $row->RemainingAmount,
                $row->InstallmentsNumber,
                $row->IsRefunded ? 'نعم' : 'لا',
            ]);
        }

        fclose($handle);
    }, $fileName, [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
}


  public function show($bookingID)
{
    $booking = DB::table('SeasonEventParticipantFinance as b')
        ->join('SeasonEvent as se', 'b.SeasonEventID', '=', 'se.SeasonEventID')
        ->join('Season as sn', 'se.SeasonID', '=', 'sn.SeasonID')
        ->join('Event as e', 'se.EventID', '=', 'e.EventID')
        ->leftJoin('PersonInformation as p', 'b.PersonID', '=', 'p.PersonID')
        ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
        ->leftJoin('Guests as g', 'b.GuestID', '=', 'g.GuestID')
        ->leftJoin('FamilyMembers as f', 'b.FamilyID', '=', 'f.FamilyID')
        ->leftJoin('PersonInformation as s', 'b.ServentID', '=', 's.PersonID')
        ->where('b.SeasonEventParticipantFinanceID', $bookingID)
        ->select(
            'b.*',
            'se.SeasonEventID',
            'sn.SeasonName',
            'sn.SeasonYear',
            'e.EventName',
            'e.EventStartDate',
            'e.EventEndDate',
            DB::raw("COALESCE(ppn.PersonPersonalMobileNumber, g.MobileNumber, f.MobileNumber, '-') as PersonPersonalMobileNumber"),
            DB::raw("
                CASE
                    WHEN b.PersonID IS NOT NULL THEN CONCAT('SH-', b.PersonID)
                    WHEN b.FamilyID IS NOT NULL THEN CONCAT('FM-', b.FamilyID)
                    WHEN b.GuestID IS NOT NULL THEN CONCAT('GU-', b.GuestID)
                    ELSE '-'
                END as BookingCode
            "),
            DB::raw("
                CASE
                    WHEN b.FamilyID IS NOT NULL THEN 'اهالي'
                    WHEN b.GuestID IS NOT NULL THEN 'ضيوف'
                    ELSE 'شخص'
                END as BookingEntityLabel
            "),
            DB::raw("
                TRIM(CONCAT(
                    COALESCE(p.FirstName, g.FirstName, f.FirstName, ''), ' ',
                    COALESCE(p.SecondName, g.SecondName, f.SecondName, ''), ' ',
                    COALESCE(p.ThirdName, g.ThirdName, f.ThirdName, ''), ' ',
                    COALESCE(p.FourthName, g.FourthName, f.FourthName, '')
                )) as PersonFullName
            "),
            DB::raw("
                TRIM(CONCAT(
                    COALESCE(s.FirstName,''), ' ',
                    COALESCE(s.SecondName,''), ' ',
                    COALESCE(s.ThirdName,''), ' ',
                    COALESCE(s.FourthName,'')
                )) as ServentFullName
            ")
        )
        ->first();

    if (!$booking) {
        abort(404);
    }

    $payments = DB::table('SeasonEventParticipantFinancePayment as p')
        ->leftJoin('PersonInformation as s', 'p.ServentID', '=', 's.PersonID')
        ->where('p.SeasonEventParticipantFinanceID', $bookingID)
        ->select(
            'p.*',
            DB::raw("
                TRIM(CONCAT(
                    COALESCE(s.FirstName,''), ' ',
                    COALESCE(s.SecondName,''), ' ',
                    COALESCE(s.ThirdName,''), ' ',
                    COALESCE(s.FourthName,'')
                )) as ServentFullName
            ")
        )
        ->orderBy('p.PaymentDate')
        ->orderBy('p.PaymentID')
        ->get()
        ->map(function ($payment) {
            $payment->PaymentDateFormatted = $payment->PaymentDate
                ? \Carbon\Carbon::parse($payment->PaymentDate)->format('Y-m-d h:i A')
                : '-';

            $payment->AmountFormatted = number_format((float) $payment->Amount, 2);
            return $payment;
        });

    $canAddInstallment =
        ((int) $booking->IsRefunded === 0
            && (float) $booking->RemainingAmount > 0
            && $this->getPaymentsCount($bookingID) < (int) $booking->InstallmentsNumber);

    $canRefund =
        ((int) $booking->IsRefunded === 0 && (float) $booking->AmountPaid > 0);

    return view('event_booking_finance.show', compact(
        'booking',
        'payments',
        'canAddInstallment',
        'canRefund'
    ));
}

    public function updateShirtSize(Request $request, $bookingID)
    {
        $validator = Validator::make($request->all(), [
            'shirt_size' => 'required|in:XS,S,M,L,XL,2XL,3XL,4XL,5XL,6XL',
        ], [
            'shirt_size.required' => 'يجب اختيار مقاس القميص.',
            'shirt_size.in' => 'مقاس القميص غير صحيح.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $booking = DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventParticipantFinanceID', $bookingID)
            ->first();

        if (!$booking) {
            abort(404);
        }

        DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventParticipantFinanceID', $bookingID)
            ->update([
                'ShirtSize' => $request->shirt_size,
            ]);

        return redirect()->route('eventBookingFinance.show', $bookingID)
            ->with('success', 'تم تحديث مقاس القميص بنجاح.');
    }

}