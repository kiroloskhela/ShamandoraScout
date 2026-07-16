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

class GroupController extends Controller
{
  public function index()
    {
        $groups = DB::table('GroupTable as g1')
            ->leftJoin('GroupType as gt1', 'g1.GroupTypeID', '=', 'gt1.GroupTypeID')
            ->leftJoin('GroupTable as g2', 'g1.IncludedUnderGroupID', '=', 'g2.GroupID')
            ->leftJoin('GroupType as gt2', 'g2.GroupTypeID', '=', 'gt2.GroupTypeID')
            ->select(
                'g1.GroupID as GroupID1',
                'g1.GroupName',
                'gt1.GroupTypeName',
                'g1.IncludedUnderGroupID',
                DB::raw("CONCAT(gt2.GroupTypeName, ' ', g2.GroupName) as IncludedUnderGroupName")
            )
            ->get();

        // 👇 للتأكد من الأعمدة
        // dd($groups);

        return view('group.index', ['groups' => $groups]);
    }


    public function create()
    {
        $groupTypes = DB::table('GroupType')->get();

        $groups = DB::select("
            SELECT g1.GroupID,
                   g1.IncludedUnderGroupID,
                   CONCAT(g3.GroupTypeName, ' ', g1.GroupName,
                          CASE WHEN g2.GroupName IS NOT NULL 
                               THEN CONCAT(' (ضمن ', g2.GroupName, ')') 
                               ELSE '' END
                   ) AS GroupInfo
            FROM GroupTable g1
            LEFT JOIN GroupTable g2 ON g1.IncludedUnderGroupID = g2.GroupID
            LEFT JOIN GroupType g3 ON g1.GroupTypeID = g3.GroupTypeID
        ");

        return view("group.create", ['groupTypes' => $groupTypes, 'groups' => $groups]);
    }

    public function insert(Request $request)
    {
        // GroupTable.GroupID is not AUTO_INCREMENT in production.
        $thisGroupID = \App\Support\ManualPrimaryKey::next('GroupTable', 'GroupID');

        DB::table('GroupTable')->insert([
            'GroupID' => $thisGroupID,
            'GroupName' => $request->group_name,
            'GroupTypeID' => $request->group_type_id,
            'IncludedUnderGroupID' => $request->included_under_group_id
        ]);

        return redirect()->route('group.index');
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        $groupSelected = DB::selectOne("
            SELECT g1.GroupID,
                   g1.GroupName,
                   g1.IncludedUnderGroupID,
                   g3.GroupTypeID,
                   g3.GroupTypeName,
                   CONCAT(g4.GroupTypeName, ' ', g2.GroupName) AS ParentGroupInfo
            FROM GroupTable g1
            LEFT JOIN GroupTable g2 ON g1.IncludedUnderGroupID = g2.GroupID
            LEFT JOIN GroupType g3 ON g1.GroupTypeID = g3.GroupTypeID
            LEFT JOIN GroupType g4 ON g2.GroupTypeID = g4.GroupTypeID
            WHERE g1.GroupID = ?
        ", [$id]);

        $groupTypes = DB::table('GroupType')->get();

        $groups = DB::select("
            SELECT g1.GroupID,
                   g1.IncludedUnderGroupID,
                   CONCAT(g3.GroupTypeName, ' ', g1.GroupName,
                          CASE WHEN g2.GroupName IS NOT NULL 
                               THEN CONCAT(' (ضمن ', g2.GroupName, ')') 
                               ELSE '' END
                   ) AS GroupInfo
            FROM GroupTable g1
            LEFT JOIN GroupTable g2 ON g1.IncludedUnderGroupID = g2.GroupID
            LEFT JOIN GroupType g3 ON g1.GroupTypeID = g3.GroupTypeID
        ");

        return view("group.edit", [
            'groupSelected' => $groupSelected,
            'groupTypes' => $groupTypes,
            'groups' => $groups
        ]);
    }

    public function updates(Request $request, $id)
    {
        DB::table('GroupTable')
            ->where('GroupID', $id)
            ->update([
                'GroupName' => $request->group_name,
                'GroupTypeID' => $request->group_type_id,
                'IncludedUnderGroupID' => $request->included_under_group_id
            ]);

        return redirect()->route('group.index');
    }

    public function deletes($id)
    {
        $group = DB::table('GroupTable')->where('GroupID', $id)->first();
        return view("group.delete", ['group' => $group]);
    }

    public function destroy($id)
    {
        DB::table('GroupTable')->where('GroupID',$id)->delete();
        return redirect()->route('group.index');
    }
}