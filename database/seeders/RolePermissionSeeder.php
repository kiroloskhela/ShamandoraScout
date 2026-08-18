<?php

namespace Database\Seeders;

use App\Domain\Authz\PermissionService;
use App\Support\ManualPrimaryKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureMkhdomRole();

        $seed = config('permissions.seed', []);
        $now = now();

        foreach ($seed as $roleName => $keys) {
            $roleId = DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');
            if (! $roleId) {
                continue;
            }

            foreach ($keys as $key) {
                DB::table('role_permissions')->insertOrIgnore([
                    'RoleID' => $roleId,
                    'permission_key' => $key,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        app(PermissionService::class)->bumpVersion();
    }

    private function ensureMkhdomRole(): void
    {
        if (DB::table('Roles')->where('RoleName', 'Mkhdom')->exists()) {
            return;
        }

        $row = [
            'RoleID' => ManualPrimaryKey::next('Roles', 'RoleID'),
            'RoleName' => 'Mkhdom',
        ];
        if (Schema::hasColumn('Roles', 'RoleDescription')) {
            $row['RoleDescription'] = 'Served person (own-only mobile access)';
        }

        DB::table('Roles')->insert($row);
    }
}
