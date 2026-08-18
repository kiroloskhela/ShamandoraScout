<?php

namespace Tests\Feature;

use App\Http\Controllers\PersonRoleController;
use App\Http\Controllers\RoleController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class SuperAdminLockControllerTest extends TestCase
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
            $table->string('RoleDescription')->nullable();
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
            $table->unsignedInteger('RequestPersonID')->nullable();
        });

        DB::table('Roles')->insert([
            ['RoleID' => 1, 'RoleName' => 'SuperAdmin', 'RoleDescription' => ''],
            ['RoleID' => 2, 'RoleName' => 'Finance', 'RoleDescription' => ''],
        ]);
    }

    public function test_superadmin_role_cannot_be_deleted_via_controller(): void
    {
        try {
            app(RoleController::class)->destroy(1);
            $this->fail('Expected 403');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertDatabaseHas('Roles', ['RoleID' => 1, 'RoleName' => 'SuperAdmin']);
    }

    public function test_last_superadmin_person_role_cannot_be_deleted(): void
    {
        $user = User::create([
            'FirstName' => 'Only',
            'SecondName' => 'Admin',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'ONLY1',
        ]);

        $personRoleId = DB::table('PersonRole')->insertGetId([
            'PersonID' => $user->PersonID,
            'RoleID' => 1,
        ]);

        try {
            app(PersonRoleController::class)->destroy($personRoleId);
            $this->fail('Expected 403');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertDatabaseHas('PersonRole', ['PersonRoleID' => $personRoleId, 'RoleID' => 1]);
    }
}
