<?php

namespace Tests\Unit;

use App\Domain\Authz\PermissionService;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PermissionServiceKeysTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
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
        });
        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
        });
    }

    public function test_keys_for_user_use_seed_map_when_enforce_is_off(): void
    {
        config(['permissions.enforce' => false]);

        $user = User::create([
            'FirstName' => 'K',
            'SecondName' => 'H',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'K1',
        ]);
        $roleId = DB::table('Roles')->insertGetId(['RoleName' => 'Khadem']);
        DB::table('PersonRole')->insert(['PersonID' => $user->PersonID, 'RoleID' => $roleId]);

        $keys = app(PermissionService::class)->keysForUser($user);
        $this->assertContains('api.mobile.staff', $keys);
        $this->assertContains('api.me.view', $keys);
        $this->assertNotContains('web.system.manage', $keys);

        $client = app(PermissionService::class)->clientKeysForUser($user);
        $this->assertContains('api.me.view', $client);
        foreach ($client as $key) {
            $this->assertTrue(str_starts_with($key, 'api.') || str_starts_with($key, 'mobile.'));
        }
    }
}
