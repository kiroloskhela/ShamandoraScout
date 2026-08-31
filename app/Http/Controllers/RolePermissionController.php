<?php

namespace App\Http\Controllers;

use App\Domain\Authz\PermissionService;
use App\Support\LookupCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RolePermissionController extends Controller
{
    public function __construct(private PermissionService $permissions) {}

    public function edit(Request $request)
    {
        $roles = LookupCache::ordered('Roles', 'RoleName');
        $selectedId = (int) $request->query('role_id', $roles->first()->RoleID ?? 0);
        $selected = $roles->firstWhere('RoleID', $selectedId) ?? $roles->first();

        $granted = [];
        if ($selected) {
            $granted = DB::table('role_permissions')
                ->where('RoleID', $selected->RoleID)
                ->pluck('permission_key')
                ->all();
        }

        $catalog = collect(config('permissions.keys', []))->map(function (array $meta, string $key) use ($granted) {
            return [
                'key' => $key,
                'label' => $meta['label'] ?? $key,
                'platform' => $meta['platform'] ?? 'web',
                'danger' => (bool) ($meta['danger'] ?? false),
                'granted' => in_array($key, $granted, true),
            ];
        })->groupBy('platform');

        return view('role-permissions.edit', [
            'roles' => $roles,
            'selected' => $selected,
            'catalog' => $catalog,
            'authVersion' => $this->permissions->version(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'role_id' => 'required|integer',
            'auth_version' => 'required|integer',
            'password' => 'required|string',
            'keys' => 'array',
            'keys.*' => 'string',
        ]);

        $user = $request->user();
        $hashed = optional($user->password)->Password;
        if (! $hashed || ! Hash::check($data['password'], $hashed)) {
            throw ValidationException::withMessages(['password' => 'Password confirmation failed.']);
        }

        $incoming = array_values(array_unique($data['keys'] ?? []));
        foreach ($incoming as $key) {
            if (! $this->permissions->isGrantable($key)) {
                throw ValidationException::withMessages([
                    'keys' => "Permission [{$key}] is not grantable.",
                ]);
            }
        }

        $role = null;
        $before = [];

        DB::transaction(function () use ($data, $incoming, &$role, &$before) {
            $role = DB::table('Roles')->where('RoleID', $data['role_id'])->lockForUpdate()->first();
            if (! $role) {
                abort(404);
            }

            if (($role->RoleName ?? '') === 'SuperAdmin') {
                throw ValidationException::withMessages([
                    'role_id' => 'SuperAdmin always has every grantable permission. It is not edited in the matrix.',
                ]);
            }

            if ((int) $data['auth_version'] !== $this->permissions->version()) {
                throw ValidationException::withMessages([
                    'auth_version' => 'This matrix was changed by someone else. Reload and try again.',
                ]);
            }

            $before = DB::table('role_permissions')
                ->where('RoleID', $role->RoleID)
                ->pluck('permission_key')
                ->sort()
                ->values()
                ->all();

            DB::table('role_permissions')->where('RoleID', $role->RoleID)->delete();
            $now = now();
            foreach ($incoming as $key) {
                DB::table('role_permissions')->insert([
                    'RoleID' => $role->RoleID,
                    'permission_key' => $key,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
        $this->permissions->bumpVersion();

        logger()->info('authz.matrix_updated', [
            'actor_id' => $user->PersonID,
            'role_id' => $role->RoleID,
            'role_name' => $role->RoleName,
            'before' => $before,
            'after' => $incoming,
            'ip' => $request->ip(),
        ]);

        return redirect()
            ->route('role-permissions.edit', ['role_id' => $role->RoleID])
            ->with('status', 'Permissions saved for '.$role->RoleName);
    }
}
