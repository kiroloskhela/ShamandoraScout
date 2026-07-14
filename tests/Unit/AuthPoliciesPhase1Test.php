<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\GamePolicy;
use App\Policies\PersonPolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthPoliciesPhase1Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('PersonInformation');

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
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
    }

    private function createUser(?string $code = null): User
    {
        return User::create([
            'FirstName' => 'Test',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => $code ?? ('T' . uniqid()),
        ]);
    }

    private function attachRole(User $user, string $roleName): void
    {
        $roleId = DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');
        if (!$roleId) {
            $roleId = DB::table('Roles')->insertGetId([
                'RoleName' => $roleName,
                'RoleDescription' => $roleName,
            ]);
        }

        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);
    }

    public function test_games_gates_allow_authenticated_user(): void
    {
        $user = $this->createUser();

        $this->assertTrue(Gate::forUser($user)->allows('games.view'));
        $this->assertTrue(Gate::forUser($user)->allows('games.create'));
        $this->assertTrue(Gate::forUser($user)->allows('games.update'));
        $this->assertTrue(Gate::forUser($user)->allows('games.delete'));
    }

    public function test_games_gates_deny_guest(): void
    {
        $this->assertTrue(Gate::forUser(null)->denies('games.create'));
        $this->assertTrue(Gate::forUser(null)->denies('games.update'));
        $this->assertTrue(Gate::forUser(null)->denies('games.delete'));
        $this->assertTrue(Gate::forUser(null)->denies('games.view'));
    }

    public function test_game_policy_allows_authenticated_user(): void
    {
        $user = $this->createUser();
        $policy = new GamePolicy();

        $this->assertTrue($policy->viewAny($user));
        $this->assertTrue($policy->create($user));
        $this->assertTrue($policy->update($user));
        $this->assertTrue($policy->delete($user));
    }

    public function test_person_policy_allows_own_record(): void
    {
        $user = $this->createUser();
        $policy = new PersonPolicy();

        $this->assertTrue($policy->view($user, $user));
        $this->assertTrue($policy->update($user, $user));
        $this->assertTrue(Gate::forUser($user)->allows('view', $user));
    }

    public function test_person_policy_denies_other_without_elevated_role(): void
    {
        $viewer = $this->createUser('V1');
        $this->attachRole($viewer, 'Servant');
        $target = $this->createUser('T1');
        $policy = new PersonPolicy();

        $this->assertFalse($policy->view($viewer, $target));
        $this->assertFalse($policy->update($viewer, $target));
        $this->assertTrue(Gate::forUser($viewer)->denies('view', $target));
    }

    public function test_person_policy_allows_super_admin_on_other(): void
    {
        $admin = $this->createUser('A1');
        $this->attachRole($admin, 'SuperAdmin');
        $target = $this->createUser('T2');
        $policy = new PersonPolicy();

        $this->assertTrue($policy->view($admin, $target));
        $this->assertTrue($policy->update($admin, $target));
    }

    public function test_person_policy_allows_admin_qetaa_on_other(): void
    {
        $admin = $this->createUser('AQ1');
        $this->attachRole($admin, 'AdminQetaa');
        $target = $this->createUser('T3');
        $policy = new PersonPolicy();

        $this->assertTrue($policy->view($admin, $target));
        $this->assertTrue($policy->update($admin, $target));
    }
}
