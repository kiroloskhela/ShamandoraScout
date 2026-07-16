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

class PersonRoleController extends Controller
{
/**
        * Display a listing of the resource.
        *
        * @return Response
        */
        public function index()
        {

            
$personRoles = DB::select(" SELECT pi.PersonID, pi.ShamandoraCode, pr.PersonRoleID, r.RoleName, q.QetaaName, r.RoleID,
           CONCAT(pi.FirstName, ' ', pi.SecondName, ' ', pi.ThirdName) as PersonFullName
    FROM PersonRole pr
    LEFT JOIN PersonInformation pi ON pi.PersonID = pr.PersonID
    LEFT JOIN Roles r ON r.RoleID = pr.RoleID
    LEFT JOIN PersonQetaa pq ON pq.PersonID = pi.PersonID
    LEFT JOIN Qetaa q ON q.QetaaID = pq.QetaaID
    WHERE q.QetaaName = 'قادة'
    ORDER BY pr.PersonRoleID ASC
");



            //return $personRoles;

            return view("person-role.index", array('personRoles' => $personRoles));
        }

        public function create()
        {
            $khoddam = DB::select("SELECT   pi.PersonID,
                                                CONCAT(pi.ShamandoraCode, ' ', pi.FirstName, ' ', pi.SecondName, ' ', pi.ThirdName) as PersonFullName
                                                FROM PersonInformation pi
                                                LEFT JOIN PersonQetaa pq ON pq.PersonID = pi.PersonID
                                                LEFT JOIN Qetaa q ON q.QetaaID = pq.QetaaID
                                                WHERE q.QetaaName = 'قادة';");

            $roles =  DB::select("  SELECT   r.RoleID, r.RoleName
                                    FROM Roles r");

            return view("person-role.create", array('khoddam'=>$khoddam, 'roles'=>$roles));
        }

        public function insert(Request  $request)
        {
            DB::table('PersonRole')->insert(
                array(
                    'PersonID' => $request -> person_id,
                    'RoleID' => $request -> role_id,
                    'RequestPersonID' => $request -> RequestPersonID,
                )
            );
            return redirect()->route('person-role.index');
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

            $personSelected = DB::table('PersonRole AS pr')
                                        ->select('pi.PersonID', 'pr.PersonRoleID', 'r.RoleName', 'r.RoleID', 
                                                    DB::raw("CONCAT(pi.ShamandoraCode, ' ', pi.FirstName, ' ', pi.SecondName, ' ', pi.ThirdName) as PersonFullName"))
                                        ->leftJoin('PersonInformation AS pi', 'pi.PersonID', '=', 'pr.PersonID')
                                        ->leftJoin('Roles AS r', 'r.RoleID', '=', 'pr.RoleID')
                                        ->where('pr.PersonRoleID', $id)
                                        ->first();
            
            $roles =  DB::select("  SELECT   r.RoleID, r.RoleName
                                    FROM Roles r");
            //return $personSelected;
            return view("person-role.edit", array('personSelected' => $personSelected, 'roles' => $roles));
        }
    
        public function updates(Request $request, $id)
        {
            //$personRole = DB::table('PersonRole')->where('PersonRoleID', $id)->first();

            $affected = DB::table('PersonRole')->where('PersonRoleID', $id)->update(['RoleID' => $request->role_id, 'RequestPersonID' => $request-> RequestPersonID]);

            return redirect()->route('person-role.index');
        }
    
        public function deletes($id)
        {
            $personRole = DB::table('PersonRole AS pr')
    ->select(
        'pr.PersonRoleID',
        'pi.ShamandoraCode',
        'pi.FirstName',
        'pi.SecondName',
        'pi.ThirdName',
        'r.RoleName'
    )
    ->leftJoin('PersonInformation AS pi', 'pi.PersonID', '=', 'pr.PersonID')
    ->leftJoin('Roles AS r', 'r.RoleID', '=', 'pr.RoleID')
    ->where('pr.PersonRoleID', $id)
    ->first();

            return view("person-role.delete", array('personRole' => $personRole));
        }

        public function destroy($id)
        {
            $deleted = DB::table('PersonRole')->where('PersonRoleID',$id)->delete();
            
            return redirect()->route('person-role.index');
        }
}