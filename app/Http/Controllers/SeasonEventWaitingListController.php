<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SeasonEventWaitingListController extends Controller
{
    public function selector()
    {
        $seasons = DB::table('Season')
            ->orderBy('SeasonYear', 'desc')
            ->get();

        return view('event_waiting_list.selector', compact('seasons'));
    }

    public function getEvents(Request $request)
    {
        $seasonID = $request->query('seasonID');

        if (!$seasonID) {
            return response()->json([]);
        }

        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->where('se.SeasonID', $seasonID)
            ->select(
                'se.SeasonEventID',
                'e.EventName',
                'et.EventTypeName',
                'e.EventStartDate',
                'e.EventEndDate'
            )
            ->orderBy('e.EventStartDate')
            ->get();

        return response()->json($events);
    }

    public function index($seasonEventID)
    {
        $event = $this->getSeasonEventFullInfo($seasonEventID);

        if (!$event) {
            abort(404);
        }

        $waitingList = DB::table('SeasonEventWaitingList as wl')
            ->join('PersonInformation as p', 'wl.PersonID', '=', 'p.PersonID')
            ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
            ->leftJoin('Qetaa as q', 'wl.QetaaID', '=', 'q.QetaaID')
            ->leftJoin('PersonInformation as s', 'wl.ServentID', '=', 's.PersonID')
            ->where('wl.SeasonEventID', $seasonEventID)
            ->select(
                'wl.SeasonEventWaitingListID',
                'wl.SeasonEventID',
                'wl.PersonID',
                'wl.ServentID',
                'wl.QetaaID',
                'wl.CreatedAt',
                'ppn.PersonPersonalMobileNumber',
                'q.QetaaName',
                DB::raw("TRIM(CONCAT(
                    COALESCE(p.FirstName,''), ' ',
                    COALESCE(p.SecondName,''), ' ',
                    COALESCE(p.ThirdName,''), ' ',
                    COALESCE(p.FourthName,'')
                )) as PersonFullName"),
                DB::raw("TRIM(CONCAT(
                    COALESCE(s.FirstName,''), ' ',
                    COALESCE(s.SecondName,''), ' ',
                    COALESCE(s.ThirdName,''), ' ',
                    COALESCE(s.FourthName,'')
                )) as ServentFullName")
            )
            ->orderBy('wl.CreatedAt')
            ->get();

        return view('event_waiting_list.index', compact('event', 'waitingList'));
    }

    public function searchEligiblePersons(Request $request, $seasonEventID)
    {
        $query = trim((string) $request->query('q', ''));

        $event = DB::table('SeasonEvent')->where('SeasonEventID', $seasonEventID)->first();
        if (!$event) {
            return response()->json([]);
        }

        $eligibleQetaaIDs = DB::table('EventQetaa')
            ->where('EventID', $event->EventID)
            ->pluck('QetaaID')
            ->toArray();

        if (empty($eligibleQetaaIDs)) {
            return response()->json([]);
        }

        $persons = DB::table('PersonInformation as p')
            ->join('PersonQetaa as pq', 'p.PersonID', '=', 'pq.PersonID')
            ->join('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
            ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
            ->whereIn('pq.QetaaID', $eligibleQetaaIDs)
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('PersonBlackList as pb')
                    ->whereColumn('pb.PersonID', 'p.PersonID');
            })
            ->whereNotExists(function ($sub) use ($seasonEventID) {
                $sub->select(DB::raw(1))
                    ->from('SeasonEventParticipantFinance as b')
                    ->where('b.SeasonEventID', $seasonEventID)
                    ->whereColumn('b.PersonID', 'p.PersonID');
            })
            ->whereNotExists(function ($sub) use ($seasonEventID) {
                $sub->select(DB::raw(1))
                    ->from('SeasonEventWaitingList as wl')
                    ->where('wl.SeasonEventID', $seasonEventID)
                    ->whereColumn('wl.PersonID', 'p.PersonID');
            })
            ->where(function ($sub) use ($query) {
                if ($query !== '') {
                    $sub->where(DB::raw("CONCAT_WS(' ', p.FirstName, p.SecondName, p.ThirdName, p.FourthName)"), 'like', '%' . $query . '%')
                        ->orWhere('p.PersonID', 'like', '%' . $query . '%')
                        ->orWhere('ppn.PersonPersonalMobileNumber', 'like', '%' . $query . '%');
                }
            })
            ->select(
                'p.PersonID',
                'pq.QetaaID',
                'ppn.PersonPersonalMobileNumber',
                DB::raw("TRIM(CONCAT(
                    COALESCE(p.FirstName,''), ' ',
                    COALESCE(p.SecondName,''), ' ',
                    COALESCE(p.ThirdName,''), ' ',
                    COALESCE(p.FourthName,'')
                )) as PersonFullName"),
                DB::raw("GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ' , ') as QetaaNames")
            )
            ->groupBy(
                'p.PersonID',
                'pq.QetaaID',
                'ppn.PersonPersonalMobileNumber',
                'p.FirstName',
                'p.SecondName',
                'p.ThirdName',
                'p.FourthName'
            )
            ->orderBy('PersonFullName')
            ->limit(20)
            ->get();

        return response()->json($persons);
    }

    public function store(Request $request, $seasonEventID)
    {
        $event = $this->getSeasonEventFullInfo($seasonEventID);
        if (!$event) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
        ], [
            'person_id.required' => 'يجب اختيار الشخص.',
            'person_id.integer' => 'الشخص المختار غير صحيح.',
            'person_id.exists' => 'الشخص المختار غير موجود.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $personID = (int) $request->person_id;
        $serventID = (int) Auth::user()->PersonID;

        if ($this->isBlacklisted($personID)) {
            return redirect()->route('eventWaitingList.index', $seasonEventID);
        }

        if (!$this->isEligibleByQetaa($seasonEventID, $personID)) {
            return redirect()->route('eventWaitingList.index', $seasonEventID);
        }

        $alreadyBooked = DB::table('SeasonEventParticipantFinance')
            ->where('SeasonEventID', $seasonEventID)
            ->where('PersonID', $personID)
            ->exists();

        if ($alreadyBooked) {
            return redirect()->route('eventWaitingList.index', $seasonEventID);
        }

        $alreadyInWaitingList = DB::table('SeasonEventWaitingList')
            ->where('SeasonEventID', $seasonEventID)
            ->where('PersonID', $personID)
            ->exists();

        if ($alreadyInWaitingList) {
            return redirect()->route('eventWaitingList.index', $seasonEventID);
        }

        $qetaaID = DB::table('PersonQetaa as pq')
            ->join('EventQetaa as eq', 'pq.QetaaID', '=', 'eq.QetaaID')
            ->join('SeasonEvent as se', 'eq.EventID', '=', 'se.EventID')
            ->where('se.SeasonEventID', $seasonEventID)
            ->where('pq.PersonID', $personID)
            ->value('pq.QetaaID');

        try {
            DB::table('SeasonEventWaitingList')->insert([
                'SeasonEventID' => $seasonEventID,
                'PersonID' => $personID,
                'ServentID' => $serventID,
                'QetaaID' => $qetaaID,
                'CreatedAt' => now(),
            ]);

            return redirect()->route('eventWaitingList.index', $seasonEventID)
                ->with('success', 'تمت إضافة الشخص إلى قائمة الانتظار بنجاح.');
        } catch (Exception $e) {
            return redirect()->route('eventWaitingList.index', $seasonEventID)
                ->withErrors([
                    'general' => 'حدث خطأ أثناء إضافة الشخص إلى قائمة الانتظار.'
                ]);
        }
    }

    public function destroy($waitingListID)
    {
        $row = DB::table('SeasonEventWaitingList')
            ->where('SeasonEventWaitingListID', $waitingListID)
            ->first();

        if (!$row) {
            abort(404);
        }

        DB::table('SeasonEventWaitingList')
            ->where('SeasonEventWaitingListID', $waitingListID)
            ->delete();

        return redirect()->route('eventWaitingList.index', $row->SeasonEventID)
            ->with('success', 'تم حذف الشخص من قائمة الانتظار بنجاح.');
    }

    private function getSeasonEventFullInfo($seasonEventID)
    {
        return DB::table('SeasonEvent as se')
            ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->where('se.SeasonEventID', $seasonEventID)
            ->select(
                'se.SeasonEventID',
                'se.EventID',
                's.SeasonID',
                's.SeasonName',
                's.SeasonYear',
                'e.EventName',
                'e.EventStartDate',
                'e.EventEndDate',
                'et.EventTypeName'
            )
            ->first();
    }

    private function isEligibleByQetaa($seasonEventID, $personID)
    {
        $event = DB::table('SeasonEvent')->where('SeasonEventID', $seasonEventID)->first();
        if (!$event) {
            return false;
        }

        return DB::table('EventQetaa as eq')
            ->join('PersonQetaa as pq', 'eq.QetaaID', '=', 'pq.QetaaID')
            ->where('eq.EventID', $event->EventID)
            ->where('pq.PersonID', $personID)
            ->exists();
    }

    private function isBlacklisted($personID)
    {
        return DB::table('PersonBlackList')
            ->where('PersonID', $personID)
            ->exists();
    }

    public function deletePage($waitingListID)
{
    $row = DB::table('SeasonEventWaitingList as wl')
        ->join('SeasonEvent as se', 'wl.SeasonEventID', '=', 'se.SeasonEventID')
        ->join('Season as sn', 'se.SeasonID', '=', 'sn.SeasonID')
        ->join('Event as e', 'se.EventID', '=', 'e.EventID')
        ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
        ->join('PersonInformation as p', 'wl.PersonID', '=', 'p.PersonID')
        ->leftJoin('PersonPhoneNumbers as ppn', 'p.PersonID', '=', 'ppn.PersonID')
        ->leftJoin('Qetaa as q', 'wl.QetaaID', '=', 'q.QetaaID')
        ->leftJoin('PersonInformation as s', 'wl.ServentID', '=', 's.PersonID')
        ->where('wl.SeasonEventWaitingListID', $waitingListID)
        ->select(
            'wl.SeasonEventWaitingListID',
            'wl.SeasonEventID',
            'wl.PersonID',
            'wl.ServentID',
            'wl.QetaaID',
            'wl.CreatedAt',
            'sn.SeasonName',
            'sn.SeasonYear',
            'e.EventName',
            'e.EventStartDate',
            'e.EventEndDate',
            'et.EventTypeName',
            'ppn.PersonPersonalMobileNumber',
            'q.QetaaName',
            DB::raw("TRIM(CONCAT(
                COALESCE(p.FirstName,''), ' ',
                COALESCE(p.SecondName,''), ' ',
                COALESCE(p.ThirdName,''), ' ',
                COALESCE(p.FourthName,'')
            )) as PersonFullName"),
            DB::raw("TRIM(CONCAT(
                COALESCE(s.FirstName,''), ' ',
                COALESCE(s.SecondName,''), ' ',
                COALESCE(s.ThirdName,''), ' ',
                COALESCE(s.FourthName,'')
            )) as ServentFullName")
        )
        ->first();

    if (!$row) {
        abort(404);
    }

    return view('event_waiting_list.delete', compact('row'));
}

}