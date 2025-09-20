<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Dompdf\Dompdf;

class InventoryIssueController extends Controller
{
    // Show all issues (index page)
 public function index()
    {
        $inventory = DB::table('Inventory')->get();
        $seasons = DB::table('Season')->orderBy('SeasonYear', 'desc')->get();
        $qetaat = DB::table('Qetaa')->get();

        return view('inventory-issue.index', compact('inventory', 'seasons', 'qetaat'));
    }



    // AJAX: Get Events for a selected Season
    public function getEventsForSeason(Request $request)
    {
        $seasonID = $request->query('seasonID');
        if (!$seasonID) return response()->json([]);

        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->where('se.SeasonID', $seasonID)
            ->select('se.SeasonEventID', 'e.EventID', 'e.EventName', 'e.EventStartDate', 'e.EventEndDate')
            ->get();

        return response()->json($events);
    }

    // Handle submission: generate Word or PDF



public function generate(Request $request)
{
    $request->validate([
        'season_id'   => 'required|integer',
        'event_id'    => 'required|integer',
        'items'       => 'required|array',
        'items.*.id'  => 'required|integer',
        'items.*.qty' => 'required|integer|min:1',
        'muslim'      => 'required|string',
        'mustalem'    => 'required|string',
    ]);

    $event = DB::table('Event')->where('EventID', $request->event_id)->first();
    $itemsData = [];

    foreach ($request->items as $item) {
        $dbItem = DB::table('Inventory')->where('InventoryID', $item['id'])->first();
        if ($dbItem) {
            $itemsData[] = [
                'name'  => $dbItem->ItemName,
                'qty'   => $item['qty'],
                'unit'  => $dbItem->ItemMeasuringUnit,
            ];
        }
    }

    $title = "عهده - " . $event->EventName;

    // Generate HTML content
    $html = view('inventory_issue.html_template', [
        'title'    => $title,
        'items'    => $itemsData,
        'muslim'   => $request->muslim,
        'mustalem' => $request->mustalem
    ])->render();

    $fileName = $title . '.html';

    return response($html)
        ->header('Content-Type', 'text/html')
        ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
}

}