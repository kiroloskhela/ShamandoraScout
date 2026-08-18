<?php

namespace App\Http\Controllers;

use App\Domain\Authz\SuperAdminGuard;
use App\Http\Requests\LookupStoreRequest;
use App\Http\Requests\LookupUpdateRequest;
use RuntimeException;

class RoleController extends LookupTableController
{
    protected string $lookupKey = 'role';

    public function insert(LookupStoreRequest $request)
    {
        try {
            app(SuperAdminGuard::class)->assertRoleNameAllowed((string) $request->input('role_name'));
        } catch (RuntimeException $e) {
            abort(403, $e->getMessage());
        }

        return parent::insert($request);
    }

    public function updates(LookupUpdateRequest $request, $id)
    {
        try {
            app(SuperAdminGuard::class)->assertRoleRowMutable((int) $id, $request->input('role_name'));
        } catch (RuntimeException $e) {
            abort(403, $e->getMessage());
        }

        return parent::updates($request, $id);
    }

    public function destroy($id)
    {
        try {
            app(SuperAdminGuard::class)->assertRoleRowMutable((int) $id);
        } catch (RuntimeException $e) {
            abort(403, $e->getMessage());
        }

        return parent::destroy($id);
    }
}
