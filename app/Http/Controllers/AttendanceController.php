<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Session;
use Auth;



class AttendanceController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {
            /*$groups = DB::table('GroupTable')
                        ->leftJoin('GroupType', 'GroupTable.GroupTypeID', '=', 'GroupType.GroupTypeID')
                        ->leftJoin('GroupType', 'GroupTable.IncludedUnderGroupID', '=', 'GroupType.GroupTypeID');*/
            
            $events = DB::select("  SELECT CONCAT(EventType.EventTypeName, ' - ', Event.EventName, ' من ' , Event.EventStartDate, ' إلى ', Event.EventEndDate) AS EventInfo, EventID
                                    FROM Event
                                    LEFT JOIN EventType ON Event.EventTypeID = EventType.EventtypeID");
            /*
            $groups =   DB::select("SELECT    g1.IncludedUnderGroupID, 
                                                g1.GroupID AS GroupID1, 
                                                g1.GroupName, 
                                                g3.GroupTypeName, 
                                                g2.GroupID AS GroupID2, 
                                                CONCAT(g4.GroupTypeName, ' ', g2.GroupName) AS IncludedUnderGroupName
                                    FROM GroupTable g1
                                    LEFT JOIN GroupTable g2 ON g1.IncludedUnderGroupID = g2.GroupID
                                    LEFT JOIN GroupType g3 ON g1.GroupTypeID = g3.GroupTypeID
                                    LEFT JOIN GroupType g4 ON g2.GroupTypeID = g4.GroupTypeID
                                    ");
            */
            return view("attendance.index", array('events' => $events));
        }

        public function getPersonsByLeaderPersonID($leaderID)
        {
            $persons = DB::select("SELECT PersonGroup.GroupID FROM PersonGroup WHERE  PeronGroup.PersonID = ?", [$leaderID]);
        }

        public function findAttendanceByEventID($eventID)
        {
            if(DB::table("PersonEventAttendance")->where("PersonEventAttendance.EventID", "=", $eventID)->exists()) //Event is found in attendance and has records attached to it
            {
                //This means that the event is having records attached to it
                //So, we need to return all the persons result array joined with the attendance of each person at this given EventID
                //For those users who are not found in the attendance table, they are fetched in the result persons array to be returned to the view

                $attendanceResultArray = DB::select ("      SELECT  PersonInformation.PersonID
                                                                PersonInformation.ShamandoraCode
                                                                CONCAT(PersonInformation.FirstName, ' ', PersonInformation.SecondName, ' ', PersonInformation.ThirdName, ' ', PersonInformation.FourthName) AS PersonFullName
                                                                GroupRole.GroupRoleName,
                                                                GroupTable.GroupName,
                                                                GroupType.GroupTypeName
                                                        FROM PersonInformation
                                                        LEFT JOIN PersonGroup ON PersonInformation.PersonID = PersonGroup.PersonID
                                                        LEFT JOIN GroupRole ON PersonGroup.GroupID = GroupRole.GroupID
                                                        LEFT JOIN PersonEventAttendance ON PersonEventAttendance.PersonID = PersonInformation.PersonID
                                                        LEFT JOIN GroupTable ON GroupTable.GroupID = PersonGroup.GroupID
                                                        LEFT JOIN GroupType ON GroupTable.GroupTypeID = GroupType.GroupTypeID
                                                        WHERE PersonInformation.
                                                ");
            }
            else
            {
                //This is event is not attached to any persons attendance and deosn't have any records
                //Return the Persons array without the attendance array
                //Return the attendance result array containing (PersonID) and Empty Checkboxes


            }

            $groupControlledByAuthUser = DB::select("   SELECT  PersonGroup.GroupID, 
                                                            PersonGroup.GroupRoleID, 
                                                            GroupRole.GroupRoleName, 
                                                            CONCAT(GroupType.GroupTypeName, ' ', GroupTable.GroupName) AS GroupInfo
                                                    FROM PersonGroup
                                                    WHERE PersonGroup.PersonID = ?
                                                    LEFT JOIN GroupRole ON GroupRole.GroupRoleID = PersonGroup.GroupRoleID
                                                    LEFT JOIN GroupTable ON GroupTable.GroupID = PersonGroup.GroupID
                                                    LEFT JOIN GroupType ON GroupTable.GroupTypeID = GroupType.GroupTypeID 
                                                ", [Auth::user()->PersonID]);

            $groupTypes = DB::table('GroupType')->get();
            $groups = DB::select("  SELECT  GroupTable.GroupID,  GroupTable.IncludedUnderGroupID,
                                            CONCAT(GroupType.GroupTypeName, ' ', GroupTable.GroupName) AS GroupInfo
                                    FROM GroupTable
                                    LEFT JOIN GroupType ON GroupTable.GroupTypeID = GroupType.GroupTypeID
                                ");
            return view("group.create", array('groupTypes'=>$groupTypes, 'groups'=>$groups));
        }

        public function insert(Request  $request)
        {
            $lastGroupID = DB::table('GroupTable')->orderBy('GroupID','desc')->first();
            
            if($lastGroupID==Null)
                $thisGroupID = 1;
            else
                $thisGroupID = $lastGroupID->GroupID + 1;

            DB::table('GroupTable')->insert(
                array(
                    'GroupID' => $thisGroupID,
                    'GroupName' => $request -> group_name,
                    'GroupTypeID' => $request -> group_type_id,
                    'IncludedUnderGroupID' => $request -> included_under_group_id
                )
            );
            return redirect()->route('group.index');
        }
    
        /**
            * Display the specified resource.
            *
            * @param  int  $id
            * @return Response
            */
        public function show($id)
        {
            //
        }
    
        /**
            * Show the form for editing the specified resource.
            *
            * @param  int  $id
            * @return Response
            */
        public function edit($id)
        {

            $groupSelected =   DB::selectOne("SELECT    g1.IncludedUnderGroupID, 
                                    g1.GroupID AS GroupID1,
                                    g3.GroupTypeID,
                                    g1.GroupName, 
                                    g3.GroupTypeName, 
                                    g2.GroupID AS GroupID2, 
                                    CONCAT(g4.GroupTypeName, ' ', g2.GroupName) AS GroupInfo
                        FROM GroupTable g1
                        LEFT JOIN GroupTable g2 ON g1.IncludedUnderGroupID = g2.GroupID
                        LEFT JOIN GroupType g3 ON g1.GroupTypeID = g3.GroupTypeID
                        LEFT JOIN GroupType g4 ON g2.GroupTypeID = g4.GroupTypeID
                        WHERE g1.GroupID = ?
                        ", [$id]);
            //return $groupSelected;
            $groupTypes = DB::table('GroupType')->get();

            $groups = DB::select("  SELECT  GroupTable.GroupID,  GroupTable.IncludedUnderGroupID,
                                            CONCAT(GroupType.GroupTypeName, ' ', GroupTable.GroupName) AS GroupInfo
                                    FROM GroupTable
                                    LEFT JOIN GroupType ON GroupTable.GroupTypeID = GroupType.GroupTypeID
                                ");
    
            return view("group.edit", array('groupSelected' => $groupSelected, 'groupTypes' => $groupTypes, 'groups' => $groups));
        }
    
        public function updates(Request $request, $id)
        {
            //$qetaa = DB::table('Qetaa')->where('QetaaID', $id)->first();
            //return $request;
            $affected = DB::table('GroupTable')->where('GroupID', $id)->update(['GroupName' => $request->group_name, 
                                                                                'GroupTypeID'=> $request -> group_type_id, 
                                                                                'IncludedUnderGroupID' => $request -> included_under_group_id]);

            return redirect()->route('group.index');
        }
    
        public function deletes($id)
        {
            $group = DB::table('GroupTable')->where('GroupID', $id)->first();
            return view("group.delete", array('group' => $group));
        }

        public function destroy($id)
        {
            $deleted = DB::table('GroupTable')->where('GroupID',$id)->delete();
            
            return redirect()->route('group.index');
        }
}