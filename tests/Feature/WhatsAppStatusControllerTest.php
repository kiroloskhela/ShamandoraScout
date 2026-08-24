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

        // CI has no Vite manifest; avoid 500 from @vite in layouts.app
        $this->withoutVite();

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
        config([
            'services.whatsapp.bridge_base_url' => 'http://127.0.0.1:3010',
            'services.whatsapp.bridge_url' => 'http://127.0.0.1:3010/send',
        ]);

        Http::fake([
            'http://127.0.0.1:3010/health' => Http::response(['ok' => true, 'connected' => true], 200),
        ]);

        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get('/whatsapp/status');

        $response->assertOk();
        $response->assertSee('متصل', false);
    }

    public function test_status_page_shows_reconnect_when_session_exists(): void
    {
        config([
            'services.whatsapp.bridge_base_url' => 'http://127.0.0.1:3010',
            'services.whatsapp.bridge_url' => 'http://127.0.0.1:3010/send',
            'services.whatsapp.bridge_token' => 'test-token',
        ]);

        Http::fake([
            'http://127.0.0.1:3010/health' => Http::response([
                'ok' => true,
                'connected' => false,
                'hasReusableSession' => true,
                'pairingRequired' => false,
                'reconnecting' => false,
            ], 200),
            'http://127.0.0.1:3010/qr' => Http::response([
                'ok' => true,
                'connected' => false,
                'qr' => null,
            ], 200),
        ]);

        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get('/whatsapp/status');

        $response->assertOk();
        $response->assertSee('إعادة توصيل الجلسة المحفوظة', false);
        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:3010/qr'
                && $request->method() === 'GET'
                && $request->hasHeader('X-Bridge-Token', 'test-token');
        });
    }

    public function test_reconnect_posts_to_local_bridge(): void
    {
        config([
            'services.whatsapp.bridge_base_url' => 'http://127.0.0.1:3010',
            'services.whatsapp.bridge_url' => 'http://127.0.0.1:3010/send',
            'services.whatsapp.bridge_token' => 'test-token',
        ]);

        Http::fake([
            'http://127.0.0.1:3010/reconnect' => Http::response(['ok' => true, 'connected' => false], 200),
        ]);

        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->post('/whatsapp/reconnect');

        $response->assertRedirect(route('whatsapp.status'));
        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:3010/reconnect'
                && $request->method() === 'POST'
                && $request->hasHeader('X-Bridge-Token', 'test-token');
        });
    }
}
