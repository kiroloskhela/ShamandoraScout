<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MkhdomApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'Attendance', 'Event', 'SeasonEvent', 'PersonSystemPassword',
            'PersonRole', 'Roles', 'PersonInformation', 'personal_access_tokens', 'refresh_tokens',
        ] as $table) {
            Schema::dropIfExists($table);
        }

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
        });
        Schema::create('PersonSystemPassword', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('Password');
        });
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('token_hash', 64)->unique();
            $table->uuid('family_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedBigInteger('replaced_by_id')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuidMorphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('SeasonEvent', function (Blueprint $table) {
            $table->increments('SeasonEventID');
            $table->unsignedInteger('EventID');
        });
        Schema::create('Event', function (Blueprint $table) {
            $table->increments('EventID');
            $table->string('EventName')->nullable();
            $table->date('EventStartDate')->nullable();
            $table->date('EventEndDate')->nullable();
        });
        Schema::create('Attendance', function (Blueprint $table) {
            $table->increments('AttendanceID');
            $table->unsignedInteger('SeasonEventID');
            $table->unsignedInteger('ServedID');
            $table->unsignedInteger('ServentID')->nullable();
            $table->string('AttendanceStatus');
            $table->text('Excuse')->nullable();
        });
    }

    public function test_mkhdom_can_read_own_me_and_attendance_but_not_staff_apis(): void
    {
        $user = $this->createUserWithRole('Mkhdom');
        $other = User::create([
            'FirstName' => 'Other',
            'SecondName' => 'P',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'O1',
        ]);

        DB::table('Event')->insert(['EventID' => 1, 'EventName' => 'Camp', 'EventStartDate' => '2026-01-01']);
        DB::table('SeasonEvent')->insert(['SeasonEventID' => 1, 'EventID' => 1]);
        $ownAttendanceId = DB::table('Attendance')->insertGetId([
            'SeasonEventID' => 1,
            'ServedID' => $user->PersonID,
            'ServentID' => $other->PersonID,
            'AttendanceStatus' => 'present',
        ]);
        DB::table('Attendance')->insert([
            'SeasonEventID' => 1,
            'ServedID' => $other->PersonID,
            'ServentID' => $other->PersonID,
            'AttendanceStatus' => 'absent',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $this->withHeaders($headers)->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('person_id', $user->PersonID)
            ->assertJsonPath('role_names.0', 'Mkhdom')
            ->assertJsonFragment(['api.me.view'])
            ->assertJsonMissing(['api.mobile.staff']);

        $mine = $this->withHeaders($headers)->getJson('/api/attendance/mine?person_id='.$other->PersonID);
        $mine->assertOk()->assertJsonPath('ok', true)->assertJsonPath('total', 1);
        $this->assertSame((int) $ownAttendanceId, (int) $mine->json('data.0.AttendanceID'));
        $this->assertSame($user->PersonID, (int) $mine->json('data.0.ServedID'));
        $this->assertSame('present', $mine->json('data.0.AttendanceStatus'));

        $this->withHeaders($headers)->getJson('/api/show-persons')
            ->assertForbidden()
            ->assertJsonPath('code', 'capability_denied');

        $this->withHeaders($headers)->postJson('/api/attendance/save', [
            'SeasonEventID' => 1,
            'attendance' => [(string) $other->PersonID => ['status' => 'present']],
        ])->assertForbidden()->assertJsonPath('code', 'capability_denied');
    }

    public function test_unknown_person_id_returns_same_401(): void
    {
        $user = $this->createUserWithRole('Mkhdom');

        $unknown = $this->postJson('/api/login', [
            'id' => 999999,
            'password' => 'secret12',
        ]);
        $badPassword = $this->postJson('/api/login', [
            'id' => $user->PersonID,
            'password' => 'wrong-password',
        ]);

        $unknown->assertUnauthorized()->assertJsonPath('ok', false)->assertJsonPath('message', 'Invalid credentials');
        $badPassword->assertUnauthorized()->assertJsonPath('ok', false)->assertJsonPath('message', 'Invalid credentials');
        $this->assertSame($unknown->json(), $badPassword->json());
    }

    public function test_password_without_app_role_cannot_login(): void
    {
        $user = User::create([
            'FirstName' => 'No',
            'SecondName' => 'Role',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'NR1',
        ]);
        DB::table('PersonSystemPassword')->insert([
            'PersonID' => $user->PersonID,
            'Password' => Hash::make('secret12'),
        ]);

        $this->postJson('/api/login', [
            'id' => $user->PersonID,
            'password' => 'secret12',
        ])->assertUnauthorized()->assertJsonPath('message', 'Invalid credentials');
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'Served',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'MK'.uniqid(),
        ]);
        $roleId = DB::table('Roles')->insertGetId(['RoleName' => $roleName]);
        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);
        DB::table('PersonSystemPassword')->insert([
            'PersonID' => $user->PersonID,
            'Password' => Hash::make('secret12'),
        ]);

        return $user;
    }
}
