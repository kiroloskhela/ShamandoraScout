<?php

namespace App\Http\Controllers;

// Core Laravel classes
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

// Facades for database and validation
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

// Other necessary classes
use \Illuminate\Http\Response;
use Session;
use Exception; // <--- FIX: Import the global Exception class

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $events = DB::select("
            SELECT e.*, et.EventTypeName, GROUP_CONCAT(q.QetaaName SEPARATOR ' | ') AS EventQetaat
            FROM Event e
            LEFT JOIN EventType et ON e.EventTypeID = et.EventTypeID
            LEFT JOIN EventQetaa eq ON e.EventID = eq.EventID
            LEFT JOIN Qetaa q ON eq.QetaaID = q.QetaaID
            GROUP BY e.EventID, e.EventName;
        ");

        return view("event.index", ['events' => $events]);
    }

    public function create()
    {
        $eventTypes = DB::table('EventType')->get();
        $qetaat = DB::table('Qetaa')->get();
        return view("event.create", ['qetaat' => $qetaat, 'eventTypes' => $eventTypes]);
    }

    public function createRecursive()
    {
        $eventTypes = DB::table('EventType')->get();
        $qetaat = DB::table('Qetaa')->get();
        return view("event.create-recursive", ['qetaat' => $qetaat, 'eventTypes' => $eventTypes]);
    }

   public function insert(Request $request)
{
    $isRecursive = $request->boolean('is_recursive');

    $baseRules = [
        'event_type_id' => 'required',
        'event_name'    => 'required|string|max:255',
        'qetaa_id'      => 'required|array|min:1',
        'qetaa_id.*'    => 'integer',
    ];

    $rules = $baseRules;

    if ($isRecursive) {
        $rules = array_merge($rules, [
            'event_multi_dates'   => 'required|array|min:1',
            'event_multi_dates.*' => 'date',
        ]);
    } else {
        $rules = array_merge($rules, [
            'event_start_date' => 'required|date',
            'event_end_date'   => 'required|date|after_or_equal:event_start_date',
        ]);
    }

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    try {
        DB::beginTransaction();

        if ($isRecursive) {
            // Insert one event per selected date (start = end = that date)
            $days = $request->input('event_multi_dates', []);
            foreach ($days as $day) {
                $eventId = DB::table('Event')->insertGetId([
                    'EventTypeID'    => $request->event_type_id,
                    'EventName'      => $request->event_name,
                    'EventStartDate' => $day,
                    'EventEndDate'   => $day,
                ]);

                $eventQetaatData = [];
                foreach ($request->qetaa_id as $qetaa) {
                    $eventQetaatData[] = ['EventID' => $eventId, 'QetaaID' => $qetaa];
                }
                DB::table('EventQetaa')->insert($eventQetaatData);
            }
        } else {
            // Single event (range)
            $eventId = DB::table('Event')->insertGetId([
                'EventTypeID'    => $request->event_type_id,
                'EventName'      => $request->event_name,
                'EventStartDate' => $request->event_start_date,
                'EventEndDate'   => $request->event_end_date,
            ]);

            $eventQetaatData = [];
            foreach ($request->qetaa_id as $qetaa) {
                $eventQetaatData[] = ['EventID' => $eventId, 'QetaaID' => $qetaa];
            }
            DB::table('EventQetaa')->insert($eventQetaatData);
        }

        DB::commit();
    } catch (Exception $e) {
        DB::rollBack();
        return view('person.entry-error');
    }

    return redirect()->route('event.index')->with('success', 'Event(s) created successfully!');
}


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        // You can implement this later if needed
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $eventTypes = DB::table('EventType')->get();
        $qetaat = DB::table('Qetaa')->get();

        $event = DB::table('Event')->where('EventID', $id)->first();

        // If the event doesn't exist, it's good practice to abort
        if (!$event) {
            abort(404);
        }

        // Get an array of QetaaIDs associated with this event
        $selectedQetaat = DB::table('EventQetaa')
            ->where('EventID', $id)
            ->pluck('QetaaID')
            ->toArray();

        // Pass all the data to the view
        return view("event.edit", [
            'event' => $event,
            'eventTypes' => $eventTypes,
            'qetaat' => $qetaat,
            'selectedQetaat' => $selectedQetaat
        ]);
    }

    public function updates(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'event_type_id' => 'required',
            'event_start_date' => 'required|date',
            'event_end_date' => 'required|date|after_or_equal:event_start_date',
            'qetaa_id' => 'required|array|min:1'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Update the main event details
            DB::table('Event')->where('EventID', $id)
                ->update([
                    'EventTypeID' => $request->event_type_id,
                    'EventName' => $request->event_name,
                    'EventStartDate' => $request->event_start_date,
                    'EventEndDate' => $request->event_end_date,
                ]);

            // Sync the pivot table: delete old entries and insert new ones
            DB::table('EventQetaa')->where('EventID', $id)->delete();

            $eventQetaatData = [];
            foreach ($request->qetaa_id as $qetaa) {
                $eventQetaatData[] = [
                    'EventID' => $id,
                    'QetaaID' => $qetaa
                ];
            }
            DB::table('EventQetaa')->insert($eventQetaatData);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            // Log::error($e->getMessage());
            return view('person.entry-error-repeat-trial');
        }

        return redirect()->route('event.index')->with('success', 'Event updated successfully!');
    }

    public function deletes($id)
    {
        $event = DB::table('Event')->where('EventID', $id)->first();
        if (!$event) {
            abort(404);
        }
        return view("event.delete", ['event' => $event]);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            DB::table('EventQetaa')->where('EventID', $id)->delete(); // Delete from child table first
            DB::table('Event')->where('EventID', $id)->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            // Log::error($e->getMessage());
            return view('person.entry-error-repeat-trial');
        }

        return redirect()->route('event.index')->with('success', 'Event deleted successfully!');
    }
}