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

    public function index($seasonEventID)
    {
        $event = $this->getSeasonEventFullInfo($seasonEventID);
        if (!$event) {
            abort(404);
        }

        $bookings = DB::table('SeasonEventParticipantFinance as b')
            ->join('PersonInformation as p', 'b.PersonID', '=', 'p.PersonID')
            ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
            ->leftJoin('PersonInformation as s', 'b.ServentID', '=', 's.PersonID')
            ->leftJoin('PersonQetaa as pq', 'p.PersonID', '=', 'pq.PersonID')
            ->leftJoin('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
            ->where('b.SeasonEventID', $seasonEventID)
            ->select(
                'b.SeasonEventParticipantFinanceID',
                'b.PersonID',
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
                'ppn.PersonPersonalMobileNumber',
                DB::raw("TRIM(CONCAT(
                    COALESCE(p.FirstName,''), ' ',
                    COALESCE(p.SecondName,''), ' ',
                    COALESCE(p.ThirdName,''), ' ',
                    COALESCE(p.FourthName,'')
                )) as PersonFullName"),
                DB::raw("TRIM(CONCAT(
                    COALESCE(s.FirstName,''), ' ',
                    COALESCE(s.SecondName,''), ' ',
                    COALESCE(s.ThirdName,''), ' ',
                    COALESCE(s.FourthName,'')
                )) as ServentFullName"),
                DB::raw("GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ' , ') as QetaaNames"),
                DB::raw("(SELECT COUNT(*) FROM SeasonEventParticipantFinancePayment p2
                          WHERE p2.SeasonEventParticipantFinanceID = b.SeasonEventParticipantFinanceID
                          AND p2.PaymentType = 'PAYMENT') as PaymentsCount"),
                DB::raw("(SELECT MAX(p3.PaymentDate) FROM SeasonEventParticipantFinancePayment p3
                          WHERE p3.SeasonEventParticipantFinanceID = b.SeasonEventParticipantFinanceID) as LastPaymentDate"),
                DB::raw("(SELECT p4.PaymentID FROM SeasonEventParticipantFinancePayment p4
                          WHERE p4.SeasonEventParticipantFinanceID = b.SeasonEventParticipantFinanceID
                          ORDER BY p4.PaymentDate DESC, p4.PaymentID DESC
                          LIMIT 1) as LastPaymentID")
            )
            ->groupBy(
                'b.SeasonEventParticipantFinanceID',
                'b.PersonID',
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
                'ppn.PersonPersonalMobileNumber',
                'p.FirstName',
                'p.SecondName',
                'p.ThirdName',
                'p.FourthName',
                's.FirstName',
                's.SecondName',
                's.ThirdName',
                's.FourthName'
            )
            ->orderBy('PersonFullName')
            ->get();

        return view('event_booking_finance.index', compact('event', 'bookings'));
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

        return view('event_booking_finance.create', compact('event', 'plan'));
    }

    public function searchEligiblePersons(Request $request, $seasonEventID)
    {
        $query = trim((string)$request->query('q', ''));

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
            ->where(function ($sub) use ($query) {
                if ($query !== '') {
                    $sub->where(DB::raw("CONCAT_WS(' ', p.FirstName, p.SecondName, p.ThirdName, p.FourthName)"), 'like', '%' . $query . '%')
                        ->orWhere('p.PersonID', 'like', '%' . $query . '%')
                        ->orWhere('ppn.PersonPersonalMobileNumber', 'like', '%' . $query . '%');
                }
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
        'person_id' => 'required|integer|exists:PersonInformation,PersonID',
        'first_payment_date' => 'required|date',
        'first_payment_amount' => 'required|numeric|min:0.01',
        'is_not_able_to_pay_all' => 'nullable|in:0,1',
        'special_case_type' => 'nullable|in:NONE,AKHOH_RAB,HAS_BROTHERS,OTHER',
        'discount_amount' => 'nullable|numeric|min:0',
        'special_case_note' => 'nullable|string|max:500',
    ], [
        'person_id.required' => 'يجب اختيار الشخص.',
        'person_id.exists' => 'الشخص المختار غير موجود.',
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

    $personID = (int) $request->person_id;
    $serventID = (int) Auth::user()->PersonID;

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

    $alreadyBooked = DB::table('SeasonEventParticipantFinance')
        ->where('SeasonEventID', $seasonEventID)
        ->where('PersonID', $personID)
        ->exists();

    if ($alreadyBooked) {
        return redirect()->back()->withErrors([
            'person_id' => 'هذا الشخص محجوز بالفعل في هذه الفعالية.'
        ])->withInput();
    }

    $paymentDate = Carbon::parse($request->first_payment_date)->format('Y-m-d');

    $priceRow = DB::table('SeasonEventFinancePrice')
        ->where('SeasonEventID', $seasonEventID)
        ->where('StartDate', '<=', $paymentDate)
        ->where('EndDate', '>=', $paymentDate)
        ->orderBy('StartDate')
        ->first();

    if (!$priceRow) {
        return redirect()->back()->withErrors([
            'first_payment_date' => 'لا يوجد سعر صالح في هذا التاريخ.'
        ])->withInput();
    }

    $isPermanentSpecial = $this->isSpecialCase($personID);

    $specialCaseType = $request->filled('is_not_able_to_pay_all')
        ? ($request->special_case_type ?? 'NONE')
        : 'NONE';

    $discountAmount = (float) ($request->discount_amount ?? 0);
    $specialCaseNote = $request->special_case_note;

    $originalPrice = (float) $priceRow->Price;
    $finalRequiredAmount = max(0, $originalPrice - $discountAmount);
    $firstPaymentAmount = (float) $request->first_payment_amount;

    // عدد الأقساط الكلي للحجز = الحد الأقصى المسموح به في الخطة
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

    if (!$isSpecialBehavior) {
        if ((int) $plan->AllowBelowMinimumDeposit === 0 && $firstPaymentAmount < (float) $plan->MinimumDeposit) {
            return redirect()->back()->withErrors([
                'first_payment_amount' => 'لا يمكن أن تكون أول دفعة أقل من الحد الأدنى للمقدم.'
            ])->withInput();
        }
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
            'ServentID' => $serventID,
            'FirstPaymentDate' => $paymentDate,
            'OriginalPrice' => $originalPrice,
            'DiscountAmount' => $discountAmount,
            'FinalRequiredAmount' => $finalRequiredAmount,
            'LockedPrice' => $finalRequiredAmount,
            'InstallmentsNumber' => $installmentsNumber,
            'AmountPaid' => $firstPaymentAmount,
            'RemainingAmount' => max(0, $finalRequiredAmount - $firstPaymentAmount),
            'SpecialCaseType' => $specialCaseType,
            'SpecialCaseNote' => $specialCaseNote,
            'IsRefunded' => 0,
            'RefundDate' => null,
        ]);

        $paymentID = DB::table('SeasonEventParticipantFinancePayment')->insertGetId([
            'SeasonEventParticipantFinanceID' => $bookingID,
            'ServentID' => $serventID,
            'PaymentDate' => Carbon::parse($request->first_payment_date)->format('Y-m-d H:i:s'),
            'Amount' => $firstPaymentAmount,
            'InstallmentNumber' => 1,
            'PaymentType' => 'PAYMENT',
            'Notes' => 'أول دفعة',
        ]);

        if ($specialCaseType === 'AKHOH_RAB' && !$isPermanentSpecial) {
            DB::table('PersonSpecialCase')->insert([
                'PersonID' => $personID,
                'ServentID' => $serventID,
                'CaseDate' => now(),
                'Note' => $specialCaseNote ?: 'تمت إضافته كأخوه رب أثناء الحجز',
            ]);
        }

        $receiptID = DB::table('SeasonEventParticipantFinanceReceipt')->insertGetId([
            'PaymentID' => $paymentID,
            'ReceiptNumber' => 'TEMP',
            'IssuedAt' => now(),
            'IssuedByServentID' => $serventID,
        ]);

        $receiptNumber = 'REC-' . now()->format('i-H-d-m-y') . '-' . $receiptID;

        DB::table('SeasonEventParticipantFinanceReceipt')
            ->where('ReceiptID', $receiptID)
            ->update([
                'ReceiptNumber' => $receiptNumber
            ]);

        DB::commit();

        return redirect()->route('eventBookingFinance.printReceipt', $paymentID)
            ->with('success', 'تم إنشاء الحجز وإصدار الإيصال بنجاح.');
    } catch (Exception $e) {
        DB::rollBack();

        return redirect()->back()->withErrors([
            'general' => 'حدث خطأ أثناء إنشاء الحجز.'
        ])->withInput();
    }
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

    return view('event_booking_finance.create_installment', compact(
        'booking',
        'nextInstallmentNumber',
        'isLastInstallment',
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
        'amount' => 'required|numeric|min:0.01',
        'notes' => 'nullable|string|max:500'
    ], [
        'amount.required' => 'يجب إدخال مبلغ الدفعة.',
        'amount.min' => 'يجب أن يكون مبلغ الدفعة أكبر من صفر.'
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $remaining = (float)$booking->RemainingAmount;
    $amount = $isLastInstallment ? $remaining : (float)$request->amount;

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
            'Notes' => $isLastInstallment
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
            'amount' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $newAmount = (float)$request->amount;
        $bookingID = (int)$payment->SeasonEventParticipantFinanceID;

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

        if ($isLastInstallment && $newAmount != $maxAllowed) {
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
        ->join('PersonInformation as person', 'b.PersonID', '=', 'person.PersonID')
        ->leftJoin('PersonPhoneNumbers as ppn', 'person.PersonID', '=', 'ppn.PersonID')
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
            'ppn.PersonPersonalMobileNumber',
            DB::raw("TRIM(CONCAT(
                COALESCE(person.FirstName,''), ' ',
                COALESCE(person.SecondName,''), ' ',
                COALESCE(person.ThirdName,''), ' ',
                COALESCE(person.FourthName,'')
            )) as PersonFullName"),
            DB::raw("TRIM(CONCAT(
                COALESCE(servant.FirstName,''), ' ',
                COALESCE(servant.SecondName,''), ' ',
                COALESCE(servant.ThirdName,''), ' ',
                COALESCE(servant.FourthName,'')
            )) as ServentFullName")
        )
        ->first();

    if (!$receipt) {
        abort(404);
    }

    $safePersonName = preg_replace('/[^A-Za-z0-9]+/', '-', $receipt->PersonFullName);
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
            ->join('PersonInformation as p', 'b.PersonID', '=', 'p.PersonID')
            ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
            ->where('b.SeasonEventParticipantFinanceID', $bookingID)
            ->select(
                'b.*',
                DB::raw("TRIM(CONCAT(
                    COALESCE(p.FirstName,''), ' ',
                    COALESCE(p.SecondName,''), ' ',
                    COALESCE(p.ThirdName,''), ' ',
                    COALESCE(p.FourthName,'')
                )) as PersonFullName"),
                'ppn.PersonPersonalMobileNumber',
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
                'b.PersonID'
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
}