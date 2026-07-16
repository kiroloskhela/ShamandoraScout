<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditLogMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('PersonInformation');

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
        });

        Schema::create('Roles', function (Blueprint $table) {
            $table->increments('RoleID');
            $table->string('RoleName')->nullable();
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('person_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('method', 16);
            $table->string('path', 512);
            $table->string('route_name')->nullable();
            $table->string('action', 512);
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('request_payload')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Route::middleware('web')->post('/__audit-test', function () {
            return response('ok', 201);
        })->name('audit.test');

        Route::middleware('web')->get('/__audit-test', function () {
            return response('ok', 200);
        });

        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_authenticated_mutating_request_creates_audit_log(): void
    {
        $user = User::create([
            'FirstName' => 'أحمد',
            'SecondName' => 'محمد',
            'ThirdName' => 'علي',
            'ShamandoraCode' => 'AUD' . uniqid(),
        ]);

        $this->actingAs($user)
            ->post('/__audit-test', ['note' => 'hello'])
            ->assertStatus(201);

        $this->assertDatabaseCount('audit_logs', 1);

        $log = AuditLog::first();
        $this->assertSame('POST', $log->method);
        $this->assertSame($user->PersonID, $log->person_id);
        $this->assertSame('أحمد محمد علي', $log->actor_name);
        $this->assertSame(201, $log->response_status);
        $this->assertSame('hello', $log->request_payload['note'] ?? null);
    }

    public function test_get_request_does_not_create_audit_log(): void
    {
        $user = User::create([
            'FirstName' => 'Test',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'AUD' . uniqid(),
        ]);

        $this->actingAs($user)
            ->get('/__audit-test')
            ->assertOk();

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_password_fields_are_scrubbed(): void
    {
        $user = User::create([
            'FirstName' => 'Test',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'AUD' . uniqid(),
        ]);

        $this->actingAs($user)
            ->post('/__audit-test', [
                'password' => 'super-secret',
                'password_confirmation' => 'super-secret',
                'api_token' => 'tok-123',
                'visible' => 'keep-me',
            ])
            ->assertStatus(201);

        $log = AuditLog::first();
        $this->assertNotNull($log);
        $this->assertSame('[redacted]', $log->request_payload['password'] ?? null);
        $this->assertSame('[redacted]', $log->request_payload['password_confirmation'] ?? null);
        $this->assertSame('[redacted]', $log->request_payload['api_token'] ?? null);
        $this->assertSame('keep-me', $log->request_payload['visible'] ?? null);
    }

    public function test_guest_mutating_request_is_not_logged(): void
    {
        $this->post('/__audit-test', ['note' => 'anon'])->assertStatus(201);
        $this->assertDatabaseCount('audit_logs', 0);
    }
}
