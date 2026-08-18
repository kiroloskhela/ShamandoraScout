<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PermissionDualRunTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('role_permissions');
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

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('RoleID');
            $table->string('permission_key', 120);
            $table->timestamps();
            $table->unique(['RoleID', 'permission_key']);
        });

        Route::middleware(['web', 'auth', 'checkAuth:AdminQetaa', 'can.permission:web.enrolments.manage'])
            ->get('/__perm-dual-run', fn () => 'ok');
    }

    public function test_check_auth_allows_admin_qetaa_when_enforce_is_off(): void
    {
        config(['permissions.enforce' => false]);

        $this->actingAs($this->userWithRole('AdminQetaa'))
            ->get('/__perm-dual-run')
            ->assertOk();

        $this->actingAs($this->userWithRole('Inventory'))
            ->get('/__perm-dual-run')
            ->assertForbidden();
    }

    public function test_matrix_allows_admin_qetaa_when_enforce_is_on(): void
    {
        config(['permissions.enforce' => true]);

        $admin = $this->userWithRole('AdminQetaa');
        DB::table('role_permissions')->insert([
            'RoleID' => DB::table('Roles')->where('RoleName', 'AdminQetaa')->value('RoleID'),
            'permission_key' => 'web.enrolments.manage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/__perm-dual-run')
            ->assertOk();

        $this->actingAs($this->userWithRole('Inventory'))
            ->get('/__perm-dual-run')
            ->assertForbidden();
    }

    public function test_empty_matrix_denies_when_enforce_is_on(): void
    {
        config(['permissions.enforce' => true]);

        $this->actingAs($this->userWithRole('AdminQetaa'))
            ->get('/__perm-dual-run')
            ->assertForbidden();
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'Dual',
            'SecondName' => $roleName,
            'ThirdName' => 'X',
            'ShamandoraCode' => 'DR'.uniqid(),
        ]);

        $roleId = DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');
        if (! $roleId) {
            $roleId = DB::table('Roles')->insertGetId(['RoleName' => $roleName]);
        }

        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);

        return $user->fresh();
    }
}
