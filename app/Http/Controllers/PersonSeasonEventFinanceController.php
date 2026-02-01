<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Session;

class PersonSeasonEventFinanceController extends Controller
{
    /* =========================
       LIST ALL ENROLLMENTS
       ========================= */
    public function index()
    {
        $records = DB::table('PersonSeasonEvent as pse')
            ->join('SeasonEvent as se', 'se.SeasonEventID', '=', 'pse.SeasonEventID')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->select(
                'pse.*',
                'e.EventName',
                DB::raw('(SELECT COALESCE(SUM(t.Amount),0)
                          FROM PersonSeasonEventTransaction t
                          WHERE t.PersonSeasonEventID = pse.PersonSeasonEventID) as Balance')
            )
            ->get();

        return view('finance.index', compact('records'));
    }

    /* =========================
       CREATE BOOKING FORM
       ========================= */
    public function create()
    {
        $seasonEvents = DB::table('SeasonEvent as se')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->select('se.SeasonEventID', 'e.EventName')
            ->get();

        return view('finance.create', compact('seasonEvents'));
    }

    /* =========================
       STORE BOOKING
       ========================= */
    public function insert(Request $request)
    {
        $finance = DB::table('SeasonEventFinance')
            ->where('SeasonEventID', $request->SeasonEventID)
            ->first();

        $targetPrice = $finance->SupportedPrice;

        if ($request->HasBrothers == 1) {
            $targetPrice = max(0, $targetPrice - $request->BrothersDiscountAmount);
        }

        DB::table('PersonSeasonEvent')->insert([
            'PersonID' => $request->PersonID,
            'SeasonEventID' => $request->SeasonEventID,
            'TargetPrice' => $targetPrice,
            'IsJesusSon' => $request->IsJesusSon ?? 0,
            'HasBrothers' => $request->HasBrothers ?? 0,
            'BrothersDiscountAmount' => $request->BrothersDiscountAmount ?? 0,
            'Notes' => $request->Notes,
            'CreatedAt' => now()
        ]);

        return redirect()->route('finance.index')
            ->with('status', 'تم تسجيل الشخص في الحدث بنجاح');
    }

    /* =========================
       SHOW PAYMENT HISTORY
       ========================= */
    public function show($id)
    {
        $enrollment = DB::table('PersonSeasonEvent')->where('PersonSeasonEventID', $id)->first();

        $transactions = DB::table('PersonSeasonEventTransaction')
            ->where('PersonSeasonEventID', $id)
            ->orderBy('TransactionDate')
            ->get();

        $balance = DB::table('PersonSeasonEventTransaction')
            ->where('PersonSeasonEventID', $id)
            ->sum('Amount');

        return view('finance.show', compact('enrollment', 'transactions', 'balance'));
    }

    /* =========================
       ADD PAYMENT
       ========================= */
    public function addPayment(Request $request, $id)
    {
        DB::table('PersonSeasonEventTransaction')->insert([
            'PersonSeasonEventID' => $id,
            'Amount' => $request->Amount, // positive
            'TransactionType' => 'payment',
            'Notes' => $request->Notes,
            'TransactionDate' => now()
        ]);

        return redirect()->back()->with('status', 'تم تسجيل الدفع بنجاح');
    }

    /* =========================
       CANCEL FORM
       ========================= */
    public function cancelForm($id)
    {
        $enrollment = DB::table('PersonSeasonEvent')->where('PersonSeasonEventID', $id)->first();

        $paid = DB::table('PersonSeasonEventTransaction')
            ->where('PersonSeasonEventID', $id)
            ->sum('Amount');

        return view('finance.cancel', compact('enrollment', 'paid'));
    }

    /* =========================
       CANCEL + ADMIN REFUND
       ========================= */
    public function cancel(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // update status
            DB::table('PersonSeasonEvent')
                ->where('PersonSeasonEventID', $id)
                ->update([
                    'Status' => 'cancelled',
                    'CancelReason' => $request->CancelReason,
                    'CancelledAt' => now()
                ]);

            // refund if any
            if ($request->RefundAmount > 0) {
                DB::table('PersonSeasonEventTransaction')->insert([
                    'PersonSeasonEventID' => $id,
                    'Amount' => -1 * abs($request->RefundAmount),
                    'TransactionType' => 'refund',
                    'Notes' => $request->RefundNotes,
                    'TransactionDate' => now()
                ]);
            }

            DB::commit();

            return redirect()->route('finance.index')
                ->with('status', 'تم إلغاء الاشتراك وتنفيذ الإجراء المالي');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors('حدث خطأ أثناء الإلغاء');
        }
    }
}