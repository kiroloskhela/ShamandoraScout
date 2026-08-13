<?php

namespace Database\Seeders;

use App\Domain\Authz\PermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $seed = config('permissions.seed', []);
        $now = now();

        foreach ($seed as $roleName => $keys) {
            $roleId = DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');
            if (! $roleId) {
                continue;
            }

            foreach ($keys as $key) {
                $exists = DB::table('role_permissions')
                    ->where('RoleID', $roleId)
                    ->where('permission_key', $key)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('role_permissions')->insert([
                    'RoleID' => $roleId,
                    'permission_key' => $key,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        app(PermissionService::class)->bumpVersion();
    }
}
