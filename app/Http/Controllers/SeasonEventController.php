<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class SeasonEventController extends Controller
{
public function index()
{
    $seasonEvents = DB::select("
        SELECT 
            se.SeasonEventID,
            s.SeasonName AS SeasonName,
            s.SeasonYear,
            e.EventName,
            e.EventStartDate,
            e.EventEndDate,
            et.EventTypeName
        FROM SeasonEvent se
        INNER JOIN Season s ON se.SeasonID = s.SeasonID
        INNER JOIN Event e ON se.EventID = e.EventID
        INNER JOIN EventType et ON e.EventTypeID = et.EventTypeID
        ORDER BY s.SeasonYear DESC, s.SeasonName
    ");

    return view('season-event.index', ['seasonEvents' => $seasonEvents]);
}

    public function create()
    {
        $seasons = DB::table('Season')->get();
        $events = DB::table('Event')->get();
        return view("season-event.create", ['seasons' => $seasons, 'events' => $events]);
    }






public function insert(Request $request)
{
    $validator = Validator::make($request->all(), [
        'season_id'   => 'required|integer|exists:Season,SeasonID',
        'event_id'    => 'required|array|min:1',
        'event_id.*'  => 'integer|exists:Event,EventID',
    ], [
        'event_id.required' => 'يجب اختيار فعالية واحدة على الأقل',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $seasonId   = (int) $request->season_id;
    $eventIds   = collect($request->event_id)->map(fn($v) => (int) $v)->unique()->values();

    try {
        DB::beginTransaction();

        // Find events already linked to ANY season (global uniqueness)
        $existing = DB::table('SeasonEvent')
            ->whereIn('EventID', $eventIds)
            ->pluck('EventID')
            ->all();

        $conflicts = collect($existing);

        // Optional: fetch names to display in error message
        $conflictNames = [];
        if ($conflicts->isNotEmpty()) {
            $conflictNames = DB::table('Event')
                ->whereIn('EventID', $conflicts)
                ->pluck('EventName', 'EventID')
                ->toArray();
        }

        // Filter out conflicting EventIDs
        $insertableIds = $eventIds->diff($conflicts)->values();

        // If you want to FAIL ALL when there’s any conflict, uncomment:
        // if ($conflicts->isNotEmpty()) {
        //     DB::rollBack();
        //     return redirect()->back()
        //         ->withErrors(['event_id' => 'لا يمكن ربط هذه الفعاليات لأنها مرتبطة بالفعل بموسم آخر: ' .
        //             implode('، ', array_values($conflictNames))])
        //         ->withInput();
        // }

        // Insert only the allowed ones
        foreach ($insertableIds as $eventId) {
            DB::table('SeasonEvent')->insert([
                'SeasonID' => $seasonId,
                'EventID'  => $eventId,
            ]);
        }

        DB::commit();

    } catch (Exception $e) {
        DB::rollBack();
        return view('person.entry-error');
    }

    // Success + partial-conflict notice
    if (!empty($conflictNames)) {
        return redirect()->route('season-event.index')->with([
            'success'  => 'تم ربط الموسم بالفعاليات المسموح بها بنجاح.',
            'warning'  => 'تم تجاهل بعض الفعاليات لأنها مرتبطة بالفعل بمواسم أخرى: ' . implode('، ', array_values($conflictNames)),
        ]);
    }

    return redirect()->route('season-event.index')
        ->with('success', 'تم ربط الموسم بعدة فعاليات بنجاح!');
}




    # ====== EDIT ======
    public function edit($id)
    {
        $seasonEvent = DB::table('SeasonEvent')->where('SeasonEventID', $id)->first();
        if (!$seasonEvent) {
            abort(404);
        }

        $seasons = DB::table('Season')->get();
        $events = DB::table('Event')->get();

        return view("season-event.edit", [
            'seasonEvent' => $seasonEvent,
            'seasons' => $seasons,
            'events' => $events
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'season_id' => 'required|integer',
            'event_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            DB::table('SeasonEvent')->where('SeasonEventID', $id)->update([
                'SeasonID' => $request->season_id,
                'EventID' => $request->event_id
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return view('person.entry-error-repeat-trial');
        }

        return redirect()->route('season-event.index')->with('success', 'SeasonEvent link updated successfully!');
    }

    # ====== DELETE ======
    public function deletes($id)
    {
        $seasonEvent = DB::table('SeasonEvent')->where('SeasonEventID', $id)->first();
        if (!$seasonEvent) {
            abort(404);
        }

        $season = DB::table('Season')->where('SeasonID', $seasonEvent->SeasonID)->first();
        $event = DB::table('Event')->where('EventID', $seasonEvent->EventID)->first();

        return view("season-event.delete", [
            'seasonEvent' => $seasonEvent,
            'season' => $season,
            'event' => $event
        ]);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            DB::table('SeasonEvent')->where('SeasonEventID', $id)->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return view('person.entry-error-repeat-trial');
        }

        return redirect()->route('season-event.index')->with('success', 'SeasonEvent link deleted successfully!');
    }
}