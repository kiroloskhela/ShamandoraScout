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

        $user = User::create([
            'FirstName' => 'Role',
            'SecondName' => 'Tester',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'ROLE' . uniqid(),
        ]);

        DB::table('Roles')->insert([
            'RoleID' => 1,
            'RoleName' => 'Media',
        ]);
        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => 1,
        ]);

        Route::get('/__test-layout-role-sharing', fn () => view('layouts.app'))
            ->middleware('web');

        DB::enableQueryLog();

        $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->get('/__test-layout-role-sharing')
            ->assertOk()
            ->assertSee('View photos', false)
            ->assertDontSee('Add photos', false);

        $roleQueries = collect(DB::getQueryLog())->filter(function (array $query): bool {
            return str_contains($query['query'], 'PersonRole')
                || str_contains($query['query'], 'Roles');
        });

        $this->assertCount(1, $roleQueries, 'Role names should be loaded once per request.');
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
