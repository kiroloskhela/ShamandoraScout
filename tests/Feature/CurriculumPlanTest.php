<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CurriculumPlanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        foreach ([
            'CurriculumPlanLecture',
            'CurriculumPlan',
            'Curricula',
            'CurriculaCategory',
            'Marhala',
            'Qetaa',
            'PersonRole',
            'Roles',
            'PersonInformation',
            'personal_access_tokens',
        ] as $table) {
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
            $table->text('RoleDescription')->nullable();
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

        Schema::create('Marhala', function (Blueprint $table) {
            $table->increments('MarhalaID');
            $table->string('MarhalaName');
        });

        Schema::create('CurriculaCategory', function (Blueprint $table) {
            $table->increments('CurriculaCategoryID');
            $table->string('CurriculaCategoryName');
        });

        Schema::create('Curricula', function (Blueprint $table) {
            $table->increments('CurriculaID');
            $table->string('CurriculaName');
            $table->string('CurriculaPath')->nullable();
            $table->unsignedInteger('CurriculaCategoryID')->nullable();
            $table->unsignedInteger('MarhalaID')->nullable();
            $table->unsignedInteger('ServentID')->nullable();
            $table->timestamps();
        });

        Schema::create('CurriculumPlan', function (Blueprint $table) {
            $table->increments('PlanID');
            $table->unsignedInteger('QetaaID');
            $table->string('PlanName');
            $table->integer('SortOrder')->default(0);
            $table->unsignedTinyInteger('IsActive')->default(0);
            $table->timestamps();
        });

        Schema::create('CurriculumPlanLecture', function (Blueprint $table) {
            $table->unsignedInteger('PlanID');
            $table->unsignedInteger('CurriculaID');
            $table->integer('SortOrder')->default(0);
            $table->primary(['PlanID', 'CurriculaID']);
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

        DB::table('Qetaa')->insert([
            ['QetaaID' => 1, 'QetaaName' => 'براعم'],
            ['QetaaID' => 2, 'QetaaName' => 'أشبال'],
        ]);

        DB::table('Marhala')->insert([
            'MarhalaID' => 1,
            'MarhalaName' => 'Stage 1',
        ]);

        DB::table('CurriculaCategory')->insert([
            'CurriculaCategoryID' => 1,
            'CurriculaCategoryName' => 'General',
        ]);

        DB::table('Curricula')->insert([
            'CurriculaID' => 10,
            'CurriculaName' => 'Lecture A',
            'CurriculaPath' => 'CurriculaDocuments/a.pdf',
            'CurriculaCategoryID' => 1,
            'MarhalaID' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('Curricula')->insert([
            'CurriculaID' => 11,
            'CurriculaName' => 'Lecture B',
            'CurriculaPath' => 'CurriculaDocuments/b.pdf',
            'CurriculaCategoryID' => 1,
            'MarhalaID' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_create_plan_is_inactive_by_default(): void
    {
        $admin = $this->createUserWithRoles(['SuperAdmin']);

        $this->actingAs($admin)
            ->post(route('curriculum-plan.insert'), [
                'qetaa_id' => 2,
                'plan_name' => 'Year 1',
                'sort_order' => 1,
            ])
            ->assertRedirect(route('curriculum-plan.index'));

        $this->assertDatabaseHas('CurriculumPlan', [
            'QetaaID' => 2,
            'PlanName' => 'Year 1',
            'IsActive' => 0,
        ]);
    }

    public function test_activate_deactivates_sibling_plans_same_qetaa_only(): void
    {
        $admin = $this->createUserWithRoles(['SuperAdmin']);

        $planA = $this->seedPlan(2, 'Year 1', 1);
        $planB = $this->seedPlan(2, 'Year 2', 0);
        $planOther = $this->seedPlan(1, 'Other', 1);

        $this->actingAs($admin)
            ->post(route('curriculum-plan.activate', $planB))
            ->assertRedirect(route('curriculum-plan.index'));

        $this->assertSame(0, (int) DB::table('CurriculumPlan')->where('PlanID', $planA)->value('IsActive'));
        $this->assertSame(1, (int) DB::table('CurriculumPlan')->where('PlanID', $planB)->value('IsActive'));
        $this->assertSame(1, (int) DB::table('CurriculumPlan')->where('PlanID', $planOther)->value('IsActive'));
    }

    public function test_get_activate_is_not_allowed(): void
    {
        $admin = $this->createUserWithRoles(['SuperAdmin']);
        $planId = $this->seedPlan(2, 'Year 1', 0);

        $this->actingAs($admin)
            ->get('/curriculum-plans/activate/'.$planId)
            ->assertStatus(405);
    }

    public function test_non_super_admin_cannot_mutate(): void
    {
        $user = $this->createUserWithRoles(['Servant']);

        $this->actingAs($user)
            ->post(route('curriculum-plan.insert'), [
                'qetaa_id' => 2,
                'plan_name' => 'Year 1',
            ])
            ->assertStatus(403);

        $planId = $this->seedPlan(2, 'Year 1', 0);

        $this->actingAs($user)
            ->post(route('curriculum-plan.activate', $planId))
            ->assertStatus(403);
    }

    public function test_delete_active_plan_is_blocked(): void
    {
        $admin = $this->createUserWithRoles(['SuperAdmin']);
        $planId = $this->seedPlan(2, 'Year 1', 1);

        $this->actingAs($admin)
            ->delete(route('curriculum-plan.destroy', $planId))
            ->assertRedirect(route('curriculum-plan.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('CurriculumPlan', ['PlanID' => $planId]);
    }

    public function test_delete_after_deactivate_succeeds(): void
    {
        $admin = $this->createUserWithRoles(['SuperAdmin']);
        $planId = $this->seedPlan(2, 'Year 1', 1);

        $this->actingAs($admin)
            ->post(route('curriculum-plan.deactivate', $planId))
            ->assertRedirect(route('curriculum-plan.index'));

        $this->actingAs($admin)
            ->delete(route('curriculum-plan.destroy', $planId))
            ->assertRedirect(route('curriculum-plan.index'));

        $this->assertDatabaseMissing('CurriculumPlan', ['PlanID' => $planId]);
    }

    public function test_sync_lectures_and_reject_invalid(): void
    {
        $admin = $this->createUserWithRoles(['SuperAdmin']);
        $planId = $this->seedPlan(2, 'Year 1', 0);

        $this->actingAs($admin)
            ->patch(route('curriculum-plan.update', $planId), [
                'plan_name' => 'Year 1',
                'sort_order' => 0,
                'curricula_ids' => [10, 11],
            ])
            ->assertRedirect(route('curriculum-plan.edit', $planId));

        $this->assertDatabaseHas('CurriculumPlanLecture', [
            'PlanID' => $planId,
            'CurriculaID' => 10,
        ]);
        $this->assertDatabaseHas('CurriculumPlanLecture', [
            'PlanID' => $planId,
            'CurriculaID' => 11,
        ]);

        $this->actingAs($admin)
            ->from(route('curriculum-plan.edit', $planId))
            ->patch(route('curriculum-plan.update', $planId), [
                'plan_name' => 'Year 1',
                'sort_order' => 0,
                'curricula_ids' => [999],
            ])
            ->assertRedirect(route('curriculum-plan.edit', $planId))
            ->assertSessionHasErrors('curricula_ids.0');
    }

    public function test_curricula_destroy_blocked_when_referenced(): void
    {
        $admin = $this->createUserWithRoles(['SuperAdmin']);
        $planId = $this->seedPlan(2, 'Year 1', 0);

        DB::table('CurriculumPlanLecture')->insert([
            'PlanID' => $planId,
            'CurriculaID' => 10,
            'SortOrder' => 0,
        ]);

        $this->actingAs($admin)
            ->delete(route('curricula.destroy', 10))
            ->assertRedirect(route('curricula.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('Curricula', ['CurriculaID' => 10]);
    }

    public function test_api_unauthenticated_is_rejected(): void
    {
        $this->getJson('/api/curriculum-plans/active/2')->assertStatus(401);
        $this->getJson('/api/curriculum-plans/active')->assertStatus(401);
    }

    public function test_api_active_for_qetaa_returns_plan_with_download_urls(): void
    {
        $headers = $this->authHeadersForRoles(['Khadem']);
        $planId = $this->seedPlan(2, 'Year 1', 1);

        DB::table('CurriculumPlanLecture')->insert([
            ['PlanID' => $planId, 'CurriculaID' => 10, 'SortOrder' => 0],
            ['PlanID' => $planId, 'CurriculaID' => 11, 'SortOrder' => 1],
        ]);

        $this->withHeaders($headers)
            ->getJson('/api/curriculum-plans/active/2')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('data.PlanID', $planId)
            ->assertJsonPath('data.PlanName', 'Year 1')
            ->assertJsonPath('data.lectures.0.CurriculaID', 10)
            ->assertJsonPath('data.lectures.0.download_url', url('/api/curricula/10/download'));
    }

    public function test_api_active_for_qetaa_null_when_none(): void
    {
        $headers = $this->authHeadersForRoles(['Khadem']);

        $this->withHeaders($headers)
            ->getJson('/api/curriculum-plans/active/2')
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'data' => null,
            ]);
    }

    public function test_api_active_list_returns_only_active_plans(): void
    {
        $headers = $this->authHeadersForRoles(['Khadem']);
        $active = $this->seedPlan(2, 'Year 1', 1);
        $this->seedPlan(2, 'Year 2', 0);
        $otherActive = $this->seedPlan(1, 'Braam', 1);

        $response = $this->withHeaders($headers)
            ->getJson('/api/curriculum-plans/active')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('count', 2);

        $ids = collect($response->json('data'))->pluck('PlanID')->sort()->values()->all();
        $this->assertSame([$active, $otherActive], $ids);
    }

    public function test_api_missing_qetaa_is_404(): void
    {
        $headers = $this->authHeadersForRoles(['Khadem']);

        $this->withHeaders($headers)
            ->getJson('/api/curriculum-plans/active/99')
            ->assertStatus(404);
    }

    private function seedPlan(int $qetaaId, string $name, int $isActive): int
    {
        return (int) DB::table('CurriculumPlan')->insertGetId([
            'QetaaID' => $qetaaId,
            'PlanName' => $name,
            'SortOrder' => 0,
            'IsActive' => $isActive,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUserWithRoles(array $roleNames): User
    {
        $user = User::create([
            'FirstName' => 'Test',
            'SecondName' => 'User',
            'ThirdName' => 'Role',
            'ShamandoraCode' => 'T'.uniqid(),
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

    private function authHeadersForRoles(array $roleNames): array
    {
        $user = $this->createUserWithRoles($roleNames);
        $token = $user->createToken('test-token')->plainTextToken;

        return ['Authorization' => 'Bearer '.$token];
    }
}
