<?php

namespace App\Http\Controllers;

// Core Laravel classes
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

// Facades for database and validation
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

// Other necessary classes
use \Illuminate\Http\Response;
use Session;
use Exception;

class EventController extends Controller
{
    public function index()
    {
        $events = DB::select("
            SELECT 
                e.*, 
                et.EventTypeName, 
                GROUP_CONCAT(DISTINCT q.QetaaName SEPARATOR ' | ') AS EventQetaat,
                s.SeasonName,
                s.SeasonYear
            FROM Event e
            LEFT JOIN EventType et ON e.EventTypeID = et.EventTypeID
            LEFT JOIN EventQetaa eq ON e.EventID = eq.EventID
            LEFT JOIN Qetaa q ON eq.QetaaID = q.QetaaID
            LEFT JOIN SeasonEvent se ON e.EventID = se.EventID
            LEFT JOIN Season s ON se.SeasonID = s.SeasonID
            GROUP BY e.EventID, e.EventName, et.EventTypeName, s.SeasonName, s.SeasonYear
        ");

        return view("event.index", ['events' => $events]);
    }

    public function create()
    {
        $eventTypes = DB::table('EventType')->get();
        $qetaat = DB::table('Qetaa')->get();
        $seasons = DB::table('Season')->orderByDesc('SeasonYear')->orderBy('SeasonName')->get();

        return view("event.create", [
            'qetaat' => $qetaat,
            'eventTypes' => $eventTypes,
            'seasons' => $seasons
        ]);
    }

    public function createRecursive()
    {
        $eventTypes = DB::table('EventType')->get();
        $qetaat = DB::table('Qetaa')->get();
        $seasons = DB::table('Season')->orderByDesc('SeasonYear')->orderBy('SeasonName')->get();

        return view("event.create-recursive", [
            'qetaat' => $qetaat,
            'eventTypes' => $eventTypes,
            'seasons' => $seasons
        ]);
    }

    public function insert(Request $request)
    {
        $isRecursive = $request->boolean('is_recursive');

        $baseRules = [
            'event_type_id' => 'required|integer|exists:EventType,EventTypeID',
            'event_name'    => 'nullable|string|max:255',
            'qetaa_id'      => 'required|array|min:1',
            'qetaa_id.*'    => 'integer|exists:Qetaa,QetaaID',
            'season_id'     => 'nullable|integer|exists:Season,SeasonID',
        ];

        $rules = $baseRules;

        if ($isRecursive) {
            $rules = array_merge($rules, [
                'event_multi_dates'   => 'required|array|min:1',
                'event_multi_dates.*' => 'date|distinct',
            ]);
        } else {
            $rules = array_merge($rules, [
                'event_start_date' => 'required|date',
                'event_end_date'   => 'nullable|date|after_or_equal:event_start_date',
            ]);
        }

        $validator = Validator::make($request->all(), $rules, [
            'season_id.exists' => 'الموسم المختار غير موجود.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $seasonId = $request->filled('season_id') ? (int) $request->season_id : null;

            if ($isRecursive) {
                $days = $request->input('event_multi_dates', []);

                foreach ($days as $day) {
                    $generatedEventName = $this->buildEventName(
                        $request->event_name,
                        (int) $request->event_type_id,
                        $request->qetaa_id,
                        $day,
                        $day
                    );

                    $eventId = DB::table('Event')->insertGetId([
                        'EventTypeID'    => $request->event_type_id,
                        'EventName'      => $generatedEventName,
                        'EventStartDate' => $day,
                        'EventEndDate'   => $day,
                    ]);

                    $eventQetaatData = [];
                    foreach ($request->qetaa_id as $qetaa) {
                        $eventQetaatData[] = [
                            'EventID' => $eventId,
                            'QetaaID' => $qetaa
                        ];
                    }
                    DB::table('EventQetaa')->insert($eventQetaatData);

                    if ($seasonId) {
                        DB::table('SeasonEvent')->insert([
                            'SeasonID' => $seasonId,
                            'EventID'  => $eventId,
                        ]);
                    }
                }
            } else {
                $startDate = $request->event_start_date;
                $endDate = $request->event_end_date ?: $startDate;

                $generatedEventName = $this->buildEventName(
                    $request->event_name,
                    (int) $request->event_type_id,
                    $request->qetaa_id,
                    $startDate,
                    $endDate
                );

                $eventId = DB::table('Event')->insertGetId([
                    'EventTypeID'    => $request->event_type_id,
                    'EventName'      => $generatedEventName,
                    'EventStartDate' => $startDate,
                    'EventEndDate'   => $endDate,
                ]);

                $eventQetaatData = [];
                foreach ($request->qetaa_id as $qetaa) {
                    $eventQetaatData[] = [
                        'EventID' => $eventId,
                        'QetaaID' => $qetaa
                    ];
                }
                DB::table('EventQetaa')->insert($eventQetaatData);

                if ($seasonId) {
                    DB::table('SeasonEvent')->insert([
                        'SeasonID' => $seasonId,
                        'EventID'  => $eventId,
                    ]);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return view('person.entry-error');
        }

        return redirect()->route('event.index')->with('success', 'Event(s) created successfully!');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $eventTypes = DB::table('EventType')->get();
        $qetaat = DB::table('Qetaa')->get();
        $seasons = DB::table('Season')->orderByDesc('SeasonYear')->orderBy('SeasonName')->get();

        $event = DB::table('Event')->where('EventID', $id)->first();

        if (!$event) {
            abort(404);
        }

        $selectedQetaat = DB::table('EventQetaa')
            ->where('EventID', $id)
            ->pluck('QetaaID')
            ->toArray();

        $seasonEvent = DB::table('SeasonEvent')
            ->where('EventID', $id)
            ->first();

        $selectedSeasonId = $seasonEvent ? $seasonEvent->SeasonID : null;

        return view("event.edit", [
            'event' => $event,
            'eventTypes' => $eventTypes,
            'qetaat' => $qetaat,
            'selectedQetaat' => $selectedQetaat,
            'seasons' => $seasons,
            'selectedSeasonId' => $selectedSeasonId
        ]);
    }

    public function updates(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'event_type_id'   => 'required|integer|exists:EventType,EventTypeID',
            'event_name'      => 'nullable|string|max:255',
            'event_start_date'=> 'required|date',
            'event_end_date'  => 'nullable|date|after_or_equal:event_start_date',
            'qetaa_id'        => 'required|array|min:1',
            'qetaa_id.*'      => 'integer|exists:Qetaa,QetaaID',
            'season_id'       => 'nullable|integer|exists:Season,SeasonID',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $startDate = $request->event_start_date;
            $endDate = $request->event_end_date ?: $startDate;

            $generatedEventName = $this->buildEventName(
                $request->event_name,
                (int) $request->event_type_id,
                $request->qetaa_id,
                $startDate,
                $endDate
            );

            DB::table('Event')
                ->where('EventID', $id)
                ->update([
                    'EventTypeID'    => $request->event_type_id,
                    'EventName'      => $generatedEventName,
                    'EventStartDate' => $startDate,
                    'EventEndDate'   => $endDate,
                ]);

            DB::table('EventQetaa')->where('EventID', $id)->delete();

            $eventQetaatData = [];
            foreach ($request->qetaa_id as $qetaa) {
                $eventQetaatData[] = [
                    'EventID' => $id,
                    'QetaaID' => $qetaa
                ];
            }
            DB::table('EventQetaa')->insert($eventQetaatData);

            DB::table('SeasonEvent')->where('EventID', $id)->delete();

            if ($request->filled('season_id')) {
                DB::table('SeasonEvent')->insert([
                    'SeasonID' => $request->season_id,
                    'EventID'  => $id,
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
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
            DB::table('SeasonEvent')->where('EventID', $id)->delete();
            DB::table('EventQetaa')->where('EventID', $id)->delete();
            DB::table('Event')->where('EventID', $id)->delete();
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return view('person.entry-error-repeat-trial');
        }

        return redirect()->route('event.index')->with('success', 'Event deleted successfully!');
    }

    private function buildEventName($manualName, int $eventTypeId, array $qetaaIds, $startDate, $endDate)
    {
        $manualName = trim((string) $manualName);
        if ($manualName !== '') {
            return $manualName;
        }

        $eventTypeName = DB::table('EventType')
            ->where('EventTypeID', $eventTypeId)
            ->value('EventTypeName');

        $qetaaNames = DB::table('Qetaa')
            ->whereIn('QetaaID', $qetaaIds)
            ->pluck('QetaaName')
            ->toArray();

        $qetaaText = implode(' - ', $qetaaNames);

        if ($startDate === $endDate) {
            return trim("{$eventTypeName} - {$qetaaText} - {$startDate}");
        }

        return trim("{$eventTypeName} - {$qetaaText} - {$startDate} till {$endDate}");
    }
}