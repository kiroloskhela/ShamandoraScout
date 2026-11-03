<?php

namespace App\Http\Controllers;

// Core Laravel classes
use App\Domain\Enrolment\LiveFormQetaaResolver;
// Facades for database and validation
use App\Domain\Event\EventAudience;
use App\Support\LookupCache;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
// Other necessary classes
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        $events = DB::select("
            SELECT 
                e.*, 
                et.EventTypeName, 
                GROUP_CONCAT(DISTINCT {$this->eventSectorLabelSql()} SEPARATOR ' | ') AS EventQetaat,
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

        return view('event.index', ['events' => $events]);
    }

    public function create()
    {
        $eventTypes = LookupCache::all('EventType');
        $qetaat = LookupCache::all('Qetaa');
        $seasons = DB::table('Season')->orderByDesc('SeasonYear')->orderBy('SeasonName')->get();

        return view('event.create', [
            'qetaat' => $qetaat,
            'eventTypes' => $eventTypes,
            'seasons' => $seasons,
        ]);
    }

    public function createRecursive()
    {
        $eventTypes = LookupCache::all('EventType');
        $qetaat = LookupCache::all('Qetaa');
        $seasons = DB::table('Season')->orderByDesc('SeasonYear')->orderBy('SeasonName')->get();

        return view('event.create-recursive', [
            'qetaat' => $qetaat,
            'eventTypes' => $eventTypes,
            'seasons' => $seasons,
        ]);
    }

    public function insert(Request $request)
    {
        $isRecursive = $request->boolean('is_recursive');

        $baseRules = [
            'event_type_id' => 'required|integer|exists:EventType,EventTypeID',
            'event_name' => 'nullable|string|max:255',
            'qetaa_id' => 'nullable|array',
            'qetaa_id.*' => 'integer|exists:Qetaa,QetaaID',
            'qetaa_scope' => 'nullable|array',
            'qetaa_scope.*' => 'in:all,serving',
            'season_id' => 'nullable|integer|exists:Season,SeasonID',
        ];

        $rules = $baseRules;

        if ($isRecursive) {
            $rules = array_merge($rules, [
                'event_multi_dates' => 'required|array|min:1',
                'event_multi_dates.*' => 'date|distinct',
            ]);
        } else {
            $rules = array_merge($rules, [
                'event_start_date' => 'required|date',
                'event_end_date' => 'nullable|date|after_or_equal:event_start_date',
            ]);
        }

        $validator = Validator::make($request->all(), $rules, [
            'season_id.exists' => 'الموسم المختار غير موجود.',
        ]);
        $audienceRows = $this->validateAudience($validator, $request);

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
                        array_column($audienceRows, 'QetaaID'),
                        $day,
                        $day
                    );

                    $eventId = DB::table('Event')->insertGetId([
                        'EventTypeID' => $request->event_type_id,
                        'EventName' => $generatedEventName,
                        'EventStartDate' => $day,
                        'EventEndDate' => $day,
                    ]);

                    $this->insertEventSectors($eventId, $audienceRows);

                    if ($seasonId) {
                        DB::table('SeasonEvent')->insert([
                            'SeasonID' => $seasonId,
                            'EventID' => $eventId,
                        ]);
                    }
                }
            } else {
                $startDate = $request->event_start_date;
                $endDate = $request->event_end_date ?: $startDate;

                $generatedEventName = $this->buildEventName(
                    $request->event_name,
                    (int) $request->event_type_id,
                    array_column($audienceRows, 'QetaaID'),
                    $startDate,
                    $endDate
                );

                $eventId = DB::table('Event')->insertGetId([
                    'EventTypeID' => $request->event_type_id,
                    'EventName' => $generatedEventName,
                    'EventStartDate' => $startDate,
                    'EventEndDate' => $endDate,
                ]);

                $this->insertEventSectors($eventId, $audienceRows);

                if ($seasonId) {
                    DB::table('SeasonEvent')->insert([
                        'SeasonID' => $seasonId,
                        'EventID' => $eventId,
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
        $eventTypes = LookupCache::all('EventType');
        $qetaat = LookupCache::all('Qetaa');
        $seasons = DB::table('Season')->orderByDesc('SeasonYear')->orderBy('SeasonName')->get();

        $event = DB::table('Event')->where('EventID', $id)->first();

        if (! $event) {
            abort(404);
        }

        [$selectedQetaat, $selectedScopes] = $this->selectedSectors((int) $id);

        $seasonEvent = DB::table('SeasonEvent')
            ->where('EventID', $id)
            ->first();

        $selectedSeasonId = $seasonEvent ? $seasonEvent->SeasonID : null;

        return view('event.edit', [
            'event' => $event,
            'eventTypes' => $eventTypes,
            'qetaat' => $qetaat,
            'selectedQetaat' => $selectedQetaat,
            'selectedScopes' => $selectedScopes,
            'seasons' => $seasons,
            'selectedSeasonId' => $selectedSeasonId,
        ]);
    }

    public function updates(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'event_type_id' => 'required|integer|exists:EventType,EventTypeID',
            'event_name' => 'nullable|string|max:255',
            'event_start_date' => 'required|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_start_date',
            'qetaa_id' => 'nullable|array',
            'qetaa_id.*' => 'integer|exists:Qetaa,QetaaID',
            'qetaa_scope' => 'nullable|array',
            'qetaa_scope.*' => 'in:all,serving',
            'season_id' => 'nullable|integer|exists:Season,SeasonID',
        ]);
        $audienceRows = $this->validateAudience($validator, $request);

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
                array_column($audienceRows, 'QetaaID'),
                $startDate,
                $endDate
            );

            DB::table('Event')
                ->where('EventID', $id)
                ->update([
                    'EventTypeID' => $request->event_type_id,
                    'EventName' => $generatedEventName,
                    'EventStartDate' => $startDate,
                    'EventEndDate' => $endDate,
                ]);

            DB::table('EventQetaa')->where('EventID', $id)->delete();
            $this->insertEventSectors((int) $id, $audienceRows);

            DB::table('SeasonEvent')->where('EventID', $id)->delete();

            if ($request->filled('season_id')) {
                DB::table('SeasonEvent')->insert([
                    'SeasonID' => $request->season_id,
                    'EventID' => $id,
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
        if (! $event) {
            abort(404);
        }

        return view('event.delete', ['event' => $event]);
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

    /**
     * @return array{0: list<int>, 1: array<int, string>}
     */
    private function selectedSectors(int $eventId): array
    {
        $links = DB::table('EventQetaa')->where('EventID', $eventId)->get();
        $hasScope = Schema::hasColumn('EventQetaa', 'AudienceScope');
        $hosts = [];
        $scopes = [];

        foreach ($links as $link) {
            $qetaaId = (int) $link->QetaaID;
            $scope = $hasScope ? ($link->AudienceScope ?: EventAudience::ALL) : EventAudience::ALL;
            if (in_array($qetaaId, LiveFormQetaaResolver::FROZEN_QETAA_IDS, true)) {
                $scopes[$qetaaId] = $scope === EventAudience::SERVING ? EventAudience::SERVING : EventAudience::ALL;
            } else {
                $hosts[] = $qetaaId;
            }
        }

        return [$hosts, $scopes];
    }

    private function validateAudience($validator, Request $request): array
    {
        $hostIds = $request->input('qetaa_id', []);
        $scopes = $request->input('qetaa_scope', []);
        if (! is_array($hostIds)) {
            $hostIds = [];
        }
        if (! is_array($scopes)) {
            $scopes = [];
        }

        $audience = app(EventAudience::class);
        foreach (array_keys($scopes) as $key) {
            if (! in_array((int) $key, LiveFormQetaaResolver::FROZEN_QETAA_IDS, true)) {
                $validator->errors()->add('qetaa_scope', __('Invalid sector.'));
                break;
            }
        }
        if ($audience->hasServingWithoutHost($hostIds, $scopes)) {
            $validator->errors()->add('qetaa_scope', __('Choose a sector first'));
        }

        $rows = $audience->rowsFromInput($hostIds, $scopes);
        if ($rows === []) {
            $validator->errors()->add('qetaa_id', __('Please choose at least one sector'));
        }

        return $rows;
    }

    /**
     * @param  list<array{QetaaID: int, AudienceScope: string}>  $rows
     */
    private function insertEventSectors(int $eventId, array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $hasScope = Schema::hasColumn('EventQetaa', 'AudienceScope');
        $payload = [];
        foreach ($rows as $row) {
            $item = [
                'EventID' => $eventId,
                'QetaaID' => $row['QetaaID'],
            ];
            if ($hasScope) {
                $item['AudienceScope'] = $row['AudienceScope'];
            }
            $payload[] = $item;
        }

        DB::table('EventQetaa')->insert($payload);
    }

    private function eventSectorLabelSql(): string
    {
        if (! Schema::hasColumn('EventQetaa', 'AudienceScope')) {
            return 'q.QetaaName';
        }

        $suffix = str_replace("'", "''", __(' (selected sectors)'));

        return "CASE WHEN eq.AudienceScope = 'serving' THEN CONCAT(q.QetaaName, '{$suffix}') ELSE q.QetaaName END";
    }
}
