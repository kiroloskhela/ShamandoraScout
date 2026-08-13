<?php

namespace Tests\Feature;

use App\Models\User;
use App\Policies\PersonPolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonPolicyPermissionTest extends TestCase
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
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
        });

        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->increments('PersonQetaaID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });
    }

    public function test_self_can_view_and_update_but_not_delete(): void
    {
        $user = User::create([
            'FirstName' => 'Self',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'S1',
        ]);

        $policy = new PersonPolicy;

        $this->assertTrue($policy->view($user, $user));
        $this->assertTrue($policy->update($user, $user));
        $this->assertFalse($policy->delete($user, $user));
    }

    public function test_admin_qetaa_can_delete_another_person_in_shared_qetaa(): void
    {
        $admin = User::create([
            'FirstName' => 'Admin',
            'SecondName' => 'Q',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'AQ1',
        ]);
        $target = User::create([
            'FirstName' => 'Target',
            'SecondName' => 'P',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'T1',
        ]);

        $roleId = DB::table('Roles')->insertGetId(['RoleName' => 'AdminQetaa']);
        DB::table('PersonRole')->insert([
            'PersonID' => $admin->PersonID,
            'RoleID' => $roleId,
        ]);
        DB::table('PersonQetaa')->insert([
            ['PersonID' => $admin->PersonID, 'QetaaID' => 1],
            ['PersonID' => $target->PersonID, 'QetaaID' => 1],
        ]);

        $policy = new PersonPolicy;
        $admin = $admin->fresh();

        $this->assertTrue($policy->view($admin, $target));
        $this->assertTrue($policy->delete($admin, $target));
    }

    public function test_admin_qetaa_cannot_delete_a_superadmin(): void
    {
        $admin = User::create([
            'FirstName' => 'Admin',
            'SecondName' => 'Q',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'AQ2',
        ]);
        $target = User::create([
            'FirstName' => 'Root',
            'SecondName' => 'Admin',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'SA1',
        ]);

        $aqId = DB::table('Roles')->insertGetId(['RoleName' => 'AdminQetaa']);
        $saId = DB::table('Roles')->insertGetId(['RoleName' => 'SuperAdmin']);
        DB::table('PersonRole')->insert([
            ['PersonID' => $admin->PersonID, 'RoleID' => $aqId],
            ['PersonID' => $target->PersonID, 'RoleID' => $saId],
        ]);
        DB::table('PersonQetaa')->insert([
            ['PersonID' => $admin->PersonID, 'QetaaID' => 1],
            ['PersonID' => $target->PersonID, 'QetaaID' => 1],
        ]);

        $this->assertFalse((new PersonPolicy)->delete($admin->fresh(), $target->fresh()));
    }
}
