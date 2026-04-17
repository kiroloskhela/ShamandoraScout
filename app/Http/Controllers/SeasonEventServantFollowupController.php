<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeasonEventServantFollowupController extends Controller
{
    public function selector()
    {
        $seasons = DB::table('Season')
            ->orderBy('SeasonYear', 'desc')
            ->get();

        return view('event_servant_followup.selector', compact('seasons'));
    }

    public function getEventsWithPlan(Request $request)
    {
        $seasonID = $request->query('seasonID');

        if (!$seasonID) {
            return response()->json([]);
        }

        $events = DB::table('SeasonEvent as se')
            ->join('Event as e', 'se.EventID', '=', 'e.EventID')
            ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
            ->join('SeasonEventFinance as sef', 'se.SeasonEventID', '=', 'sef.SeasonEventID')
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
    $servantPersonID = (int) Auth::user()->PersonID;

    $event = DB::table('SeasonEvent as se')
        ->join('Season as s', 'se.SeasonID', '=', 's.SeasonID')
        ->join('Event as e', 'se.EventID', '=', 'e.EventID')
        ->join('EventType as et', 'e.EventTypeID', '=', 'et.EventTypeID')
        ->where('se.SeasonEventID', $seasonEventID)
        ->select(
            'se.SeasonEventID',
            'se.EventID',
            's.SeasonName',
            's.SeasonYear',
            'e.EventName',
            'e.EventStartDate',
            'e.EventEndDate',
            'et.EventTypeName'
        )
        ->first();

    if (!$event) {
        abort(404);
    }

    $servedPeopleSubquery = DB::table('PersonInformation as pi')
        ->distinct()
        ->leftJoin('PersonQetaa as pq', 'pi.PersonID', '=', 'pq.PersonID')
        ->leftJoin('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
        ->whereIn('q.QetaaID', function ($query) use ($servantPersonID) {
            $query->select('gq2.QetaaID')
                ->from('GroupQetaa as gq2')
                ->whereIn('gq2.GroupID', function ($sub) use ($servantPersonID) {
                    $sub->select('pg3.GroupID')
                        ->from('PersonGroup as pg3')
                        ->where('pg3.PersonID', $servantPersonID);
                });
        })
        ->select('pi.PersonID');

    $booked = DB::table('SeasonEventParticipantFinance as b')
        ->join('PersonInformation as pi', 'b.PersonID', '=', 'pi.PersonID')
        ->leftJoin('PersonQetaa as pq', 'pi.PersonID', '=', 'pq.PersonID')
        ->leftJoin('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
        ->leftJoin('PersonSanaMarhala as psm', 'pi.PersonID', '=', 'psm.PersonID')
        ->leftJoin('SanaMarhala as sm', 'psm.SanaMarhalaID', '=', 'sm.SanaMarhalaID')
        ->where('b.SeasonEventID', $seasonEventID)
        ->where('b.IsRefunded', 0)
        ->whereNotNull('b.PersonID')
        ->whereIn('pi.PersonID', $servedPeopleSubquery)
        ->select(
            'pi.PersonID',
            'pi.ShamandoraCode',
            DB::raw("TRIM(CONCAT(
                COALESCE(pi.FirstName,''), ' ',
                COALESCE(pi.SecondName,''), ' ',
                COALESCE(pi.ThirdName,''), ' ',
                COALESCE(pi.FourthName,'')
            )) as PersonFullName"),
            DB::raw("COALESCE(GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ' , '), '-') as QetaaName"),
            DB::raw("COALESCE(MAX(sm.SanaMarhalaName), '-') as SanaMarhalaName"),
            'b.FinalRequiredAmount',
            'b.AmountPaid',
            'b.RemainingAmount'
        )
        ->groupBy(
            'pi.PersonID',
            'pi.ShamandoraCode',
            'pi.FirstName',
            'pi.SecondName',
            'pi.ThirdName',
            'pi.FourthName',
            'b.FinalRequiredAmount',
            'b.AmountPaid',
            'b.RemainingAmount'
        )
        ->orderBy('PersonFullName')
        ->get()
        ->map(function ($row) {
            $row->ShamandoraCode = $row->ShamandoraCode ?: ('SH-' . $row->PersonID);
            $row->QetaaName = $row->QetaaName ?: '-';
            $row->SanaMarhalaName = $row->SanaMarhalaName ?: '-';
            $row->FinalRequiredAmount = number_format((float) $row->FinalRequiredAmount, 2);
            $row->AmountPaid = number_format((float) $row->AmountPaid, 2);
            $row->RemainingAmount = number_format((float) $row->RemainingAmount, 2);
            return $row;
        });

    $waitingList = DB::table('SeasonEventWaitingList as w')
        ->join('PersonInformation as pi', 'w.PersonID', '=', 'pi.PersonID')
        ->leftJoin('PersonQetaa as pq', 'pi.PersonID', '=', 'pq.PersonID')
        ->leftJoin('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
        ->leftJoin('PersonSanaMarhala as psm', 'pi.PersonID', '=', 'psm.PersonID')
        ->leftJoin('SanaMarhala as sm', 'psm.SanaMarhalaID', '=', 'sm.SanaMarhalaID')
        ->where('w.SeasonEventID', $seasonEventID)
        ->whereIn('pi.PersonID', $servedPeopleSubquery)
        ->select(
            'pi.PersonID',
            'pi.ShamandoraCode',
            DB::raw("TRIM(CONCAT(
                COALESCE(pi.FirstName,''), ' ',
                COALESCE(pi.SecondName,''), ' ',
                COALESCE(pi.ThirdName,''), ' ',
                COALESCE(pi.FourthName,'')
            )) as PersonFullName"),
            DB::raw("COALESCE(GROUP_CONCAT(DISTINCT q.QetaaName ORDER BY q.QetaaName SEPARATOR ' , '), '-') as QetaaName"),
            DB::raw("COALESCE(MAX(sm.SanaMarhalaName), '-') as SanaMarhalaName")
        )
        ->groupBy(
            'pi.PersonID',
            'pi.ShamandoraCode',
            'pi.FirstName',
            'pi.SecondName',
            'pi.ThirdName',
            'pi.FourthName'
        )
        ->orderBy('PersonFullName')
        ->get()
        ->map(function ($row) {
            $row->ShamandoraCode = $row->ShamandoraCode ?: ('SH-' . $row->PersonID);
            $row->QetaaName = $row->QetaaName ?: '-';
            $row->SanaMarhalaName = $row->SanaMarhalaName ?: '-';
            return $row;
        });

    return view('event_servant_followup.index', compact(
        'event',
        'booked',
        'waitingList'
    ));
}
}