<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeasonEventFinanceController extends Controller
{
    /* =========================
       INDEX
       ========================= */
    public function index()
    {
        $data = DB::table('SeasonEventFinance as sef')
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
            ->get();

        return view('season-event-finance.index', compact('data'));
    }

    /* =========================
       CREATE FORM
       ========================= */
    public function create()
    {
        $seasons = DB::table('Season')->get();
        return view('season-event-finance.create', compact('seasons'));
    }

    /* =========================
       AJAX: GET EVENTS FOR SEASON
       ========================= */
    public function getEventsForSeason(Request $request)
    {
        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->where('se.SeasonID', $request->seasonID)
            ->select(
                'se.SeasonEventID',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate'
            )
            ->get();

        return response()->json($events);
    }

    /* =========================
       INSERT
       ========================= */
    public function insert(Request $request)
    {
        DB::table('SeasonEventFinance')->insert([
            'SeasonEventID' => $request->season_event_id,
            'SupportedPrice' => $request->supported_price,
            'ActualMaxPrice' => $request->actual_max_price,
            'InstallmentsNumber' => $request->installments_number
        ]);

        return redirect()
            ->route('seasonEventFinance.index')
            ->with('status', 'تم إضافة الإعدادات المالية بنجاح');
    }

    /* =========================
       EDIT FORM
       ========================= */
    public function edit($id)
    {
        $finance = DB::table('SeasonEventFinance')->where('SeasonEventID', $id)->first();

        $info = DB::table('SeasonEvent as se')
            ->join('Season as s', 's.SeasonID', '=', 'se.SeasonID')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->where('se.SeasonEventID', $id)
            ->select('s.SeasonName', 's.SeasonYear', 'e.EventName')
            ->first();

        return view('season-event-finance.edit', compact('finance', 'info'));
    }

    /* =========================
       UPDATE
       ========================= */
    public function update(Request $request, $id)
    {
        DB::table('SeasonEventFinance')
            ->where('SeasonEventID', $id)
            ->update([
                'SupportedPrice' => $request->supported_price,
                'ActualMaxPrice' => $request->actual_max_price,
                'InstallmentsNumber' => $request->installments_number
            ]);

        return redirect()
            ->route('seasonEventFinance.index')
            ->with('status', 'تم تعديل الإعدادات المالية بنجاح');
    }

    /* =========================
       DELETE CONFIRM
       ========================= */
    public function delete($id)
    {
        $info = DB::table('SeasonEventFinance as sef')
            ->join('SeasonEvent as se', 'se.SeasonEventID', '=', 'sef.SeasonEventID')
            ->join('Season as s', 's.SeasonID', '=', 'se.SeasonID')
            ->join('Event as e', 'e.EventID', '=', 'se.EventID')
            ->where('sef.SeasonEventID', $id)
            ->select('s.SeasonName', 's.SeasonYear', 'e.EventName')
            ->first();

        return view('season-event-finance.delete', compact('info', 'id'));
    }

    /* =========================
       DESTROY
       ========================= */
    public function destroy($id)
    {
        DB::table('SeasonEventFinance')->where('SeasonEventID', $id)->delete();

        return redirect()
            ->route('seasonEventFinance.index')
            ->with('status', 'تم حذف الإعدادات المالية بنجاح');
    }
}