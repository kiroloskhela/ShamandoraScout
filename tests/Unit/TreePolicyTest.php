<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\TreePolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TreePolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('PersonQetaa');
        Schema::dropIfExists('GroupQetaa');
        Schema::dropIfExists('PersonGroup');
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

        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->increments('PersonGroupID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
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

        Schema::create('GroupQetaa', function (Blueprint $table) {
            $table->increments('GroupQetaaID');
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->increments('PersonQetaaID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });
    }

    public function test_staff_member_cannot_manage_unserved_qetaa(): void
    {
        $user = User::create([
            'FirstName' => 'A',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'ShamandoraCode' => 'T1',
        ]);
        $this->grantStaffRole($user);

        DB::table('PersonGroup')->insert(['PersonID' => $user->PersonID, 'GroupID' => 1]);
        DB::table('GroupQetaa')->insert(['GroupID' => 1, 'QetaaID' => 5]);

        $policy = new TreePolicy;

        $this->assertTrue($policy->manageQetaa($user, 5));
        $this->assertFalse($policy->manageQetaa($user, 99));
    }

    public function test_group_member_without_staff_role_cannot_manage_qetaa(): void
    {
        $user = User::create([
            'FirstName' => 'A',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'ShamandoraCode' => 'T1b',
        ]);

        DB::table('PersonGroup')->insert(['PersonID' => $user->PersonID, 'GroupID' => 1]);
        DB::table('GroupQetaa')->insert(['GroupID' => 1, 'QetaaID' => 5]);

        $this->assertFalse((new TreePolicy)->manageQetaa($user, 5));
    }

    public function test_manage_group_requires_served_qetaa(): void
    {
        $user = User::create([
            'FirstName' => 'A',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'ShamandoraCode' => 'T2',
        ]);
        $this->grantStaffRole($user);

        DB::table('PersonGroup')->insert(['PersonID' => $user->PersonID, 'GroupID' => 2]);
        DB::table('GroupQetaa')->insert(['GroupID' => 2, 'QetaaID' => 7]);
        DB::table('GroupQetaa')->insert(['GroupID' => 9, 'QetaaID' => 99]);

        $policy = new TreePolicy;

        $this->assertTrue($policy->manageGroup($user, 2));
        $this->assertFalse($policy->manageGroup($user, 9));
    }

    public function test_serves_person_requires_group_in_target_qetaa(): void
    {
        $servant = User::create([
            'FirstName' => 'A',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'ShamandoraCode' => 'T3',
        ]);
        $this->grantStaffRole($servant);
        $served = User::create([
            'FirstName' => 'S',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'ShamandoraCode' => 'T4',
        ]);
        $other = User::create([
            'FirstName' => 'O',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'ShamandoraCode' => 'T5',
        ]);

        DB::table('PersonGroup')->insert(['PersonID' => $servant->PersonID, 'GroupID' => 3]);
        DB::table('GroupQetaa')->insert(['GroupID' => 3, 'QetaaID' => 11]);
        DB::table('PersonQetaa')->insert([
            ['PersonID' => $served->PersonID, 'QetaaID' => 11],
            ['PersonID' => $other->PersonID, 'QetaaID' => 22],
        ]);

        $policy = new TreePolicy;

        $this->assertTrue($policy->servesPerson($servant, $served));
        $this->assertFalse($policy->servesPerson($servant, $other));
    }
}
