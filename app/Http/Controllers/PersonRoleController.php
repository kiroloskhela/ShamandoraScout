<?php

namespace App\Http\Controllers;

use App\Domain\Auth\TokenSessionService;
use App\Domain\Authz\PermissionService;
use App\Domain\Authz\SuperAdminGuard;
use App\Support\ManualPrimaryKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PersonRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {

        $personRoles = DB::select(" SELECT pi.PersonID, pi.ShamandoraCode, pr.PersonRoleID, r.RoleName,
           (SELECT q.QetaaName FROM PersonQetaa pq
            JOIN Qetaa q ON q.QetaaID = pq.QetaaID
            WHERE pq.PersonID = pi.PersonID
            LIMIT 1) as QetaaName,
           r.RoleID,
           CONCAT(pi.FirstName, ' ', pi.SecondName, ' ', pi.ThirdName) as PersonFullName
    FROM PersonRole pr
    LEFT JOIN PersonInformation pi ON pi.PersonID = pr.PersonID
    LEFT JOIN Roles r ON r.RoleID = pr.RoleID
    ORDER BY pr.PersonRoleID ASC
");

        // return $personRoles;

        return view('person-role.index', ['personRoles' => $personRoles]);
    }

    public function create()
    {
        $khoddam = DB::select("SELECT   pi.PersonID,
                                                CONCAT(pi.ShamandoraCode, ' ', pi.FirstName, ' ', pi.SecondName, ' ', pi.ThirdName) as PersonFullName
                                                FROM PersonInformation pi
                                                ORDER BY pi.PersonID");

        $roles = DB::select('  SELECT   r.RoleID, r.RoleName
                                    FROM Roles r');

        return view('person-role.create', ['khoddam' => $khoddam, 'roles' => $roles]);
    }

    public function insert(Request $request)
    {
        $request->validate([
            'person_id' => 'required|integer|exists:PersonInformation,PersonID',
            'role_id' => 'required|integer|exists:Roles,RoleID',
            'RequestPersonID' => 'nullable|integer|exists:PersonInformation,PersonID',
        ]);

        try {
            DB::transaction(function () use ($request) {
                app(SuperAdminGuard::class)->assertPersonRoleChangeAllowed(
                    null,
                    (int) $request->role_id,
                    $request->user()
                );
                $this->assertRoleAssignableToPerson((int) $request->person_id, (int) $request->role_id);

                // PersonRole.PersonRoleID is not AUTO_INCREMENT in production.
                $thisPersonRoleID = ManualPrimaryKey::next('PersonRole', 'PersonRoleID');

                DB::table('PersonRole')->insert([
                    'PersonRoleID' => $thisPersonRoleID,
                    'PersonID' => $request->person_id,
                    'RoleID' => $request->role_id,
                    'RequestPersonID' => $request->RequestPersonID,
                ]);

                app(PermissionService::class)->bumpVersion();
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            abort(403, $e->getMessage());
        }

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

        $roles = DB::select('  SELECT   r.RoleID, r.RoleName
                                    FROM Roles r');

        // return $personSelected;
        return view('person-role.edit', ['personSelected' => $personSelected, 'roles' => $roles]);
    }

    public function updates(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|integer|exists:Roles,RoleID',
            'RequestPersonID' => 'nullable|integer|exists:PersonInformation,PersonID',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $row = DB::table('PersonRole')->where('PersonRoleID', $id)->lockForUpdate()->first();
                if (! $row) {
                    abort(404);
                }

                app(SuperAdminGuard::class)->assertPersonRoleChangeAllowed(
                    (int) $row->RoleID,
                    (int) $request->role_id,
                    $request->user()
                );
                $this->assertRoleAssignableToPerson((int) $row->PersonID, (int) $request->role_id);

                DB::table('PersonRole')->where('PersonRoleID', $id)->update([
                    'RoleID' => $request->role_id,
                    'RequestPersonID' => $request->RequestPersonID,
                ]);

                app(PermissionService::class)->bumpVersion();
                app(TokenSessionService::class)->revokeIfNoAppAccess((int) $row->PersonID);
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            abort(403, $e->getMessage());
        }

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

        return view('person-role.delete', ['personRole' => $personRole]);
    }

    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $row = DB::table('PersonRole')->where('PersonRoleID', $id)->lockForUpdate()->first();
                if (! $row) {
                    abort(404);
                }

                app(SuperAdminGuard::class)->assertPersonRoleDeleteAllowed((int) $row->RoleID);
                $personId = (int) $row->PersonID;
                DB::table('PersonRole')->where('PersonRoleID', $id)->delete();
                app(PermissionService::class)->bumpVersion();
                app(TokenSessionService::class)->revokeIfNoAppAccess($personId);
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (RuntimeException $e) {
            abort(403, $e->getMessage());
        }

        return redirect()->route('person-role.index');
    }

    private function assertRoleAssignableToPerson(int $personId, int $roleId): void
    {
        $roleName = (string) DB::table('Roles')->where('RoleID', $roleId)->value('RoleName');
        if ($roleName === '') {
            abort(404, 'Role not found.');
        }
        if ($roleName === 'Mkhdom') {
            return;
        }

        $isLeader = DB::table('PersonQetaa as pq')
            ->join('Qetaa as q', 'q.QetaaID', '=', 'pq.QetaaID')
            ->where('pq.PersonID', $personId)
            ->where('q.QetaaName', 'قادة')
            ->exists();

        if (! $isLeader) {
            abort(403, 'Staff roles can only be assigned to people in قادة.');
        }
    }
}
