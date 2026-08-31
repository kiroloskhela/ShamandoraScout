<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\User;
use App\Policies\EventBookingPolicy;
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

        Schema::dropIfExists('GroupQetaa');
        Schema::dropIfExists('PersonGroup');
        Schema::dropIfExists('PersonQetaa');
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

    private function createUser(?string $code = null): User
    {
        return User::create([
            'FirstName' => 'Test',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => $code ?? ('T'.uniqid()),
        ]);
    }

    private function attachRole(User $user, string $roleName): void
    {
        $roleId = DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');
        if (! $roleId) {
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

    public function test_games_gates_deny_unprivileged_user(): void
    {
        $user = $this->createUser();

        $this->assertTrue(Gate::forUser($user)->denies('games.view'));
        $this->assertTrue(Gate::forUser($user)->denies('games.create'));
        $this->assertTrue(Gate::forUser($user)->denies('games.update'));
        $this->assertTrue(Gate::forUser($user)->denies('games.delete'));
    }

    public function test_games_gates_allow_create_and_update_but_deny_delete_for_staff(): void
    {
        $user = $this->createUser();
        $this->attachRole($user, 'Khadem');

        $this->assertTrue(Gate::forUser($user)->allows('games.view'));
        $this->assertTrue(Gate::forUser($user)->allows('games.create'));
        $this->assertTrue(Gate::forUser($user)->allows('games.update'));
        $this->assertTrue(Gate::forUser($user)->denies('games.delete'));
    }

    public function test_games_gates_allow_mutate_for_superadmin(): void
    {
        $user = $this->createUser();
        $this->attachRole($user, 'SuperAdmin');

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

    public function test_game_policy_allows_create_and_update_but_denies_delete_for_staff(): void
    {
        $user = $this->createUser();
        $this->attachRole($user, 'Khadem');
        $policy = new GamePolicy;
        $game = new Game;

        $this->assertTrue($policy->viewAny($user));
        $this->assertTrue($policy->view($user, $game));
        $this->assertTrue($policy->create($user));
        $this->assertTrue($policy->update($user, $game));
        $this->assertFalse($policy->delete($user, $game));
    }

    public function test_person_policy_allows_own_record(): void
    {
        $user = $this->createUser();
        $policy = new PersonPolicy;

        $this->assertTrue($policy->view($user, $user));
        $this->assertTrue($policy->update($user, $user));
        $this->assertTrue(Gate::forUser($user)->allows('view', $user));
    }

    public function test_person_policy_denies_other_without_elevated_role(): void
    {
        $viewer = $this->createUser('V1');
        $this->attachRole($viewer, 'Servant');
        $target = $this->createUser('T1');
        $policy = new PersonPolicy;

        $this->assertFalse($policy->view($viewer, $target));
        $this->assertFalse($policy->update($viewer, $target));
        $this->assertTrue(Gate::forUser($viewer)->denies('view', $target));
    }

    public function test_person_policy_allows_super_admin_on_other(): void
    {
        $admin = $this->createUser('A1');
        $this->attachRole($admin, 'SuperAdmin');
        $target = $this->createUser('T2');
        $policy = new PersonPolicy;

        $this->assertTrue($policy->view($admin, $target));
        $this->assertTrue($policy->update($admin, $target));
    }

    public function test_person_policy_allows_admin_qetaa_on_shared_qetaa_only(): void
    {
        $admin = $this->createUser('AQ1');
        $this->attachRole($admin, 'AdminQetaa');
        $shared = $this->createUser('T3');
        $other = $this->createUser('T4');
        $policy = new PersonPolicy;

        DB::table('PersonQetaa')->insert([
            ['PersonID' => $admin->PersonID, 'QetaaID' => 1],
            ['PersonID' => $shared->PersonID, 'QetaaID' => 1],
            ['PersonID' => $other->PersonID, 'QetaaID' => 2],
        ]);

        $this->assertTrue($policy->view($admin, $shared));
        $this->assertTrue($policy->update($admin, $shared));
        $this->assertTrue($policy->delete($admin, $shared));
        $this->assertFalse($policy->view($admin, $other));
        $this->assertFalse($policy->update($admin, $other));
        $this->assertFalse($policy->delete($admin, $other));
    }

    public function test_event_booking_policy_allows_finance_roles_to_mutate(): void
    {
        $finance = $this->createUser('F1');
        $this->attachRole($finance, 'AdminFinance');
        $policy = new EventBookingPolicy;

        $this->assertTrue($policy->create($finance));
        $this->assertTrue($policy->update($finance));
        $this->assertFalse($policy->delete($finance));
    }

    public function test_event_booking_policy_allows_superadmin_delete(): void
    {
        $admin = $this->createUser('SA1');
        $this->attachRole($admin, 'SuperAdmin');
        $policy = new EventBookingPolicy;

        $this->assertTrue($policy->delete($admin));
    }
}
