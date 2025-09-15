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
            SELECT se.SeasonEventID, s.SeasonName AS SeasonName, s.SeasonYear, e.EventName, e.EventStartDate, e.EventEndDate
            FROM SeasonEvent se
            INNER JOIN Season s ON se.SeasonID = s.SeasonID
            INNER JOIN Event e ON se.EventID = e.EventID
            ORDER BY s.SeasonYear DESC, s.SeasonName;
        ");

        return view("season-event.index", ['seasonEvents' => $seasonEvents]);
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
            'season_id' => 'required|integer',
            'event_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            DB::table('SeasonEvent')->insert([
                'SeasonID' => $request->season_id,
                'EventID' => $request->event_id
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return view('person.entry-error');
        }

        return redirect()->route('season-event.index')->with('success', 'SeasonEvent link created successfully!');
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