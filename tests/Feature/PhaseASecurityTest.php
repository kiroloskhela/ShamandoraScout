<?php

namespace Tests\Feature;

use App\Models\User;
use App\Policies\PersonPolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Phase A security regressions: password scrubbing, signed liveform resume,
 * Games write fail-closed, AdminQetaa PersonPolicy scope, attendance allow-list.
 */
class PhaseASecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('Attendance');
        Schema::dropIfExists('EventQetaa');
        Schema::dropIfExists('GroupQetaa');
        Schema::dropIfExists('PersonGroup');
        Schema::dropIfExists('SeasonEvent');
        Schema::dropIfExists('PersonQetaa');
        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('PersonSystemPassword');
        Schema::dropIfExists('NewUsersInformation');
        Schema::dropIfExists('MarhalaEntryQuestions');
        Schema::dropIfExists('NewUsersPersonEntryQuestions');
        Schema::dropIfExists('Games');
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

        Schema::create('NewUsersInformation', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID')->nullable();
            $table->unsignedInteger('SanaMarhalaID')->nullable();
            $table->string('FirstName')->nullable();
        });

        Schema::create('MarhalaEntryQuestions', function (Blueprint $table) {
            $table->increments('QuestionID');
            $table->unsignedInteger('QetaaID')->nullable();
            $table->string('QuestionText')->nullable();
            $table->boolean('NotToBeShown')->default(0);
            $table->boolean('IsRequired')->default(0);
        });

        Schema::create('NewUsersPersonEntryQuestions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QuestionID');
            $table->text('Answer')->nullable();
        });

        Schema::create('Games', function (Blueprint $table) {
            $table->increments('GameID');
            $table->string('Title');
            $table->text('GameDescription')->nullable();
            $table->text('Rules')->nullable();
            $table->text('PointSystem')->nullable();
            $table->string('AgeGroup')->nullable();
            $table->string('Target')->nullable();
            $table->string('ReferenceLink')->nullable();
            $table->string('RequireCustody')->nullable();
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

        Schema::create('SeasonEvent', function (Blueprint $table) {
            $table->increments('SeasonEventID');
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('SeasonID')->nullable();
        });

        Schema::create('EventQetaa', function (Blueprint $table) {
            $table->increments('EventQetaaID');
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('QetaaID');
        });

        Schema::create('Attendance', function (Blueprint $table) {
            $table->increments('AttendanceID');
            $table->unsignedInteger('SeasonEventID');
            $table->unsignedInteger('ServedID');
            $table->unsignedInteger('ServentID')->nullable();
            $table->string('AttendanceStatus')->nullable();
            $table->text('Excuse')->nullable();
            $table->unique(['SeasonEventID', 'ServedID']);
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

    }

    private function createUserWithRoles(array $roleNames, ?string $code = null): User
    {
        $user = User::create([
            'FirstName' => 'Test',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => $code ?? ('T'.uniqid()),
        ]);

        foreach ($roleNames as $roleName) {
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

        return $user->fresh();
    }

    public function test_person_api_controller_does_not_select_password_column(): void
    {
        $source = file_get_contents(app_path('Http/Controllers/API/PersonApiController.php'));

        $this->assertStringNotContainsString("PersonSystemPassword.Password", $source);
        $this->assertStringNotContainsString("leftJoin('PersonSystemPassword'", $source);
    }

    public function test_person_questions_blade_does_not_echo_password_property(): void
    {
        $source = file_get_contents(resource_path('views/person/person-questions.blade.php'));

        $this->assertStringNotContainsString('$person->Password', $source);
    }

    public function test_unsigned_liveform_resume_is_rejected(): void
    {
        DB::table('NewUsersInformation')->insert([
            'PersonID' => 42,
            'QetaaID' => 1,
            'SanaMarhalaID' => 1,
            'FirstName' => 'Applicant',
        ]);

        $this->get('/liveform/resume/42')->assertStatus(403);
    }

    public function test_signed_liveform_resume_is_accepted(): void
    {
        DB::table('NewUsersInformation')->insert([
            'PersonID' => 43,
            'QetaaID' => 1,
            'SanaMarhalaID' => 1,
            'FirstName' => 'Applicant',
        ]);

        $url = URL::temporarySignedRoute(
            'person.liveform-resume-questions',
            now()->addHour(),
            ['id' => 43]
        );

        $this->get($url)->assertOk();
    }

    public function test_non_superadmin_cannot_create_game_via_api(): void
    {
        $user = $this->createUserWithRoles(['Servant']);
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/games', ['title' => 'Blocked Game'])
            ->assertForbidden();

        $this->assertDatabaseCount('Games', 0);
    }

    public function test_superadmin_can_create_game_via_api(): void
    {
        $user = $this->createUserWithRoles(['SuperAdmin']);
        $token = $user->createToken('t')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/games', ['title' => 'Allowed Game'])
            ->assertCreated();

        $this->assertDatabaseCount('Games', 1);
    }

    public function test_admin_qetaa_cannot_view_person_outside_shared_qetaa(): void
    {
        $admin = $this->createUserWithRoles(['AdminQetaa'], 'AQ1');
        $target = $this->createUserWithRoles([], 'T3');

        DB::table('PersonQetaa')->insert([
            ['PersonID' => $admin->PersonID, 'QetaaID' => 10],
            ['PersonID' => $target->PersonID, 'QetaaID' => 99],
        ]);

        $this->assertFalse((new PersonPolicy)->view($admin, $target));
    }

    public function test_admin_qetaa_can_view_person_in_shared_qetaa(): void
    {
        $admin = $this->createUserWithRoles(['AdminQetaa'], 'AQ2');
        $target = $this->createUserWithRoles([], 'T4');

        DB::table('PersonQetaa')->insert([
            ['PersonID' => $admin->PersonID, 'QetaaID' => 10],
            ['PersonID' => $target->PersonID, 'QetaaID' => 10],
        ]);

        $this->assertTrue((new PersonPolicy)->view($admin, $target));
    }

    public function test_web_attendance_save_skips_person_ids_outside_allow_list(): void
    {
        $servant = $this->createUserWithRoles(['Servant'], 'SRV1');
        $allowed = $this->createUserWithRoles([], 'ALL1');
        $blocked = $this->createUserWithRoles([], 'BLK1');

        DB::table('PersonGroup')->insert([
            'PersonID' => $servant->PersonID,
            'GroupID' => 1,
        ]);
        DB::table('GroupQetaa')->insert([
            'GroupID' => 1,
            'QetaaID' => 5,
        ]);
        DB::table('SeasonEvent')->insert([
            'SeasonEventID' => 7,
            'EventID' => 3,
            'SeasonID' => 1,
        ]);
        DB::table('EventQetaa')->insert([
            'EventID' => 3,
            'QetaaID' => 5,
        ]);
        DB::table('PersonQetaa')->insert([
            ['PersonID' => $allowed->PersonID, 'QetaaID' => 5],
            ['PersonID' => $blocked->PersonID, 'QetaaID' => 8],
        ]);

        $this->actingAs($servant)->post('/attendance/save/7', [
            'season_id' => 1,
            'attendance' => [
                $allowed->PersonID => ['status' => 'present'],
                $blocked->PersonID => ['status' => 'present'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('Attendance', [
            'SeasonEventID' => 7,
            'ServedID' => $allowed->PersonID,
            'AttendanceStatus' => 'present',
        ]);
        $this->assertDatabaseMissing('Attendance', [
            'SeasonEventID' => 7,
            'ServedID' => $blocked->PersonID,
        ]);
    }
}
