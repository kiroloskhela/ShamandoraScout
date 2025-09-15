<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class MediaController extends Controller
{
    // Show all Media links
    public function index()
    {
        $media = DB::table('Media')
            ->join('SeasonEvent', 'Media.SeasonEventID', '=', 'SeasonEvent.SeasonEventID')
            ->join('Season', 'SeasonEvent.SeasonID', '=', 'Season.SeasonID')
            ->join('Event', 'SeasonEvent.EventID', '=', 'Event.EventID')
            ->select('Media.MediaID', 'Media.DriveLink', 'Season.SeasonName', 'Season.SeasonYear', 'Event.EventName')
            ->get();

        return view('media.index', compact('media'));
    }

    // Show form to add new Media
    public function create()
    {
        $seasons = DB::table('Season')->orderBy('SeasonYear', 'desc')->get();
        return view('media.create', compact('seasons'));
    }

    // AJAX: Get events for a selected season
    public function getEventsForSeason(Request $request)
    {
        // if using query param: ?seasonID=1
        $seasonID = $request->query('seasonID');

        if (!$seasonID) {
            return response()->json([]);
        }

        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->where('se.SeasonID', $seasonID)
            ->select('se.SeasonEventID', 'e.EventName', 'e.EventStartDate', 'e.EventEndDate')
            ->get();

        return response()->json($events);
    }


    // Insert new Media
    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'season_event_id' => 'required|integer',
            'drive_link' => 'required|url|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::table('Media')->insert([
            'SeasonEventID' => $request->season_event_id,
            'DriveLink' => $request->drive_link
        ]);

        return redirect()->route('media.index')->with('success', 'Drive link added successfully!');
    }

    // Edit Media
    public function edit($id)
    {
        $media = DB::table('Media')->where('MediaID', $id)->first();
        if (!$media) abort(404);

        $seasonEvent = DB::table('SeasonEvent')
            ->join('Season', 'SeasonEvent.SeasonID', '=', 'Season.SeasonID')
            ->join('Event', 'SeasonEvent.EventID', '=', 'Event.EventID')
            ->where('SeasonEvent.SeasonEventID', $media->SeasonEventID)
            ->select('SeasonEvent.SeasonEventID', 'Season.SeasonName', 'Season.SeasonYear', 'Event.EventName')
            ->first();

        return view('media.edit', compact('media', 'seasonEvent'));
    }

    // Update Media
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'drive_link' => 'required|url|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::table('Media')->where('MediaID', $id)->update([
            'DriveLink' => $request->drive_link
        ]);

        return redirect()->route('media.index')->with('success', 'Drive link updated successfully!');
    }


public function delete($id)
{
    $media = DB::table('Media')->where('MediaID', $id)->first();
    if (!$media) abort(404);

    $seasonEvent = DB::table('SeasonEvent')
        ->join('Season', 'SeasonEvent.SeasonID', '=', 'Season.SeasonID')
        ->join('Event', 'SeasonEvent.EventID', '=', 'Event.EventID')
        ->where('SeasonEvent.SeasonEventID', $media->SeasonEventID)
        ->select('SeasonEvent.SeasonEventID', 'Season.SeasonName', 'Season.SeasonYear', 'Event.EventName')
        ->first();

    return view('media.delete', compact('media', 'seasonEvent'));
}
    // Delete Media
    public function destroy($id)
    {
        DB::table('Media')->where('MediaID', $id)->delete();
        return redirect()->route('media.index')->with('success', 'Drive link deleted successfully!');
    }


// 1️⃣ Show the "Pages" view with Seasons dropdown
public function pages()
{
    $seasons = DB::table('Season')->orderBy('SeasonYear', 'desc')->get();
    return view('media.pages', compact('seasons'));
}

// 2️⃣ AJAX: Get Events for a selected Season
public function getEventsForPages(Request $request)
{
    $seasonID = $request->query('seasonID');
    if (!$seasonID) return response()->json([]);

    $events = DB::table('SeasonEvent as se')
        ->join('Event as e', 'se.EventID', '=', 'e.EventID')
        ->where('se.SeasonID', $seasonID)
        ->select('se.SeasonEventID', 'e.EventName', 'e.EventStartDate', 'e.EventEndDate')
        ->get();

    return response()->json($events);
}

// 3️⃣ Show media (photos) for a selected Event (SeasonEventID)
public function getMediaForEvent(Request $request)
{
    $seasonEventID = $request->query('seasonEventID');
    if (!$seasonEventID) return response()->json([]);

    $media = DB::table('Media')
        ->where('SeasonEventID', $seasonEventID)
        ->select('DriveLink')
        ->get();

    return response()->json($media);
}


}