<?php

namespace Tests\Unit;

use App\Domain\Authz\SuperAdminGuard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class SuperAdminGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('PersonInformation');

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('ShamandoraCode')->nullable();
        });

        Schema::create('Roles', function (Blueprint $table) {
            $table->increments('RoleID');
            $table->string('RoleName');
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
        });

        DB::table('Roles')->insert([
            ['RoleID' => 1, 'RoleName' => 'SuperAdmin'],
            ['RoleID' => 2, 'RoleName' => 'Finance'],
        ]);
    }

    public function test_superadmin_role_row_cannot_be_renamed_or_deleted(): void
    {
        $guard = new SuperAdminGuard;

        $this->expectException(RuntimeException::class);
        $guard->assertRoleRowMutable(1, 'NotSuperAdmin');
    }

    public function test_superadmin_role_row_cannot_be_edited_even_with_same_name(): void
    {
        $this->expectException(RuntimeException::class);
        (new SuperAdminGuard)->assertRoleRowMutable(1, 'SuperAdmin');
    }

    public function test_other_roles_remain_mutable(): void
    {
        (new SuperAdminGuard)->assertRoleRowMutable(2, 'Treasurer');
        $this->assertTrue(true);
    }

    public function test_last_superadmin_assignment_cannot_be_removed(): void
    {
        DB::table('PersonRole')->insert([
            'PersonID' => 10,
            'RoleID' => 1,
        ]);

        $this->expectException(RuntimeException::class);
        (new SuperAdminGuard)->assertPersonRoleDeleteAllowed(1);
    }

    public function test_superadmin_assignment_can_be_removed_when_another_remains(): void
    {
        DB::table('PersonRole')->insert([
            ['PersonID' => 10, 'RoleID' => 1],
            ['PersonID' => 11, 'RoleID' => 1],
        ]);

        (new SuperAdminGuard)->assertPersonRoleDeleteAllowed(1);
        $this->assertTrue(true);
    }

    public function test_non_superadmin_cannot_assign_superadmin(): void
    {
        $actor = \App\Models\User::create([
            'FirstName' => 'Fin',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'FIN1',
        ]);
        DB::table('PersonRole')->insert([
            'PersonID' => $actor->PersonID,
            'RoleID' => 2,
        ]);

        $this->expectException(RuntimeException::class);
        (new SuperAdminGuard)->assertPersonRoleChangeAllowed(null, 1, $actor->fresh());
    }

    public function test_superadmin_can_assign_superadmin(): void
    {
        $actor = \App\Models\User::create([
            'FirstName' => 'Root',
            'SecondName' => 'Admin',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'SA1',
        ]);
        DB::table('PersonRole')->insert([
            'PersonID' => $actor->PersonID,
            'RoleID' => 1,
        ]);

        (new SuperAdminGuard)->assertPersonRoleChangeAllowed(null, 1, $actor->fresh());
        $this->assertTrue(true);
    }

    public function test_cannot_rename_another_role_to_superadmin(): void
    {
        $this->expectException(RuntimeException::class);
        (new SuperAdminGuard)->assertRoleRowMutable(2, 'SuperAdmin');
    }

    public function test_cannot_create_a_role_named_superadmin(): void
    {
        $this->expectException(RuntimeException::class);
        (new SuperAdminGuard)->assertRoleNameAllowed('SuperAdmin');
    }
}
