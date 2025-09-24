<?php

namespace App\Http\Controllers\API;

// Core Laravel classes
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
class AttendanceController extends Controller
{
    /**
     * Get attendance data for a specific event, filtered by the authenticated user's authority.
     *
     * @param int $eventID The ID of the event.
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAttendanceByEventID($eventID)
    {
        // --- FIX 3: Corrected query to get event details ---
        $event = DB::table('Event')->where('EventID', $eventID)->first();

        // Check if the event exists. If not, return a 404 error.
        if (!$event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        // --- FIX 2 (Part 1): Get the QetaaID associated with this event ---
        // This assumes an event is linked to only ONE Qetaa.
        // If an event can have multiple Qetaat, this logic will need to be adjusted.
        $eventQetaa = DB::table('EventQetaa')->where('EventID', $eventID)->first();
        if (!$eventQetaa) {
            return response()->json(['message' => 'No sector (Qetaa) is associated with this event.'], 404);
        }
        $qetaa_id = $eventQetaa->QetaaID; // Now $qetaa_id is defined

        // Get the currently authenticated user's ID
        $authPersonID = Auth::user()->PersonID;

        // This query seems to get the groups a user manages.
        $groupControlledByAuthUser = DB::select("
            SELECT
                pg.GroupID,
                pg.GroupRoleID,
                gr.GroupRoleName,
                CONCAT(gt.GroupTypeName, ' ', g.GroupName) AS GroupInfo
            FROM PersonGroup pg
            LEFT JOIN GroupRole gr ON gr.GroupRoleID = pg.GroupRoleID
            LEFT JOIN `Group` g ON g.GroupID = pg.GroupID
            LEFT JOIN GroupType gt ON g.GroupTypeID = gt.GroupTypeID
            WHERE pg.PersonID = ?
        ", [$authPersonID]);

        // --- FIX 2 (Part 2): Use the defined $qetaa_id in the query ---
        // This query gets all people within the event's specific Qetaa.
        $personsInQetaa = DB::select("
            SELECT DISTINCT
                pi.ShamandoraCode,
                pi.FirstName,
                pi.SecondName,
                pi.ThirdName,
                pi.FourthName,
                q.QetaaName,
                pi.ScoutJoiningYear,
                sm.SanaMarhalaName,
                pi.RaqamQawmy,
                ppn.PersonPersonalMobileNumber
            FROM PersonInformation pi
            LEFT JOIN PersonSanaMarhala psm ON psm.PersonID = pi.PersonID
            LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
            LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
            LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
            LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
            WHERE q.QetaaID = ?
        ", [$qetaa_id]); // <-- $qetaa_id is now correctly used here

        // Return all the data in a structured JSON response
        return response()->json([
            'event' => $event,
            'groupControlledByAuthUser' => $groupControlledByAuthUser,
            'personsInQetaa' => $personsInQetaa,
        ], 200);
    }
}