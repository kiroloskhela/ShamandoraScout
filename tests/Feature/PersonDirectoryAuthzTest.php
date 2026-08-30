<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonDirectoryAuthzTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('GroupQetaa');
        Schema::dropIfExists('PersonGroup');
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
        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->increments('PersonGroupID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
        });
        Schema::create('GroupQetaa', function (Blueprint $table) {
            $table->increments('GroupQetaaID');
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('QetaaID');
        });
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'Admin',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'U'.uniqid(),
        ]);

        $roleId = DB::table('Roles')->insertGetId([
            'RoleName' => $roleName,
            'RoleDescription' => $roleName,
        ]);

        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);

        return $user->fresh();
    }

    public function test_admin_qetaa_cannot_show_person_outside_shared_qetaa(): void
    {
        $admin = $this->userWithRole('AdminQetaa');
        $target = User::create([
            'FirstName' => 'Target',
            'SecondName' => 'Person',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'T1',
        ]);

        DB::table('PersonQetaa')->insert([
            ['PersonID' => $admin->PersonID, 'QetaaID' => 1],
            ['PersonID' => $target->PersonID, 'QetaaID' => 2],
        ]);

        $this->actingAs($admin)
            ->get('/person/show/'.$target->PersonID)
            ->assertForbidden();
    }

    public function test_admin_qetaa_can_show_person_in_shared_qetaa(): void
    {
        $admin = $this->userWithRole('AdminQetaa');
        $target = User::create([
            'FirstName' => 'Target',
            'SecondName' => 'Person',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'T2',
        ]);

        DB::table('PersonQetaa')->insert([
            ['PersonID' => $admin->PersonID, 'QetaaID' => 1],
            ['PersonID' => $target->PersonID, 'QetaaID' => 1],
        ]);

        $response = $this->actingAs($admin)->get('/person/show/'.$target->PersonID);

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_khadem_cannot_show_person_outside_served_qetaa(): void
    {
        $khadem = $this->userWithRole('Khadem');
        $target = User::create([
            'FirstName' => 'Target',
            'SecondName' => 'Person',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'T3',
        ]);

        DB::table('PersonGroup')->insert(['PersonID' => $khadem->PersonID, 'GroupID' => 1]);
        DB::table('GroupQetaa')->insert(['GroupID' => 1, 'QetaaID' => 1]);
        DB::table('PersonQetaa')->insert(['PersonID' => $target->PersonID, 'QetaaID' => 2]);

        $this->actingAs($khadem)
            ->get('/person/show/'.$target->PersonID)
            ->assertForbidden();
    }

    public function test_khadem_can_open_show_for_person_in_served_qetaa(): void
    {
        $khadem = $this->userWithRole('Khadem');
        $target = User::create([
            'FirstName' => 'Target',
            'SecondName' => 'Person',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'T4',
        ]);

        DB::table('PersonGroup')->insert(['PersonID' => $khadem->PersonID, 'GroupID' => 2]);
        DB::table('GroupQetaa')->insert(['GroupID' => 2, 'QetaaID' => 1]);
        DB::table('PersonQetaa')->insert(['PersonID' => $target->PersonID, 'QetaaID' => 1]);

        $response = $this->actingAs($khadem)->get('/person/show/'.$target->PersonID);

        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_mkhdom_cannot_show_another_person(): void
    {
        $mkhdom = $this->userWithRole('Mkhdom');
        $target = User::create([
            'FirstName' => 'Target',
            'SecondName' => 'Person',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'T5',
        ]);

        DB::table('PersonGroup')->insert(['PersonID' => $mkhdom->PersonID, 'GroupID' => 3]);
        DB::table('GroupQetaa')->insert(['GroupID' => 3, 'QetaaID' => 1]);
        DB::table('PersonQetaa')->insert([
            ['PersonID' => $mkhdom->PersonID, 'QetaaID' => 1],
            ['PersonID' => $target->PersonID, 'QetaaID' => 1],
        ]);

        $this->actingAs($mkhdom)
            ->get('/person/show/'.$target->PersonID)
            ->assertRedirect(route('login-auth'));
    }
}
