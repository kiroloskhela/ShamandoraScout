<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\PersonPolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('PersonQetaa');
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
            $table->text('RoleDescription')->nullable();
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
            $table->unsignedInteger('RequestPersonID')->nullable();
        });

        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->increments('PersonQetaaID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });
    }

    private function userWithRole(?string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'A',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'ShamandoraCode' => 'U'.uniqid(),
        ]);

        if ($roleName) {
            $roleId = DB::table('Roles')->insertGetId([
                'RoleName' => $roleName,
                'RoleDescription' => $roleName,
            ]);
            DB::table('PersonRole')->insert([
                'PersonID' => $user->PersonID,
                'RoleID' => $roleId,
            ]);
        }

        return $user->fresh();
    }

    public function test_owner_can_view_self(): void
    {
        $user = $this->userWithRole(null);
        $this->assertTrue((new PersonPolicy())->view($user, $user));
    }

    public function test_regular_user_cannot_view_other(): void
    {
        $a = $this->userWithRole(null);
        $b = $this->userWithRole(null);
        $this->assertFalse((new PersonPolicy())->view($a, $b));
    }

    public function test_super_admin_can_view_other(): void
    {
        $admin = $this->userWithRole('SuperAdmin');
        $other = $this->userWithRole(null);
        $this->assertTrue((new PersonPolicy())->view($admin, $other));
    }
}
