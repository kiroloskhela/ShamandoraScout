<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffWebLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['PersonSystemPassword', 'PersonRole', 'Roles', 'PersonInformation'] as $table) {
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
    }

    public function test_staff_can_log_in_to_web(): void
    {
        $user = $this->userWithRole('Khadem');

        $this->from('/login-auth')->post('/login', [
            'person_id' => (string) $user->PersonID,
            'person_password' => 'secret12',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    public function test_mkhdom_cannot_log_in_to_web(): void
    {
        $user = $this->userWithRole('Mkhdom');

        $this->from('/login-auth')->post('/login', [
            'person_id' => (string) $user->PersonID,
            'person_password' => 'secret12',
        ])->assertRedirect('/login-auth')
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    private function userWithRole(string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'Web',
            'SecondName' => $roleName,
            'ThirdName' => 'X',
            'ShamandoraCode' => 'WEB'.uniqid(),
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

        return $user->fresh();
    }
}
