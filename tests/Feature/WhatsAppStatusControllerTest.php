<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WhatsAppStatusControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('PersonImages');
        Schema::dropIfExists('PersonInformation');

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
            $table->string('RoleName');
            $table->text('RoleDescription')->nullable();
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
            $table->unsignedInteger('RequestPersonID')->nullable();
        });
    }

    private function createSuperAdmin(): User
    {
        $user = User::create([
            'FirstName' => 'Super',
            'SecondName' => 'Admin',
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'WA' . uniqid(),
        ]);

        $roleId = DB::table('Roles')->insertGetId([
            'RoleName' => 'SuperAdmin',
            'RoleDescription' => 'test',
        ]);

        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);

        return $user->fresh();
    }

    public function test_status_page_handles_bridge_down_gracefully(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get('/whatsapp/status');

        $response->assertOk();
        $response->assertSee('تعذر الاتصال بجسر الواتساب', false);
        $response->assertSee('غير متاح', false);
    }

    public function test_status_page_shows_connected_when_health_ok(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/health' => Http::response(['ok' => true, 'connected' => true], 200),
        ]);

        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get('/whatsapp/status');

        $response->assertOk();
        $response->assertSee('متصل', false);
    }
}
