<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Lightweight smoke coverage for finance selector + person directory authz.
 */
class HotPathSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->createAuthSchema();
    }

    public function test_guest_is_redirected_from_event_booking_finance_selector(): void
    {
        $this->get(route('eventBookingFinance.selector'))
            ->assertRedirect();
    }

    public function test_finance_role_can_open_booking_finance_selector(): void
    {
        $user = $this->createUserWithRole('Finance');

        $this->actingAs($user)
            ->get(route('eventBookingFinance.selector'))
            ->assertOk();
    }

    public function test_unprivileged_user_cannot_open_person_directory_index(): void
    {
        $user = $this->createUserWithRole('Media');

        $this->actingAs($user)
            ->get(route('person.index'))
            ->assertStatus(403);
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'Smoke',
            'SecondName' => $roleName,
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'SMK'.uniqid(),
        ]);

        $roleId = (int) DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');
        if (!$roleId) {
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
        foreach (['PersonRole', 'Roles', 'PersonImages', 'PersonInformation', 'Season'] as $table) {
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

        // Finance selector lists seasons.
        Schema::create('Season', function (Blueprint $table) {
            $table->increments('SeasonID');
            $table->string('SeasonName')->nullable();
            $table->integer('SeasonYear')->nullable();
        });
    }
}
