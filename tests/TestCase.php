<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function grantStaffRole(\App\Models\User $user, string $roleName = 'Khadem'): void
    {
        $roleId = \Illuminate\Support\Facades\DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');
        if (! $roleId) {
            $row = ['RoleName' => $roleName];
            if (\Illuminate\Support\Facades\Schema::hasColumn('Roles', 'RoleDescription')) {
                $row['RoleDescription'] = $roleName;
            }
            $roleId = \Illuminate\Support\Facades\DB::table('Roles')->insertGetId($row);
        }

        \Illuminate\Support\Facades\DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);
    }
}
