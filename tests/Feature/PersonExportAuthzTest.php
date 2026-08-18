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

    public function test_media_can_open_person_directory_but_not_export(): void
    {
        $this->mock(PersonSearchService::class, function ($mock) {
            $mock->shouldReceive('paginateScopedToPerson')
                ->once()
                ->andReturn(new Collection);
        });

        $user = $this->createUserWithRole('Media');

        $this->actingAs($user)
            ->get(route('person.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('export.served-people'))
            ->assertStatus(403);

        $this->actingAs($user)
            ->get(route('export.scouts.excel'))
            ->assertStatus(403);
    }

    public function test_khadem_legacy_export_url_redirects_to_form(): void
    {
        $user = $this->createUserWithRole('Khadem');

        $this->actingAs($user)
            ->get(route('export.scouts.excel'))
            ->assertRedirect(route('export.served-people'));
    }

    public function test_khadem_can_open_served_people_export_form(): void
    {
        $user = $this->createUserWithRole('Khadem');

        $this->actingAs($user)
            ->get(route('export.served-people'))
            ->assertOk()
            ->assertSee(__('Download served people data'), false);
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
        foreach (['PersonGroup', 'GroupQetaa', 'Qetaa', 'Season', 'PersonRole', 'Roles', 'PersonImages', 'PersonInformation'] as $table) {
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
        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName')->nullable();
        });
        Schema::create('Season', function (Blueprint $table) {
            $table->increments('SeasonID');
            $table->string('SeasonName')->nullable();
            $table->integer('SeasonYear')->nullable();
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
}
