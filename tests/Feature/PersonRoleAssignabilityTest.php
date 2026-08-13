<?php

namespace Tests\Feature;

use App\Http\Controllers\PersonRoleController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class PersonRoleAssignabilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['PersonQetaa', 'Qetaa', 'PersonRole', 'Roles', 'PersonInformation'] as $table) {
            Schema::dropIfExists($table);
        }

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
            $table->unsignedInteger('RequestPersonID')->nullable();
        });
        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName');
        });
        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->increments('PersonQetaaID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });

        DB::table('Roles')->insert([
            ['RoleID' => 1, 'RoleName' => 'SuperAdmin'],
            ['RoleID' => 2, 'RoleName' => 'Khadem'],
            ['RoleID' => 3, 'RoleName' => 'Mkhdom'],
        ]);
        DB::table('Qetaa')->insert([
            ['QetaaID' => 1, 'QetaaName' => 'قادة'],
            ['QetaaID' => 2, 'QetaaName' => 'أشبال'],
        ]);
    }

    public function test_non_leader_can_receive_mkhdom_but_not_staff(): void
    {
        $actor = $this->superAdmin();
        $served = User::create([
            'FirstName' => 'Cub',
            'SecondName' => 'A',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'C1',
        ]);
        DB::table('PersonQetaa')->insert(['PersonID' => $served->PersonID, 'QetaaID' => 2]);

        $ok = Request::create('/person-role/insert', 'POST', [
            'person_id' => $served->PersonID,
            'role_id' => 3,
            'RequestPersonID' => $actor->PersonID,
        ]);
        $ok->setUserResolver(fn () => $actor);
        app(PersonRoleController::class)->insert($ok);
        $this->assertDatabaseHas('PersonRole', ['PersonID' => $served->PersonID, 'RoleID' => 3]);

        $bad = Request::create('/person-role/insert', 'POST', [
            'person_id' => $served->PersonID,
            'role_id' => 2,
            'RequestPersonID' => $actor->PersonID,
        ]);
        $bad->setUserResolver(fn () => $actor);
        try {
            app(PersonRoleController::class)->insert($bad);
            $this->fail('Expected 403');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    private function superAdmin(): User
    {
        $user = User::create([
            'FirstName' => 'SA',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'SA1',
        ]);
        DB::table('PersonRole')->insert(['PersonID' => $user->PersonID, 'RoleID' => 1]);
        DB::table('PersonQetaa')->insert(['PersonID' => $user->PersonID, 'QetaaID' => 1]);

        return $user;
    }
}
