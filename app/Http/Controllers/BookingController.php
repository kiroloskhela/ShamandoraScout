<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /* =========================
       STEP 1: SELECT EVENT (finance only)
       ========================= */
    public function create()
    {
        $events = DB::table('SeasonEventFinance as sef')
            ->join('SeasonEvent as se', 'se.SeasonEventID', '=', 'sef.SeasonEventID')
            ->join('Season as s', 's.SeasonID', '=', 'se.SeasonID')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->select(
                'sef.SeasonEventID',
                's.SeasonName',
                's.SeasonYear',
                'e.EventName',
                'sef.SupportedPrice',
                'sef.ActualMaxPrice',
                'sef.InstallmentsNumber'
            )
            ->orderBy('s.SeasonYear', 'desc')
            ->get();

        return view('booking.create', compact('events'));
    }

    /* =========================
       STEP 2: SHOW SPOTLIGHT SEARCH PAGE
       ========================= */
    public function choosePerson($seasonEventID)
    {
        $eventInfo = DB::table('SeasonEventFinance as sef')
            ->join('SeasonEvent as se', 'se.SeasonEventID', '=', 'sef.SeasonEventID')
            ->join('Season as s', 's.SeasonID', '=', 'se.SeasonID')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->where('sef.SeasonEventID', $seasonEventID)
            ->select(
                'sef.SeasonEventID',
                's.SeasonName',
                's.SeasonYear',
                'e.EventName',
                'sef.SupportedPrice',
                'sef.ActualMaxPrice',
                'sef.InstallmentsNumber'
            )
            ->first();

        return view('booking.choose_person', compact('seasonEventID', 'eventInfo'));
    }

    /* =========================
       AJAX: SPOTLIGHT SEARCH
       ========================= */
    public function searchPerson(Request $request)
    {
        $q = trim($request->q ?? '');

        $rows = DB::select("
            SELECT DISTINCT
                pi.PersonID,
                pi.ShamandoraCode,
                pi.FirstName,
                pi.SecondName,
                pi.ThirdName,
                pi.FourthName,
                q.QetaaName,
                sm.SanaMarhalaName,
                ppn.PersonPersonalMobileNumber
            FROM PersonInformation pi
            LEFT JOIN PersonEntryQuestions peq ON pi.PersonID = peq.PersonID
            LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
            LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
            LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
            LEFT JOIN PersonGroup PG ON PG.PersonID = pi.PersonID
            LEFT JOIN PersonImages IG on IG.PersonID = pi.PersonID
            JOIN GroupQetaa gq ON gq.QetaaID = q.QetaaID
            JOIN PersonGroup pg2 ON pg2.GroupID = gq.GroupID
            WHERE
                pi.ShamandoraCode LIKE ?
                OR ppn.PersonPersonalMobileNumber LIKE ?
                OR CONCAT_WS(' ', pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName) LIKE ?
            ORDER BY pi.ShamandoraCode ASC
            LIMIT 20
        ", [
            "%$q%", "%$q%", "%$q%"
        ]);

        // add FullName in response
        $data = array_map(function($r){
            $r->FullName = trim($r->FirstName.' '.$r->SecondName.' '.$r->ThirdName.' '.$r->FourthName);
            return $r;
        }, $rows);

        return response()->json($data);
    }

    /* =========================
    STEP 3: DETAILS PAGE
    ========================= */
public function details($seasonEventID, $personID)
{
    $eventInfo = DB::table('SeasonEventFinance as sef')
        ->join('SeasonEvent as se', 'se.SeasonEventID', '=', 'sef.SeasonEventID')
        ->join('Season as s', 's.SeasonID', '=', 'se.SeasonID')
        ->join('Event as e', 'e.EventID', '=', 'se.EventID')
        ->where('sef.SeasonEventID', $seasonEventID)
        ->select(
            'sef.SeasonEventID',
            'sef.SupportedPrice',
            'sef.ActualMaxPrice',
            'sef.InstallmentsNumber',
            's.SeasonName',
            's.SeasonYear',
            'e.EventName'
        )
        ->first();

    // person
    $person = DB::selectOne("
        SELECT DISTINCT
            pi.PersonID,
            pi.ShamandoraCode,
            pi.FirstName,
            pi.SecondName,
            pi.ThirdName,
            pi.FourthName,
            q.QetaaName,
            sm.SanaMarhalaName,
            ppn.PersonPersonalMobileNumber
        FROM PersonInformation pi
        LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
        LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
        LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
        LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
        LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
        WHERE pi.PersonID = ?
        LIMIT 1
    ", [$personID]);

    $person->FullName = trim($person->FirstName.' '.$person->SecondName.' '.$person->ThirdName.' '.$person->FourthName);

    // enrollment
    $enrollment = DB::table('PersonSeasonEvent')
        ->where('PersonID', $personID)
        ->where('SeasonEventID', $seasonEventID)
        ->first();

    $installments = [];
    $transactions = [];
    $balance = 0.0;

    $maxPayments = (int)($eventInfo->InstallmentsNumber ?? 1);
    if ($maxPayments < 1) $maxPayments = 1;

    $paymentsCount = 0;

    if ($enrollment) {
        $installments = DB::table('PersonSeasonEventInstallment')
            ->where('PersonSeasonEventID', $enrollment->PersonSeasonEventID)
            ->orderBy('InstallmentNo')
            ->get();

       $transactions = DB::select("
    SELECT
        t.*,
        CASE
            WHEN t.TransactionType = 'payment' THEN
                (t.Amount + COALESCE((
                    SELECT SUM(r.Amount)
                    FROM PersonSeasonEventTransaction r
                    WHERE r.ParentTransactionID = t.TransactionID
                      AND r.TransactionType = 'refund'
                      AND r.DeletedAt IS NULL
                ), 0))
            ELSE
                t.Amount
        END AS NetAmount
    FROM PersonSeasonEventTransaction t
    WHERE t.PersonSeasonEventID = ?
      AND t.DeletedAt IS NULL
      AND (
        -- ✅ hide fully refunded payments
        t.TransactionType <> 'payment'
        OR (
            (t.Amount + COALESCE((
                SELECT SUM(r.Amount)
                FROM PersonSeasonEventTransaction r
                WHERE r.ParentTransactionID = t.TransactionID
                  AND r.TransactionType = 'refund'
                  AND r.DeletedAt IS NULL
            ), 0)) > 0.009
        )
      )
    ORDER BY t.TransactionDate ASC
", [$enrollment->PersonSeasonEventID]);


        $balance = (float)DB::table('PersonSeasonEventTransaction')
            ->where('PersonSeasonEventID', $enrollment->PersonSeasonEventID)
            ->whereNull('DeletedAt')
            ->sum('Amount');

        // ✅ effective payments
        $paymentsCount = $this->effectivePaymentsCount($enrollment->PersonSeasonEventID);
    }

    return view('booking.details', compact(
        'eventInfo',
        'person',
        'enrollment',
        'installments',
        'transactions',
        'balance',
        'paymentsCount',
        'maxPayments'
    ));
}


    /* =========================
      STORE BOOKING + OPTIONAL PAYMENT, THEN INVOICE
    ========================= */
 public function store(Request $request)
{
    DB::beginTransaction();

    try {
        $seasonEventID = (int)$request->season_event_id;
        $personID      = (int)$request->person_id;

        $finance = DB::table('SeasonEventFinance')
            ->where('SeasonEventID', $seasonEventID)
            ->first();

        if (!$finance) {
            DB::rollBack();
            return redirect()->back()->withErrors('هذه الفعالية غير مفعلة مالياً');
        }

        // compute target price = supported - discount, and allow optional increase up to max
        $hasBrothers = (int)($request->has_brothers ?? 0);
        $discount    = (float)($request->brothers_discount_amount ?? 0);
        if ($hasBrothers != 1) $discount = 0;
        if ($discount < 0) $discount = 0;

        $baseTarget  = (float)$finance->SupportedPrice;
        $targetPrice = max(0, $baseTarget - $discount);

        // admin may increase target up to actual max (optional)
        if ($request->target_price !== null && $request->target_price !== '') {
            $requestedTarget = (float)$request->target_price;
            if ($requestedTarget < 0) $requestedTarget = 0;
            if ($requestedTarget > $targetPrice) {
                $targetPrice = min($requestedTarget, (float)$finance->ActualMaxPrice);
            }
        }

        $isJesusSon = (int)($request->is_jesus_son ?? 0);

        // find existing enrollment
        $enrollment = DB::table('PersonSeasonEvent')
            ->where('PersonID', $personID)
            ->where('SeasonEventID', $seasonEventID)
            ->first();

        // create or update enrollment
        if (!$enrollment) {
            $pseID = DB::table('PersonSeasonEvent')->insertGetId([
                'PersonID' => $personID,
                'SeasonEventID' => $seasonEventID,
                'TargetPrice' => $targetPrice,
                'IsJesusSon' => $isJesusSon,
                'HasBrothers' => $hasBrothers,
                'BrothersDiscountAmount' => $discount,
                'Notes' => null,
                'Status' => 'active',
                'CreatedAt' => now()
            ]);

            // create installments schedule once
            $n = (int)$finance->InstallmentsNumber;
            if ($n < 1) $n = 1;

            $each = round($targetPrice / $n, 2);
            $sum  = 0;

            for ($i = 1; $i <= $n; $i++) {
                $amount = ($i == $n) ? round($targetPrice - $sum, 2) : $each;
                $sum += $amount;

                DB::table('PersonSeasonEventInstallment')->insert([
                    'PersonSeasonEventID' => $pseID,
                    'InstallmentNo' => $i,
                    'DueDate' => now()->addMonths($i - 1)->toDateString(),
                    'AmountDue' => $amount,
                    'Status' => 'unpaid'
                ]);
            }
        } else {
            $pseID = (int)$enrollment->PersonSeasonEventID;

            DB::table('PersonSeasonEvent')
                ->where('PersonSeasonEventID', $pseID)
                ->update([
                    'TargetPrice' => $targetPrice,
                    'IsJesusSon' => $isJesusSon,
                    'HasBrothers' => $hasBrothers,
                    'BrothersDiscountAmount' => $discount
                ]);
        }

        // payment
        $payAmount = (float)($request->pay_amount ?? 0);
        if ($payAmount < 0) {
            DB::rollBack();
            return redirect()->back()->withErrors('المبلغ لا يمكن أن يكون سالب');
        }

        if ($payAmount > 0) {

            $pseRow = DB::table('PersonSeasonEvent')->where('PersonSeasonEventID', $pseID)->first();
            if (!$pseRow) {
                DB::rollBack();
                return redirect()->back()->withErrors('خطأ في بيانات الاشتراك');
            }

            if (($pseRow->Status ?? '') === 'cancelled') {
                DB::rollBack();
                return redirect()->back()->withErrors('لا يمكن إضافة دفعة لشخص تم إلغاء اشتراكه');
            }

            // balance (includes refunds as negative) excluding soft-deleted
            $balance = (float)DB::table('PersonSeasonEventTransaction')
                ->where('PersonSeasonEventID', $pseID)
                ->whereNull('DeletedAt')
                ->sum('Amount');

            $remaining = (float)$pseRow->TargetPrice - $balance;

            if ($remaining <= 0.009) {
                DB::rollBack();
                return redirect()->back()->withErrors('تم سداد كل الأقساط بالفعل ولا يمكن إضافة دفعة جديدة');
            }

            if ($payAmount > $remaining + 0.009) {
                DB::rollBack();
                return redirect()->back()->withErrors('مبلغ الدفع أكبر من المتبقي');
            }

            // ✅ NEW RULE: max payments = installments number (effective)
            $n = (int)$finance->InstallmentsNumber;
            if ($n < 1) $n = 1;

            $effectivePayments = $this->effectivePaymentsCount($pseID);

            if ($effectivePayments >= $n) {
                DB::rollBack();
                return redirect()->back()->withErrors("لا يمكن إضافة دفعات أكثر من عدد الأقساط ($n)");
            }

            // ✅ last allowed payment must close remaining
            if ($effectivePayments == ($n - 1)) {
                if (abs($payAmount - $remaining) > 0.01) {
                    DB::rollBack();
                    return redirect()->back()
                        ->withErrors("هذه آخر دفعة مسموحة ويجب أن تساوي المتبقي بالكامل: " . number_format($remaining, 2));
                }
            }

            // insert payment transaction
            $trxID = DB::table('PersonSeasonEventTransaction')->insertGetId([
                'PersonSeasonEventID' => $pseID,
                'ParentTransactionID' => null,
                'Amount' => $payAmount,
                'TransactionType' => 'payment',
                'TransactionDate' => now(),
                'Notes' => $request->pay_notes,
                'DeletedAt' => null,
                'DeletedBy' => null
            ]);

            // auto-allocate payment to earliest unpaid installments
            $remainingPay = $payAmount;

            $insts = DB::select("
                SELECT
                    i.InstallmentID,
                    i.AmountDue,
                    COALESCE(SUM(pit.AppliedAmount),0) as Applied
                FROM PersonSeasonEventInstallment i
                LEFT JOIN PersonInstallmentTransaction pit ON pit.InstallmentID = i.InstallmentID
                WHERE i.PersonSeasonEventID = ?
                GROUP BY i.InstallmentID
                HAVING (i.AmountDue - COALESCE(SUM(pit.AppliedAmount),0)) > 0
                ORDER BY i.InstallmentNo ASC
            ", [$pseID]);

            foreach ($insts as $ins) {
                if ($remainingPay <= 0) break;

                $dueLeft = round($ins->AmountDue - $ins->Applied, 2);
                $apply   = ($remainingPay >= $dueLeft) ? $dueLeft : $remainingPay;

                DB::table('PersonInstallmentTransaction')->insert([
                    'TransactionID' => $trxID,
                    'InstallmentID' => $ins->InstallmentID,
                    'AppliedAmount' => $apply
                ]);

                $remainingPay = round($remainingPay - $apply, 2);
            }

            // recalc statuses
            $this->recalcInstallmentStatuses($pseID);

            DB::commit();
            return redirect()->route('transactions.invoice', $trxID)
                ->with('status', 'تم حفظ الدفعة بنجاح');
        }

        DB::commit();
        return redirect()->route('booking.details', [$seasonEventID, $personID])
            ->with('status', 'تم حفظ بيانات الحجز بدون دفع');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->withErrors('حدث خطأ أثناء الحفظ');
    }
}



 

    /* =========================
       INVOICE (2 COPIES IN ONE PAGE)
       ========================= */
        public function invoice($personSeasonEventID)
        {
            $data = DB::selectOne("
                SELECT
                    pse.PersonSeasonEventID,
                    pse.PersonID,
                    pse.SeasonEventID,
                    pse.CreatedAt as BookingDate,
                    pse.TargetPrice,

                    pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName,
                    q.QetaaName,
                    sm.SanaMarhalaName,

                    s.SeasonName, s.SeasonYear,
                    e.EventName

                FROM PersonSeasonEvent pse
                JOIN SeasonEvent se ON se.SeasonEventID = pse.SeasonEventID
                JOIN Season s ON s.SeasonID = se.SeasonID
                JOIN Event e ON e.EventID = se.EventID

                JOIN PersonInformation pi ON pi.PersonID = pse.PersonID
                LEFT JOIN PersonQetaa pq ON pq.PersonID = pi.PersonID
                LEFT JOIN Qetaa q ON q.QetaaID = pq.QetaaID
                LEFT JOIN PersonSanaMarhala psm ON psm.PersonID = pi.PersonID
                LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID

                WHERE pse.PersonSeasonEventID = ?
                LIMIT 1
            ", [$personSeasonEventID]);

            $data->FullName = trim($data->FirstName.' '.$data->SecondName.' '.$data->ThirdName.' '.$data->FourthName);

            // latest payment transaction (THIS TIME)
            $lastPayment = DB::table('PersonSeasonEventTransaction')
                ->where('PersonSeasonEventID', $personSeasonEventID)
                ->where('TransactionType', 'payment')
                ->orderBy('TransactionDate', 'desc')
                ->first();

            // current installment being paid (latest allocation linked to latest payment)
            $lastInstallment = null;
            if ($lastPayment) {
                $lastInstallment = DB::selectOne("
                    SELECT i.InstallmentNo, i.InstallmentID
                    FROM PersonInstallmentTransaction pit
                    JOIN PersonSeasonEventInstallment i ON i.InstallmentID = pit.InstallmentID
                    WHERE pit.TransactionID = ?
                    ORDER BY i.InstallmentNo DESC
                    LIMIT 1
                ", [$lastPayment->TransactionID]);
            }

            return view('booking.invoice', compact('data', 'lastInstallment', 'lastPayment'));
        }


public function editTransaction($id)
{
    $trx = DB::table('PersonSeasonEventTransaction')
        ->where('TransactionID', $id)
        ->whereNull('DeletedAt')
        ->first();

    if (!$trx || $trx->TransactionType != 'payment') {
        return redirect()->back()->withErrors('هذه العملية غير موجودة أو لا يمكن تعديلها');
    }

    $enrollment = DB::table('PersonSeasonEvent')
        ->where('PersonSeasonEventID', $trx->PersonSeasonEventID)
        ->first();

    // balance without this transaction (only not deleted)
    $balanceAll = (float)DB::table('PersonSeasonEventTransaction')
        ->where('PersonSeasonEventID', $trx->PersonSeasonEventID)
        ->whereNull('DeletedAt')
        ->sum('Amount');

    $balanceWithoutThis = $balanceAll - (float)$trx->Amount;

    // remaining allowed for this transaction after excluding it
    $remaining = (float)$enrollment->TargetPrice - $balanceWithoutThis;

    return view('transactions.edit', compact('trx', 'enrollment', 'remaining'));
}

public function updateTransaction(Request $request, $id)
{
    DB::beginTransaction();

    try {
        // 1) load transaction (payment only) and not deleted
        $trx = DB::table('PersonSeasonEventTransaction')
            ->where('TransactionID', $id)
            ->whereNull('DeletedAt')
            ->first();

        if (!$trx || $trx->TransactionType != 'payment') {
            DB::rollBack();
            return redirect()->back()->withErrors('هذه العملية غير موجودة أو لا يمكن تعديلها');
        }

        // 2) load enrollment
        $pse = DB::table('PersonSeasonEvent')
            ->where('PersonSeasonEventID', $trx->PersonSeasonEventID)
            ->first();

        if (!$pse) {
            DB::rollBack();
            return redirect()->back()->withErrors('الاشتراك غير موجود');
        }

        if ($pse->Status == 'cancelled') {
            DB::rollBack();
            return redirect()->back()->withErrors('لا يمكن تعديل دفعات بعد الإلغاء');
        }

        // 3) validate amount
        $newAmount = (float)$request->amount;
        if ($newAmount <= 0) {
            DB::rollBack();
            return redirect()->back()->withErrors('المبلغ يجب أن يكون أكبر من صفر');
        }

        // 4) compute remaining allowed excluding this transaction
        $balanceAll = (float)DB::table('PersonSeasonEventTransaction')
            ->where('PersonSeasonEventID', $trx->PersonSeasonEventID)
            ->whereNull('DeletedAt')
            ->sum('Amount');

        $balanceWithoutThis = $balanceAll - (float)$trx->Amount;
        $remainingAllowed = (float)$pse->TargetPrice - $balanceWithoutThis;

        if ($newAmount > $remainingAllowed) {
            DB::rollBack();
            return redirect()->back()->withErrors('المبلغ أكبر من المتبقي (بعد استبعاد هذه الدفعة)');
        }

        // 5) audit log (before update)
        DB::table('FinanceAuditLog')->insert([
            'ActionType' => 'update_transaction',
            'TransactionID' => $trx->TransactionID,
            'PersonSeasonEventID' => $trx->PersonSeasonEventID,
            'AmountOld' => $trx->Amount,
            'AmountNew' => $newAmount,
            'NotesOld' => $trx->Notes,
            'NotesNew' => $request->notes,
            'DoneBy' => Auth::id(),
            'Extra' => null
        ]);

        // 6) update transaction amount/notes
        DB::table('PersonSeasonEventTransaction')
            ->where('TransactionID', $id)
            ->update([
                'Amount' => $newAmount,
                'Notes' => $request->notes
            ]);

        // 7) delete old allocations for this transaction
        DB::table('PersonInstallmentTransaction')
            ->where('TransactionID', $id)
            ->delete();

        // 8) re-allocate new amount to earliest unpaid/partial installments
        $remainingPay = $newAmount;

        $insts = DB::select("
            SELECT
                i.InstallmentID,
                i.AmountDue,
                COALESCE(SUM(pit.AppliedAmount),0) as Applied
            FROM PersonSeasonEventInstallment i
            LEFT JOIN PersonInstallmentTransaction pit ON pit.InstallmentID = i.InstallmentID
            WHERE i.PersonSeasonEventID = ?
            GROUP BY i.InstallmentID
            HAVING (i.AmountDue - COALESCE(SUM(pit.AppliedAmount),0)) > 0
            ORDER BY i.InstallmentNo ASC
        ", [$trx->PersonSeasonEventID]);

        foreach ($insts as $ins) {
            if ($remainingPay <= 0) break;

            $dueLeft = round($ins->AmountDue - $ins->Applied, 2);
            if ($dueLeft <= 0) continue;

            $apply = ($remainingPay >= $dueLeft) ? $dueLeft : $remainingPay;

            DB::table('PersonInstallmentTransaction')->insert([
                'TransactionID' => $id,
                'InstallmentID' => $ins->InstallmentID,
                'AppliedAmount' => $apply
            ]);

            $remainingPay = round($remainingPay - $apply, 2);
        }

        // ✅ Guard: must be fully allocated (no "4th installment" behavior)
        if ($remainingPay > 0.009) {
            DB::rollBack();
            return redirect()->back()->withErrors('خطأ: لا توجد أقساط كافية لتوزيع هذا المبلغ بعد التعديل');
        }

        // 9) recalc installment statuses
        $this->recalcInstallmentsStatus($trx->PersonSeasonEventID);

        DB::commit();
        return redirect()->back()->with('status', 'تم تعديل الدفعة بنجاح');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->withErrors('حدث خطأ أثناء تعديل الدفعة');
    }
}



public function deleteTransaction(Request $request, $id)
{
    $trx = DB::table('PersonSeasonEventTransaction')
        ->where('TransactionID', $id)
        ->whereNull('DeletedAt')
        ->first();

    if (!$trx || $trx->TransactionType != 'payment') {
        return redirect()->route('booking.create')
            ->withErrors('هذه العملية غير موجودة أو لا يمكن حذفها');
    }

    $returnUrl = $request->query('return'); // 👈 important

    return view('transactions.delete', compact('trx', 'returnUrl'));
}


public function destroyTransaction(Request $request, $id)
{
    DB::beginTransaction();

    try {
        $trx = DB::table('PersonSeasonEventTransaction')
            ->where('TransactionID', $id)
            ->whereNull('DeletedAt')
            ->first();

        if (!$trx || $trx->TransactionType != 'payment') {
            DB::rollBack();
            return redirect()->route('booking.create')->withErrors('هذه العملية غير موجودة أو لا يمكن حذفها');
        }

        // Audit log for payment delete
        DB::table('FinanceAuditLog')->insert([
            'ActionType' => 'delete_transaction',
            'TransactionID' => $trx->TransactionID,
            'PersonSeasonEventID' => $trx->PersonSeasonEventID,
            'AmountOld' => $trx->Amount,
            'AmountNew' => null,
            'NotesOld' => $trx->Notes,
            'NotesNew' => null,
            'DoneBy' => Auth::id(),
            'Extra' => 'Soft delete payment + linked refunds'
        ]);

        // 1) soft delete refunds linked to this payment + delete their allocations
        $refunds = DB::table('PersonSeasonEventTransaction')
            ->where('ParentTransactionID', $trx->TransactionID)
            ->whereNull('DeletedAt')
            ->get();

        foreach ($refunds as $r) {
            DB::table('PersonInstallmentTransaction')
                ->where('TransactionID', $r->TransactionID)
                ->delete();

            DB::table('PersonSeasonEventTransaction')
                ->where('TransactionID', $r->TransactionID)
                ->update([
                    'DeletedAt' => now(),
                    'DeletedBy' => Auth::id()
                ]);
        }

        // 2) delete allocations of payment
        DB::table('PersonInstallmentTransaction')
            ->where('TransactionID', $trx->TransactionID)
            ->delete();

        // 3) soft delete payment
        DB::table('PersonSeasonEventTransaction')
            ->where('TransactionID', $trx->TransactionID)
            ->update([
                'DeletedAt' => now(),
                'DeletedBy' => Auth::id()
            ]);

        // 4) recalc installment statuses
        $this->recalcInstallmentStatuses($trx->PersonSeasonEventID);

        DB::commit();

        // return to the page user came from
        if ($request->filled('return')) {
            return redirect($request->return)->with('status', 'تم حذف الدفعة (Soft Delete) بنجاح');
        }

        return redirect()->route('booking.create')->with('status', 'تم حذف الدفعة (Soft Delete) بنجاح');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('booking.create')->withErrors('حدث خطأ أثناء الحذف');
    }
}





public function invoiceByTransaction($transactionID)
{
    $trx = DB::table('PersonSeasonEventTransaction')
        ->where('TransactionID', $transactionID)
        ->whereNull('DeletedAt')
        ->first();

    if (!$trx || $trx->TransactionType != 'payment') {
        return redirect()->back()->withErrors('لا يمكن طباعة فاتورة لهذه العملية');
    }

    $data = DB::selectOne("
        SELECT
            pse.PersonSeasonEventID,
            pse.PersonID,
            pse.SeasonEventID,
            pse.CreatedAt as BookingDate,

            pi.FirstName, pi.SecondName, pi.ThirdName, pi.FourthName,
            q.QetaaName,
            sm.SanaMarhalaName,

            s.SeasonName, s.SeasonYear,
            e.EventName

        FROM PersonSeasonEvent pse
        JOIN SeasonEvent se ON se.SeasonEventID = pse.SeasonEventID
        JOIN Season s ON s.SeasonID = se.SeasonID
        JOIN Event e ON e.EventID = se.EventID

        JOIN PersonInformation pi ON pi.PersonID = pse.PersonID
        LEFT JOIN PersonQetaa pq ON pq.PersonID = pi.PersonID
        LEFT JOIN Qetaa q ON q.QetaaID = pq.QetaaID
        LEFT JOIN PersonSanaMarhala psm ON psm.PersonID = pi.PersonID
        LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID

        WHERE pse.PersonSeasonEventID = ?
        LIMIT 1
    ", [$trx->PersonSeasonEventID]);

    $data->FullName = trim($data->FirstName.' '.$data->SecondName.' '.$data->ThirdName.' '.$data->FourthName);

    $lastInstallment = DB::selectOne("
        SELECT i.InstallmentNo, i.InstallmentID
        FROM PersonInstallmentTransaction pit
        JOIN PersonSeasonEventInstallment i ON i.InstallmentID = pit.InstallmentID
        WHERE pit.TransactionID = ?
        ORDER BY i.InstallmentNo DESC
        LIMIT 1
    ", [$transactionID]);

    $lastPayment = $trx;

    return view('booking.invoice', compact('data', 'lastInstallment', 'lastPayment'));
}



public function refundForm(Request $request, $transactionID)
{
    $trx = DB::table('PersonSeasonEventTransaction')
        ->where('TransactionID', $transactionID)
        ->whereNull('DeletedAt')
        ->first();

    if (!$trx || $trx->TransactionType != 'payment') {
        return redirect()->back()->withErrors('المرتجع متاح فقط لعمليات الدفع');
    }

    // max refundable = payment - refunded already
    $alreadyRefunded = (float) DB::table('PersonSeasonEventTransaction')
        ->where('ParentTransactionID', $trx->TransactionID)
        ->whereNull('DeletedAt')
        ->where('TransactionType', 'refund')
        ->sum('Amount'); // negative

    $maxRefund = (float)$trx->Amount - abs($alreadyRefunded);

    $returnUrl = $request->query('return', url()->previous());

    return view('transactions.refund', compact('trx', 'maxRefund', 'returnUrl'));
}


public function refundStore(Request $request, $transactionID)
{
    DB::beginTransaction();

    try {
        $trx = DB::table('PersonSeasonEventTransaction')
            ->where('TransactionID', $transactionID)
            ->whereNull('DeletedAt')
            ->first();

        if (!$trx || $trx->TransactionType != 'payment') {
            DB::rollBack();
            return redirect()->back()->withErrors('المرتجع متاح فقط لعمليات الدفع');
        }

        // ✅ safety: payment amount must be positive
        if ((float)$trx->Amount <= 0) {
            DB::rollBack();
            return redirect()->back()->withErrors('لا يمكن عمل مرتجع لدفعة غير صحيحة');
        }

        $refundAmount = (float) $request->refund_amount;
        if ($refundAmount <= 0) {
            DB::rollBack();
            return redirect()->back()->withErrors('مبلغ المرتجع يجب أن يكون أكبر من صفر');
        }

        // ✅ Max refundable = payment amount - already refunded (linked to this payment)
        $alreadyRefunded = (float) DB::table('PersonSeasonEventTransaction')
            ->where('ParentTransactionID', $trx->TransactionID)
            ->whereNull('DeletedAt')
            ->where('TransactionType', 'refund')
            ->sum('Amount'); // negative

        $alreadyRefundedAbs = abs($alreadyRefunded);
        $maxRefund = (float)$trx->Amount - $alreadyRefundedAbs;

        if ($maxRefund <= 0.009) {
            DB::rollBack();
            return redirect()->back()->withErrors('تم عمل مرتجع كامل لهذه الدفعة بالفعل');
        }

        if ($refundAmount > $maxRefund + 0.009) {
            DB::rollBack();
            return redirect()->back()->withErrors('مبلغ المرتجع أكبر من المتاح للاسترجاع من هذه الدفعة');
        }

        // ✅ create refund transaction
        $refundID = DB::table('PersonSeasonEventTransaction')->insertGetId([
            'PersonSeasonEventID' => $trx->PersonSeasonEventID,
            'ParentTransactionID' => $trx->TransactionID, // ✅ link
            'Amount' => -1 * abs($refundAmount),
            'TransactionType' => 'refund',
            'TransactionDate' => now(),
            'Notes' => $request->notes,
            'DeletedAt' => null,
            'DeletedBy' => null,
        ]);

        // ✅ audit
        DB::table('FinanceAuditLog')->insert([
            'ActionType' => 'refund_transaction',
            'TransactionID' => $refundID,
            'PersonSeasonEventID' => $trx->PersonSeasonEventID,
            'AmountOld' => null,
            'AmountNew' => -1 * abs($refundAmount),
            'NotesOld' => null,
            'NotesNew' => $request->notes,
            'DoneBy' => Auth::id(),
            'Extra' => 'Refund created from payment TransactionID=' . $trx->TransactionID
        ]);

        // ✅ IMPORTANT: refund must "un-allocate" from installments (reverse allocations)
        $remainingRefund = abs($refundAmount);

        // installments that currently have applied > 0, start from last installment backwards
        $insts = DB::select("
            SELECT
                i.InstallmentID,
                i.AmountDue,
                COALESCE(SUM(pit.AppliedAmount),0) as Applied
            FROM PersonSeasonEventInstallment i
            LEFT JOIN PersonInstallmentTransaction pit ON pit.InstallmentID = i.InstallmentID
            WHERE i.PersonSeasonEventID = ?
            GROUP BY i.InstallmentID
            HAVING COALESCE(SUM(pit.AppliedAmount),0) > 0
            ORDER BY i.InstallmentNo DESC
        ", [$trx->PersonSeasonEventID]);

        if (count($insts) == 0) {
            // no allocations exist; rollback to avoid refund breaking installments logic
            DB::rollBack();
            return redirect()->back()->withErrors('لا يمكن عمل مرتجع لأن الدفعة لم تُخصص على أقساط (Allocation غير موجود)');
        }

        foreach ($insts as $ins) {
            if ($remainingRefund <= 0) break;

            $applied = (float)$ins->Applied;
            $takeBack = ($remainingRefund >= $applied) ? $applied : $remainingRefund;

            DB::table('PersonInstallmentTransaction')->insert([
                'TransactionID' => $refundID,
                'InstallmentID' => $ins->InstallmentID,
                'AppliedAmount' => -1 * abs($takeBack)
            ]);

            $remainingRefund = round($remainingRefund - $takeBack, 2);
        }

        // ✅ recalc installment statuses using helper
        $this->recalcInstallmentStatuses($trx->PersonSeasonEventID);

        DB::commit();
       if ($request->filled('return')) {
            return redirect($request->return)->with('status', 'تم إنشاء المرتجع بنجاح');
        }
        return redirect()->back()->with('status', 'تم إنشاء المرتجع بنجاح');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->withErrors('حدث خطأ أثناء إنشاء المرتجع');
    }
}




private function effectivePaymentsCount($personSeasonEventID): int
{
    $row = DB::selectOne("
        SELECT COUNT(*) as cnt
        FROM PersonSeasonEventTransaction p
        WHERE p.PersonSeasonEventID = ?
          AND p.TransactionType = 'payment'
          AND p.DeletedAt IS NULL
          AND (
            p.Amount + COALESCE((
                SELECT SUM(r.Amount)
                FROM PersonSeasonEventTransaction r
                WHERE r.ParentTransactionID = p.TransactionID
                  AND r.TransactionType = 'refund'
                  AND r.DeletedAt IS NULL
            ), 0)
          ) > 0.009
    ", [$personSeasonEventID]);

    return (int)($row->cnt ?? 0);
}

private function recalcInstallmentStatuses($personSeasonEventID): void
{
    $insts = DB::table('PersonSeasonEventInstallment')
        ->where('PersonSeasonEventID', $personSeasonEventID)
        ->get();

    foreach ($insts as $i) {
        $applied = (float)DB::table('PersonInstallmentTransaction')
            ->where('InstallmentID', $i->InstallmentID)
            ->sum('AppliedAmount');

        $status = 'unpaid';
        if ($applied >= (float)$i->AmountDue) $status = 'paid';
        else if ($applied > 0) $status = 'partial';

        DB::table('PersonSeasonEventInstallment')
            ->where('InstallmentID', $i->InstallmentID)
            ->update(['Status' => $status]);
    }
}



}