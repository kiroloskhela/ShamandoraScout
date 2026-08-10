<?php

namespace Tests\Feature;

use App\Domain\Person\PersonSearchService;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonExportAuthzTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->createAuthSchema();
    }

    public function test_khadem_can_open_person_directory_index(): void
    {
        $this->mock(PersonSearchService::class, function ($mock) {
            $mock->shouldReceive('paginateScopedToPerson')
                ->once()
                ->andReturn(new Collection);
        });

        $user = $this->createUserWithRole('Khadem');

        $this->actingAs($user)
            ->get(route('person.index'))
            ->assertOk();
    }

    public function test_secretary_can_open_person_directory_index(): void
    {
        $this->mock(PersonSearchService::class, function ($mock) {
            $mock->shouldReceive('paginateScopedToPerson')
                ->once()
                ->andReturn(new Collection);
        });

        $user = $this->createUserWithRole('Secretary');

        $this->actingAs($user)
            ->get(route('person.index'))
            ->assertOk();
    }

    public function test_media_cannot_open_person_directory_or_export(): void
    {
        $user = $this->createUserWithRole('Media');

        $this->actingAs($user)
            ->get(route('person.index'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->get(route('export.scouts.excel'))
            ->assertStatus(403);
    }

    public function test_khadem_is_authorized_for_export_route(): void
    {
        $user = $this->createUserWithRole('Khadem');

        // SQLite cannot run the MySQL export SQL; auth must pass (not 403).
        $response = $this->actingAs($user)->get(route('export.scouts.excel'));

        $this->assertNotSame(403, $response->status());
    }

    public function test_export_route_has_no_user_id_parameter(): void
    {
        $route = app('router')->getRoutes()->getByName('export.scouts.excel');

        $this->assertNotNull($route);
        $this->assertSame('export/scouts', $route->uri());
        $this->assertSame([], $route->parameterNames());
    }

    public function test_legacy_export_url_with_user_id_is_gone(): void
    {
        $user = $this->createUserWithRole('SuperAdmin');

        $this->actingAs($user)
            ->get('/export/scouts/'.$user->PersonID)
            ->assertNotFound();
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'Export',
            'SecondName' => $roleName,
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'EXP'.uniqid(),
        ]);

        $roleId = (int) DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');
        if (! $roleId) {
            $roleId = (int) DB::table('Roles')->insertGetId([
                'RoleName' => $roleName,
            ]);
        }

        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);

        return $user->fresh();
    }

    private function createAuthSchema(): void
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
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
        });
    }
}
