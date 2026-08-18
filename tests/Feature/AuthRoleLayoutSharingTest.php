<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthRoleLayoutSharingTest extends TestCase
{
    public function test_layout_uses_shared_authenticated_role_flags(): void
    {
        $this->withoutVite();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('sqlite required for layout role sharing test.');
        }

        $this->createMinimalAuthRoleSchema();
        $this->registerLayoutProbeRoute();

        $user = $this->userWithRole('Media');

        DB::enableQueryLog();

        $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get('/__test-layout-role-sharing')
            ->assertOk()
            ->assertSee('View photos', false)
            ->assertDontSee('Add photos', false)
            ->assertDontSee('Events attendance', false)
            ->assertDontSee('Scan attendance', false)
            ->assertDontSee('Live attendance', false)
            ->assertDontSee('WhatsApp campaigns', false)
            ->assertDontSee('Curriculum plans', false)
            ->assertDontSee('Change person sector', false)
            ->assertDontSee('My program tab', false)
            ->assertDontSee('Camp leader programs', false);

        $roleQueries = collect(DB::getQueryLog())->filter(function (array $query): bool {
            return str_contains($query['query'], 'PersonRole')
                || str_contains($query['query'], 'Roles');
        });

        $this->assertCount(1, $roleQueries, 'Role names should be loaded once per request.');
    }

    public function test_finance_sees_events_attendance_but_not_superadmin_tools(): void
    {
        $this->withoutVite();

        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('sqlite required for layout role sharing test.');
        }

        $this->createMinimalAuthRoleSchema();
        $this->registerLayoutProbeRoute();

        $this->actingAs($this->userWithRole('Finance'))
            ->withSession(['locale' => 'en'])
            ->get('/__test-layout-role-sharing')
            ->assertOk()
            ->assertSee('Events attendance', false)
            ->assertSee('Scan attendance', false)
            ->assertSee('Live attendance', false)
            ->assertDontSee('Change person sector', false)
            ->assertDontSee('Curriculum plans', false)
            ->assertDontSee('WhatsApp campaigns', false)
            ->assertDontSee('My program tab', false)
            ->assertDontSee('Camp leader programs', false);
    }

    private function registerLayoutProbeRoute(): void
    {
        Route::get('/__test-layout-role-sharing', fn () => view('layouts.app'))
            ->middleware('web');
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'Role',
            'SecondName' => 'Tester',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'ROLE'.uniqid(),
        ]);

        $roleId = DB::table('Roles')->insertGetId([
            'RoleName' => $roleName,
        ]);
        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);

        return $user;
    }

    private function createMinimalAuthRoleSchema(): void
    {
        foreach (['PersonRole', 'Roles', 'PersonImages', 'PersonInformation'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
        });

        Schema::create('PersonImages', function (Blueprint $table) {
            $table->increments('PersonImageID');
            $table->unsignedInteger('PersonID')->nullable();
            $table->string('PersonSystemImagePath')->nullable();
            $table->string('PersonSystemImageThumbnailPath')->nullable();
        });

        Schema::create('Roles', function (Blueprint $table) {
            $table->increments('RoleID');
            $table->string('RoleName')->nullable();
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
        });
    }
}
