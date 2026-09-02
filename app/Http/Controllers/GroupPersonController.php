<?php

namespace App\Http\Controllers;

use App\Domain\Enrolment\LiveFormQetaaResolver;
use App\Domain\OrgTree\GroupTreeService;
use App\Support\LikeSearch;
use App\Support\SqlPaginator;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GroupPersonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $bindings = [];
        $searchSql = '';
        $term = LikeSearch::fromRequest($request);
        if ($term !== null) {
            $fragment = LikeSearch::sqlFlexibleOr(
                [
                    'PersonInformation.ShamandoraCode',
                    'PersonInformation.FirstName',
                    'PersonInformation.SecondName',
                    'PersonInformation.ThirdName',
                    'GroupRole.GroupRoleName',
                    'GroupTable.GroupName',
                    'GroupType.GroupTypeName',
                    'CAST(PersonGroup.PersonID AS CHAR)',
                    'CAST(PersonGroup.GroupID AS CHAR)',
                ],
                $term,
                LikeSearch::personPhoneColumns('ppn'),
            );
            $searchSql = ' WHERE '.$fragment['sql'];
            $bindings = $fragment['bindings'];
        }

        $sql = "
            SELECT PersonGroup.PersonGroupRoleID,
                PersonGroup.PersonID,
                PersonGroup.GroupID,
                PersonGroup.GroupRoleID,
                PersonInformation.ShamandoraCode,
                CONCAT(PersonInformation.FirstName, ' ',
                PersonInformation.SecondName, ' ', PersonInformation.ThirdName) AS PersonFullName,
                GroupRole.GroupRoleName,
                CONCAT(GroupType.GroupTypeName, ' ', GroupTable.GroupName) AS GroupDetails
            FROM PersonGroup
            LEFT JOIN PersonInformation ON PersonGroup.PersonID = PersonInformation.PersonID
            LEFT JOIN PersonPhoneNumbers ppn ON ppn.PersonID = PersonInformation.PersonID
            LEFT JOIN GroupTable ON GroupTable.GroupID = PersonGroup.GroupID
            LEFT JOIN GroupRole ON GroupRole.GroupRoleID = PersonGroup.GroupRoleID
            LEFT JOIN GroupType ON GroupTable.GroupTypeID = GroupType.GroupTypeID
            {$searchSql}
            ORDER BY PersonInformation.ShamandoraCode ASC
        ";

        $groupPersons = SqlPaginator::paginate($sql, $bindings, 25);

        return view('group-person.index', [
            'groupPersons' => $groupPersons,
        ]);
    }

    public function createKhadem()
    {

        $groupsResult = DB::select("SELECT g1.GroupID,  
                                    CONCAT('مجموعة رقم: ', g1.GroupID, ' - ',g3.GroupTypeName, ' ', g1.GroupName, ' -> ', g4.GroupTypeName, ' ', g2.GroupName) AS GroupInfo   
                                    FROM GroupTable g1
                                    LEFT JOIN GroupTable g2 ON g1.IncludedUnderGroupID = g2.GroupID
                                    LEFT JOIN GroupType g3 ON g1.GroupTypeID = g3.GroupTypeID
                                    LEFT JOIN GroupType g4 ON g2.GroupTypeID = g4.GroupTypeID
                                    WHERE g1.GroupID > 0
                                    ");

        $groups = [];
        foreach ($groupsResult as $g) {
            $object = new \stdClass;
            $object->GroupID = $g->GroupID;
            $object->GroupInfo = $this->getParentsPathString($g->GroupID);
            array_push($groups, $object);
        }

        $leaderQetaaIds = LiveFormQetaaResolver::LEADER_QETAA_IDS;
        $placeholders = implode(',', array_fill(0, count($leaderQetaaIds), '?'));
        $persons = DB::select("SELECT DISTINCT PersonInformation.PersonID, PersonInformation.ShamandoraCode, 
                                        CONCAT(PersonInformation.ShamandoraCode, ' ', PersonInformation.FirstName, ' ', PersonInformation.SecondName, ' ', PersonInformation.ThirdName) AS FullName
                                    FROM PersonInformation
                                    INNER JOIN PersonQetaa ON PersonInformation.PersonID = PersonQetaa.PersonID
                                    WHERE PersonQetaa.QetaaID IN ({$placeholders})", $leaderQetaaIds);
        $groupRoles = DB::select('SELECT * FROM GroupRole WHERE isKhademRole = 1');

        $isKhadem = true;

        return view('group-person.create', ['groups' => $groups, 'persons' => $persons, 'groupRoles' => $groupRoles, 'isKhadem' => $isKhadem]);
    }

    public function createMakhdoom()
    {
        // Fetch all groups (courses) as per createKhadem, but ensure they are relevant for makhdoom
        $groupsResult = DB::select("SELECT g1.GroupID,  
                                    CONCAT('مجموعة رقم: ', g1.GroupID, ' - ',g3.GroupTypeName, ' ', g1.GroupName, ' -> ', g4.GroupTypeName, ' ', g2.GroupName) AS GroupInfo   
                                    FROM GroupTable g1
                                    LEFT JOIN GroupTable g2 ON g1.IncludedUnderGroupID = g2.GroupID
                                    LEFT JOIN GroupType g3 ON g1.GroupTypeID = g3.GroupTypeID
                                    LEFT JOIN GroupType g4 ON g2.GroupTypeID = g4.GroupTypeID
                                    WHERE g1.GroupID > 0
                                    ");

        $groups = [];
        foreach ($groupsResult as $g) {
            $object = new \stdClass;
            $object->GroupID = $g->GroupID;
            $object->GroupInfo = $this->getParentsPathString($g->GroupID);
            array_push($groups, $object);
        }

        // Fetch all persons (students) without initial filtering, as user will select who attends
        $persons = DB::select("SELECT PersonInformation.PersonID, PersonInformation.ShamandoraCode, 
                                        CONCAT(PersonInformation.ShamandoraCode, ' ', PersonInformation.FirstName, ' ', PersonInformation.SecondName, ' ', PersonInformation.ThirdName) AS FullName
                                    FROM PersonInformation
                                    ");

        // Fetch group roles relevant for makhdoom (students)
        $groupRoles = DB::select('SELECT GroupRole.GroupRoleID, GroupRole.GroupRoleName
                                            From GroupRole
                                            WHERE GroupRole.isKhademRole = 0');

        $isKhadem = false;

        return view('group-person.create', ['groups' => $groups, 'persons' => $persons, 'groupRoles' => $groupRoles, 'isKhadem' => $isKhadem]);
    }

    public function insert(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'group_id' => 'required',
            'person_id' => 'required',
            'group_role_id' => 'required',
        ]);

        if ($validator->fails()) {
            return view('person.entry-error-repeat-trial');
        }

        try {

            DB::beginTransaction();

            foreach ($request->person_id as $personID) {
                DB::table('PersonGroup')->insert(
                    [
                        'PersonID' => $personID,
                        'GroupID' => $request->group_id,
                        'GroupRoleID' => $request->group_role_id,
                    ]
                );
            }
        } catch (Exception $e) {
            dd($e->getMessage());
            DB::rollBack();

            return view('person.entry-error');
        }

        DB::commit();

        return redirect()->route('group-person.index');
    }

    public function edit($id)
    {
        // Here $id = PersonID (not PersonGroupRoleID)
        $personGroupRoleRow = DB::table('PersonGroup')->where('PersonID', $id)->first();
        if (! $personGroupRoleRow) {
            abort(404, 'No PersonGroup found for this person');
        }

        // Check if Khadem role
        $isKhadem = DB::table('GroupRole')
            ->where('GroupRoleID', $personGroupRoleRow->GroupRoleID)
            ->value('isKhademRole') == 1;

        // Person info
        $person = DB::table('PersonInformation')
            ->selectRaw("PersonID, CONCAT(ShamandoraCode, ' ', FirstName, ' ', SecondName, ' ', ThirdName, ' ', FourthName) AS FullName")
            ->where('PersonID', $personGroupRoleRow->PersonID)
            ->first();

        // Selected group
        $selectedGroup = DB::table('GroupTable as g')
            ->leftJoin('GroupType as gt', 'g.GroupTypeID', '=', 'gt.GroupTypeID')
            ->selectRaw("g.GroupID, CONCAT(gt.GroupTypeName, ' ', g.GroupName) AS GroupInfo")
            ->where('g.GroupID', $personGroupRoleRow->GroupID)
            ->first();

        // Selected role
        $selectedGroupRole = DB::table('GroupRole')
            ->where('GroupRoleID', $personGroupRoleRow->GroupRoleID)
            ->first();

        if (! $isKhadem) {
            // Roles (non-Khadem)
            $groupRoles = DB::table('GroupRole')->where('isKhademRole', 0)->get();

            // Groups under authenticated Khadem
            $khademAuthenticatedID = Auth::user()->PersonID;
            $directGroupsConnectedToKhadem = DB::table('PersonGroup')
                ->where('PersonID', $khademAuthenticatedID)
                ->pluck('GroupID');

            $groups = collect();

            if ($directGroupsConnectedToKhadem->isNotEmpty()) {
                $allGroupsIDsBelowKhadem = [];
                foreach ($directGroupsConnectedToKhadem as $groupConnected) {
                    $allGroupsIDsBelowKhadem = array_merge(
                        $allGroupsIDsBelowKhadem,
                        $this->getNodesBelow($groupConnected, [$groupConnected])
                    );
                }

                foreach ($allGroupsIDsBelowKhadem as $g) {
                    $groups->push((object) [
                        'GroupID' => $g,
                        'GroupInfo' => $this->getParentsPathString($g),
                    ]);
                }
            }
        } else {
            // Roles (Khadem)
            $groupRoles = DB::table('GroupRole')->where('isKhademRole', 1)->get();

            // All groups
            $groups = DB::table('GroupTable as g')
                ->leftJoin('GroupType as gt', 'g.GroupTypeID', '=', 'gt.GroupTypeID')
                ->selectRaw("g.GroupID, CONCAT(gt.GroupTypeName, ' ', g.GroupName) AS GroupInfo")
                ->get();
        }

        return view('group-person.edit', [
            'groupRoles' => $groupRoles,
            'person' => $person,
            'selectedGroup' => $selectedGroup,
            'groups' => $groups,
            'personGroupRoleRow' => $personGroupRoleRow,
            'isKhadem' => $isKhadem,
            'selectedGroupRole' => $selectedGroupRole,
        ]);
    }

    public function updates(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required',
            'group_role_id' => 'required',
        ]);

        if ($validator->fails()) {
            return view('person.entry-error-repeat-trial');
        }

        DB::table('PersonGroup')
            ->where('PersonGroupRoleID', $id)
            ->update([
                'GroupID' => $request->group_id,
                'GroupRoleID' => $request->group_role_id,
            ]);

        return redirect()->route('group-person.index');
    }

    public function deletes(Request $request, $personId) // personId passed, not PersonGroupID
    {
        // Load person (for header info)
        $person = DB::table('PersonInformation')
            ->select(
                'PersonID',
                'ShamandoraCode',
                DB::raw("CONCAT(FirstName,' ',SecondName,' ',ThirdName,' ',FourthName) AS FullName")
            )
            ->where('PersonID', $personId)
            ->first();

        abort_if(! $person, 404, 'Person not found.');

        // Load all links for this person
        $links = DB::table('PersonGroup as pg')
            ->leftJoin('GroupTable as g', 'g.GroupID', '=', 'pg.GroupID')
            ->leftJoin('GroupType as gt', 'gt.GroupTypeID', '=', 'g.GroupTypeID')
            ->leftJoin('GroupRole as gr', 'gr.GroupRoleID', '=', 'pg.GroupRoleID') // adjust table name if different
            ->where('pg.PersonID', $personId)
            ->select(
                'pg.PersonGroupRoleID',
                'pg.PersonID',
                'pg.GroupID',
                DB::raw("COALESCE(gr.GroupRoleName, '—') as GroupRoleName"),
                DB::raw("CONCAT(COALESCE(gt.GroupTypeName,''),' ',COALESCE(g.GroupName,'')) as GroupInfo")
            )
            ->get();

        // If no links, 404
        abort_if($links->isEmpty(), 404, 'No group links found for this person.');

        // If caller already specified group_id, short-circuit to confirm that exact one
        $selectedGroupId = $request->query('group_id');
        if ($selectedGroupId) {
            $row = $links->firstWhere('GroupID', (int) $selectedGroupId);
            abort_if(! $row, 404, 'Selected group link not found for this person.');

            return view('group-person.delete', [
                'person' => $person,
                'links' => collect([$row]), // single-row confirm
                'multiple' => false,
            ]);
        }

        // If one link only → simple confirm
        if ($links->count() === 1) {
            return view('group-person.delete', [
                'person' => $person,
                'links' => $links, // one row
                'multiple' => false,
            ]);
        }

        // Multiple links → show chooser (each delete submits with group_id)
        return view('group-person.delete', [
            'person' => $person,
            'links' => $links,
            'multiple' => true,
        ]);
    }

    public function destroy(Request $request, $personId) // personId passed, not PersonGroupID
    {
        $groupId = $request->input('group_id'); // optional disambiguator

        try {
            DB::beginTransaction();

            if ($groupId) {
                // Delete the specific link for this person+group
                $deleted = DB::table('PersonGroup')
                    ->where('PersonID', $personId)
                    ->where('GroupID', $groupId)
                    ->delete();

                if (! $deleted) {
                    DB::rollBack();

                    return redirect()->route('group-person.index')
                        ->with('error', 'No record deleted. Person/group link not found.');
                }

                DB::commit();

                return redirect()->route('group-person.index')
                    ->with('status', 'Record deleted successfully.');
            }

            // No group_id provided → check how many links exist
            $links = DB::table('PersonGroup')
                ->where('PersonID', $personId)
                ->select('PersonGroupRoleID', 'GroupID')
                ->get();

            if ($links->isEmpty()) {
                DB::rollBack();

                return redirect()->route('group-person.index')
                    ->with('error', 'No links found for this person.');
            }

            if ($links->count() > 1) {
                DB::rollBack();

                // Ask the user to choose; redirect back to the confirm page that lists options
                return redirect()
                    ->route('group-person.delete', ['id' => $personId])
                    ->with('error', 'Multiple links found. Please select which group to delete.');
            }

            // Exactly one link → delete it
            $deleted = DB::table('PersonGroup')
                ->where('PersonGroupRoleID', $links->first()->PersonGroupRoleID)
                ->delete();

            if (! $deleted) {
                DB::rollBack();

                return redirect()->route('group-person.index')
                    ->with('error', 'No record deleted. Link vanished?');
            }

            DB::commit();

            return redirect()->route('group-person.index')
                ->with('status', 'Record deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('group-person.index')
                ->with('error', 'Unexpected error while deleting.');
        }
    }

    private function getNodesBelow($groupID, $orgIDs)
    {
        if ($groupID == null) {
            return null;
        }

        $tree = app(GroupTreeService::class);

        return array_values(array_unique(array_merge($orgIDs, $tree->nodesBelow((int) $groupID))));
    }

    private function getParentsPathString($groupID)
    {
        return app(GroupTreeService::class)->parentsPathString((int) $groupID);
    }

    private function getLatestParentBeforeRoot($groupID)
    {
        $parentID = DB::selectOne(
            'SELECT GroupTable.IncludedUnderGroupID FROM GroupTable WHERE GroupTable.GroupID = ?',
            [$groupID]
        )->IncludedUnderGroupID;
        if ($parentID == 0) {
            return $groupID;
        }

        return $this->getLatestParentBeforeRoot($parentID);
    }
}
