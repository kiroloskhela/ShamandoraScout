<?php

namespace App\Http\Controllers;

use App\Domain\EventFinance\SeasonEventBookingEligibilitySearch;
use App\Domain\EventFinance\SeasonEventBookingPaymentService;
use App\Domain\EventFinance\SeasonEventBookingService;
use App\Http\Requests\StoreBookingInstallmentRequest;
use App\Http\Requests\StoreSeasonEventBookingRequest;
use App\Services\AttendanceQrService;
use App\Support\LikeSearch;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SeasonEventBookingFinanceController extends Controller
{
    public function __construct(
        private readonly SeasonEventBookingService $bookings,
        private readonly SeasonEventBookingPaymentService $payments,
        private readonly SeasonEventBookingEligibilitySearch $eligibility,
    ) {}

    public function selector()
    {
        $seasons = DB::table('Season')->orderBy('SeasonYear', 'desc')->get();

        return view('event_booking_finance.selector', compact('seasons'));
    }

    private function shouldBypassLastInstallmentCompletion($personID = null, $specialCaseType = null): bool
    {
        return $this->payments->shouldBypassLastInstallmentCompletion(
            $personID ? (int) $personID : null,
            $specialCaseType,
        );
    }

    private function authorizeFinanceUpdate(): void
    {
        $this->authorize('eventBooking.update');
    }

    private function authorizeFinanceDelete(): void
    {
        $this->authorize('eventBooking.delete');
    }

    public function getEventsWithPlan(Request $request)
    {
        $seasonID = $request->query('seasonID');

        if (! $seasonID) {
            return response()->json([]);
        }

        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->join('SeasonEventFinance as sef', 'se.SeasonEventID', '=', 'sef.SeasonEventID')
            ->where('se.SeasonID', $seasonID)
            ->where('et.TakesReservation', 1)
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
        if (! $event) {
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
        if (! $selectedSummaryDate || ! $paymentDays->contains($selectedSummaryDate)) {
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

                DB::raw('(SELECT MAX(p3.PaymentDate) FROM SeasonEventParticipantFinancePayment p3
                      WHERE p3.SeasonEventParticipantFinanceID = b.SeasonEventParticipantFinanceID) as LastPaymentAt'),

                DB::raw('(SELECT p4.PaymentID FROM SeasonEventParticipantFinancePayment p4
                      WHERE p4.SeasonEventParticipantFinanceID = b.SeasonEventParticipantFinanceID
                      ORDER BY p4.PaymentDate DESC, p4.PaymentID DESC
                      LIMIT 1) as LastPaymentID')
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
            ->get()
            ->map(function ($booking) {
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
                $booking->PaymentsProgress = ((int) $booking->PaymentsCount).' / '.((int) $booking->InstallmentsNumber);

                $booking->CanAddInstallment =
                    ((int) $booking->IsRefunded === 0
                        && (float) $booking->RemainingAmount > 0
                        && (int) $booking->PaymentsCount < (int) $booking->InstallmentsNumber) ? 1 : 0;

                $booking->CanEditLastPayment = ! empty($booking->LastPaymentID) ? 1 : 0;
                $booking->CanPrintReceipt = ! empty($booking->LastPaymentID) ? 1 : 0;
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

        $haveShirt = (int) ($this->bookings->getFinancePlan((int) $seasonEventID)?->HaveShirt ?? 0);

        return $this->noStoreView('event_booking_finance.index', [
            'event' => $event,
            'bookings' => $bookings,
            'paymentDays' => $paymentDays,
            'selectedSummaryDate' => $selectedSummaryDate,
            'selectedDaySummary' => $selectedDaySummary,
            'totalSummary' => $totalSummary,
            'qetaaCounts' => $qetaaCounts,
            'haveShirt' => $haveShirt,
        ]);
    }

    public function searchEligibleGuests(Request $request, $seasonEventID)
    {
        return response()->json(
            $this->eligibility->searchGuests((int) $seasonEventID, LikeSearch::fromRequest($request))
        );
    }

    public function searchEligibleFamilies(Request $request, $seasonEventID)
    {
        return response()->json(
            $this->eligibility->searchFamilies((int) $seasonEventID, LikeSearch::fromRequest($request))
        );
    }

    public function create($seasonEventID)
    {
        $event = $this->getSeasonEventFullInfo($seasonEventID);
        if (! $event) {
            abort(404);
        }

        $plan = DB::table('SeasonEventFinance')->where('SeasonEventID', $seasonEventID)->first();
        if (! $plan) {
            return redirect()->route('eventBookingFinance.selector')->withErrors([
                'general' => __('No finance plan exists for this event.'),
            ]);
        }

        return view('event_booking_finance.create', compact('event', 'plan', 'seasonEventID'));
    }

    public function searchEligiblePersons(Request $request, $seasonEventID)
    {
        return response()->json(
            $this->eligibility->searchPersons((int) $seasonEventID, LikeSearch::fromRequest($request))
        );
    }

    public function store(StoreSeasonEventBookingRequest $request, $seasonEventID)
    {
        $event = $this->bookings->getEventInfo((int) $seasonEventID);
        $plan = $this->bookings->getFinancePlan((int) $seasonEventID);

        if (! $event || ! $plan) {
            abort(404);
        }

        $filledCount = 0;
        $filledCount += $request->filled('person_id') ? 1 : 0;
        $filledCount += $request->filled('guest_id') ? 1 : 0;
        $filledCount += $request->filled('family_id') ? 1 : 0;

        if ($filledCount !== 1) {
            return redirect()->back()->withErrors([
                'general' => __('You must select exactly one person of one type only.'),
            ])->withInput();
        }

        $result = $this->bookings->createBooking(
            (int) $seasonEventID,
            $request->all(),
            (int) Auth::user()->PersonID
        );

        if (! $result['ok']) {
            return redirect()->back()->withErrors([
                $result['field'] => $result['message'],
            ])->withInput();
        }

        return $this->redirectToPrintReceipt(
            (int) $result['payment_id'],
            __('Booking created successfully.')
        );
    }

    public function createGuestFamily($seasonEventID)
    {
        $event = $this->getSeasonEventFullInfo($seasonEventID);
        if (! $event) {
            abort(404);
        }

        $plan = DB::table('SeasonEventFinance')->where('SeasonEventID', $seasonEventID)->first();
        if (! $plan) {
            return redirect()->route('eventBookingFinance.selector')->withErrors([
                'general' => __('No finance plan exists for this event.'),
            ]);
        }

        return view('event_booking_finance.create_guest_family', compact('event', 'plan', 'seasonEventID'));
    }

    public function createInstallment($bookingID)
    {
        $booking = $this->bookings->getBookingDetails($bookingID);
        if (! $booking) {
            abort(404);
        }

        if ((int) $booking->IsRefunded === 1) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => __('Cannot add a payment to a refunded booking.')]);
        }

        if ((float) $booking->RemainingAmount <= 0) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => __('No remaining amount to add a new payment.')]);
        }

        $paymentsCount = $this->bookings->countPayments($bookingID);
        $nextInstallmentNumber = $paymentsCount + 1;

        $isLastInstallment = ($nextInstallmentNumber >= (int) $booking->InstallmentsNumber);

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

        $forceFullLastInstallment = $isLastInstallment && ! $isSpecialBehavior;

        return view('event_booking_finance.create_installment', compact(
            'booking',
            'nextInstallmentNumber',
            'isLastInstallment',
            'forceFullLastInstallment',
            'previousPayments'
        ));
    }

    public function storeInstallment(StoreBookingInstallmentRequest $request, $bookingID)
    {
        $booking = $this->bookings->getBookingDetails($bookingID);
        if (! $booking) {
            abort(404);
        }

        if ((int) $booking->IsRefunded === 1) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => __('Cannot add a payment to a refunded booking.')]);
        }

        if ((float) $booking->RemainingAmount <= 0) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => __('No remaining amount to add a new payment.')]);
        }

        $paymentsCount = $this->bookings->countPayments($bookingID);

        $remaining = (float) $booking->RemainingAmount;
        $installment = $this->payments->calculateInstallment(
            $booking,
            $paymentsCount,
            (float) $request->amount,
            $request->notes,
        );
        $amount = (float) $installment['amount'];

        if ($amount > $remaining) {
            return redirect()->back()->withErrors([
                'amount' => __('Payment cannot exceed the remaining amount.'),
            ])->withInput();
        }

        try {
            $paymentID = $this->payments->recordInstallment(
                $booking,
                (int) $bookingID,
                (int) Auth::user()->PersonID,
                $installment,
            );

            return $this->redirectToPrintReceipt(
                (int) $paymentID,
                __('Payment recorded and receipt issued successfully.')
            );
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'general' => __('An error occurred while recording the payment.'),
            ])->withInput();
        }
    }

    public function editLastPayment($paymentID)
    {
        $payment = $this->bookings->getPaymentWithBooking($paymentID);
        if (! $payment) {
            abort(404);
        }

        if (! $this->bookings->isLastPayment($payment->SeasonEventParticipantFinanceID, $paymentID)) {
            return redirect()->route('eventBookingFinance.index', $payment->SeasonEventID)
                ->withErrors(['general' => __('Only the last payment can be edited.')]);
        }

        return view('event_booking_finance.edit_last_payment', compact('payment'));
    }

    public function updateLastPayment(Request $request, $paymentID)
    {
        $this->authorizeFinanceUpdate();

        $payment = $this->bookings->getPaymentWithBooking($paymentID);
        if (! $payment) {
            abort(404);
        }

        if (! $this->bookings->isLastPayment($payment->SeasonEventParticipantFinanceID, $paymentID)) {
            return redirect()->route('eventBookingFinance.index', $payment->SeasonEventID)
                ->withErrors(['general' => __('Only the last payment can be edited.')]);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $newAmount = (float) $request->amount;
        $bookingID = (int) $payment->SeasonEventParticipantFinanceID;
        $booking = $this->bookings->getBookingDetails($bookingID);
        if (! $booking) {
            abort(404);
        }

        $isSpecialBehavior = $this->shouldBypassLastInstallmentCompletion(
            (int) $booking->PersonID,
            $booking->SpecialCaseType
        );

        $otherPaymentsTotal = (float) DB::table('SeasonEventParticipantFinancePayment')
            ->where('SeasonEventParticipantFinanceID', $bookingID)
            ->where('PaymentType', 'PAYMENT')
            ->where('PaymentID', '<>', $paymentID)
            ->sum('Amount');

        $paymentsCount = $this->bookings->countPayments($bookingID);
        $isLastInstallment = ((int) $payment->InstallmentNumber === (int) $payment->InstallmentsNumber);

        $maxAllowed = max(0, (float) $payment->FinalRequiredAmount - $otherPaymentsTotal);

        if ($newAmount > $maxAllowed) {
            return redirect()->back()->withErrors([
                'amount' => __('The new amount exceeds the allowed remaining balance.'),
            ])->withInput();
        }

        if (! $isSpecialBehavior && $isLastInstallment && abs($newAmount - $maxAllowed) > 0.009) {
            return redirect()->back()->withErrors([
                'amount' => __('Because this is the last payment, it must equal the full remaining balance.'),
            ])->withInput();
        }

        try {
            $this->payments->updateLastPaymentAmount(
                (int) $paymentID,
                $bookingID,
                $newAmount,
                (float) $payment->FinalRequiredAmount,
                $otherPaymentsTotal,
                $payment->Notes,
            );

            return $this->redirectToPrintReceipt(
                (int) $paymentID,
                __('Last payment amount updated successfully.')
            );
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'general' => __('An error occurred while updating the last payment.'),
            ])->withInput();
        }
    }

    public function refundPage($bookingID)
    {
        $booking = $this->bookings->getBookingDetails($bookingID);
        if (! $booking) {
            abort(404);
        }

        return view('event_booking_finance.refund', compact('booking'));
    }

    public function refundStore(Request $request, $bookingID)
    {
        $this->authorizeFinanceUpdate();

        $booking = $this->bookings->getBookingDetails($bookingID);
        if (! $booking) {
            abort(404);
        }

        if ((int) $booking->IsRefunded === 1) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => __('This booking has already been refunded.')]);
        }

        if ((float) $booking->AmountPaid <= 0) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => __('No paid amount to refund.')]);
        }

        try {
            $paymentID = $this->payments->refundFull(
                (int) $bookingID,
                $booking,
                (int) Auth::user()->PersonID,
                $this->bookings->countPayments($bookingID) + 1,
            );

            return $this->redirectToPrintReceipt(
                (int) $paymentID,
                __('Full paid amount refunded successfully.')
            );
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'general' => __('An error occurred during refund.'),
            ]);
        }
    }

    public function printReceipt($paymentID, AttendanceQrService $qr)
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
                'b.SeasonEventID',
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
                'et.TakesReservation',
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

        if (! $receipt) {
            abort(404);
        }

        $qrPng = null;
        $qrPayload = null;
        $entity = $qr->entityFromBooking($receipt);
        if (
            $entity
            && $receipt->PaymentType !== 'REFUND'
            && ! empty($receipt->TakesReservation)
        ) {
            $qrPayload = $qr->payloadForEntity($entity['type'], $entity['id']);
        }

        $safePersonName = preg_replace('/[^A-Za-z0-9\-]+/', '-', $receipt->PersonFullName);
        $safePersonName = trim($safePersonName, '-');
        $fileName = 'Receipt-'.$receipt->ReceiptNumber.'-'.$safePersonName.'-'.date('Y').'.pdf';

        return $this->noStoreView('event_booking_finance.print_receipt', compact('receipt', 'fileName', 'qrPng', 'qrPayload'));
    }

    private function noStoreView(string $view, array $data): Response
    {
        return response()
            ->view($view, $data)
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache');
    }

    private function redirectToPrintReceipt(int $paymentId, string $message)
    {
        return redirect()
            ->route('eventBookingFinance.printReceipt', [
                'paymentID' => $paymentId,
                'return' => 1,
            ])
            ->with('success', $message);
    }

    private function getSeasonEventFullInfo($seasonEventID)
    {
        return $this->bookings->getEventInfo((int) $seasonEventID);
    }

    public function partialRefundPage($bookingID)
    {
        $booking = $this->bookings->getBookingDetails($bookingID);
        if (! $booking) {
            abort(404);
        }

        if ((int) $booking->IsRefunded === 1) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => __('This booking has already been refunded.')]);
        }

        if ((float) $booking->AmountPaid <= 0) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => __('No paid amount to refund.')]);
        }

        return view('event_booking_finance.partial_refund', compact('booking'));
    }

    public function partialRefundStore(Request $request, $bookingID)
    {
        $this->authorizeFinanceUpdate();

        $booking = $this->bookings->getBookingDetails($bookingID);
        if (! $booking) {
            abort(404);
        }

        if ((int) $booking->IsRefunded === 1) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => __('This booking has already been refunded.')]);
        }

        if ((float) $booking->AmountPaid <= 0) {
            return redirect()->route('eventBookingFinance.index', $booking->SeasonEventID)
                ->withErrors(['general' => __('No paid amount to refund.')]);
        }

        $validator = Validator::make($request->all(), [
            'deduction_amount' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ], [
            'deduction_amount.required' => __('Deduction amount is required.'),
            'deduction_amount.integer' => __('Deduction amount must be a whole number without cents.'),
            'deduction_amount.min' => __('Deduction amount cannot be less than zero.'),
            'notes.max' => __('Notes must not exceed 500 characters.'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $amountPaid = (float) $booking->AmountPaid;
        $deductionAmount = (float) $request->deduction_amount;

        if ($deductionAmount > $amountPaid) {
            return redirect()->back()->withErrors([
                'deduction_amount' => __('Deduction amount must be less than the paid amount.'),
            ])->withInput();
        }

        try {
            $paymentID = $this->payments->refundPartial(
                (int) $bookingID,
                $booking,
                (int) Auth::user()->PersonID,
                $this->bookings->countPayments($bookingID) + 1,
                $deductionAmount,
                $request->filled('notes') ? $request->notes : null,
            );

            return $this->redirectToPrintReceipt(
                (int) $paymentID,
                __('Amount refunded successfully after partial deduction.')
            );
        } catch (Exception $e) {
            return redirect()->back()->withErrors([
                'general' => __('An error occurred while processing partial refund.'),
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
        if (! $event) {
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
            ? 'event-bookings-today-'.$seasonEventID.'.csv'
            : 'event-bookings-all-'.$seasonEventID.'.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

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
            ->leftJoin('SeasonEventFinance as sef', 'sef.SeasonEventID', '=', 'b.SeasonEventID')
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
                DB::raw('COALESCE(sef.SendQrWhatsApp, 0) as SendQrWhatsApp'),
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

        if (! $booking) {
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
                    ? Carbon::parse($payment->PaymentDate)->format('Y-m-d h:i A')
                    : '-';

                $payment->AmountFormatted = number_format((float) $payment->Amount, 2);

                return $payment;
            });

        $canAddInstallment =
            ((int) $booking->IsRefunded === 0
                && (float) $booking->RemainingAmount > 0
                && $this->bookings->countPayments($bookingID) < (int) $booking->InstallmentsNumber);

        $canRefund =
            ((int) $booking->IsRefunded === 0 && (float) $booking->AmountPaid > 0);

        return view('event_booking_finance.show', compact(
            'booking',
            'payments',
            'canAddInstallment',
            'canRefund'
        ));
    }

    public function sendQr($bookingID, AttendanceQrService $qr)
    {
        $this->authorizeFinanceUpdate();

        $booking = DB::table('SeasonEventParticipantFinance as b')
            ->join('SeasonEvent as se', 'b.SeasonEventID', '=', 'se.SeasonEventID')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->leftJoin('SeasonEventFinance as sef', 'sef.SeasonEventID', '=', 'b.SeasonEventID')
            ->where('b.SeasonEventParticipantFinanceID', $bookingID)
            ->select('b.*', 'e.EventName', 'et.TakesReservation', 'sef.SendQrWhatsApp')
            ->first();

        if (! $booking) {
            abort(404);
        }

        if ((int) $booking->IsRefunded === 1) {
            return back()->with('error', __('No active booking found for this QR code.'));
        }

        if (empty($booking->TakesReservation) || empty($booking->SendQrWhatsApp)) {
            return back()->with('error', __('This event type does not take reservation QR codes.'));
        }

        if (! $qr->shouldSendViaWhatsApp()) {
            return back()->with('error', __('WhatsApp QR sending is temporarily disabled.'));
        }

        $entity = $qr->entityFromBooking($booking);
        if (! $entity) {
            return back()->with('error', __('Person not found.'));
        }

        try {
            $qr->sendEntityQrViaWhatsApp($entity['type'], $entity['id'], (string) $booking->EventName);
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('QR code sent via WhatsApp.'));
    }

    public function updateShirtSize(Request $request, $bookingID)
    {
        $this->authorizeFinanceUpdate();

        $validator = Validator::make($request->all(), [
            'shirt_size' => 'required|in:XS,S,M,L,XL,2XL,3XL,4XL,5XL,6XL',
        ], [
            'shirt_size.required' => __('Shirt size is required.'),
            'shirt_size.in' => __('Invalid shirt size.'),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $booking = DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventParticipantFinanceID', $bookingID)
            ->first();

        if (! $booking) {
            abort(404);
        }

        DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventParticipantFinanceID', $bookingID)
            ->update([
                'ShirtSize' => $request->shirt_size,
            ]);

        return redirect()->route('eventBookingFinance.show', $bookingID)
            ->with('success', __('Shirt size updated successfully.'));
    }

    public function deletePage($bookingID)
    {
        $this->authorizeFinanceDelete();

        $booking = $this->bookings->getBookingDetails((int) $bookingID);
        if (! $booking) {
            abort(404);
        }

        $paymentsCount = DB::table('SeasonEventParticipantFinancePayment')
            ->where('SeasonEventParticipantFinanceID', $bookingID)
            ->count();

        $receiptsCount = DB::table('SeasonEventParticipantFinanceReceipt as r')
            ->join('SeasonEventParticipantFinancePayment as p', 'r.PaymentID', '=', 'p.PaymentID')
            ->where('p.SeasonEventParticipantFinanceID', $bookingID)
            ->count();

        return view('event_booking_finance.delete', compact('booking', 'paymentsCount', 'receiptsCount'));
    }

    public function destroy($bookingID)
    {
        $this->authorizeFinanceDelete();

        $booking = $this->bookings->deleteBooking((int) $bookingID);
        if (! $booking) {
            abort(404);
        }

        return redirect()
            ->route('eventBookingFinance.index', $booking->SeasonEventID)
            ->with('success', __('Booking deleted successfully. All related payments and receipts were removed.'));
    }
}
