<?php

namespace Tests\Feature;

use App\Domain\Auth\TokenSessionService;
use App\Http\Controllers\AdminPasswordController;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RefreshFamilyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['PersonPhoneNumbers', 'PersonSystemPassword', 'PersonRole', 'Roles', 'PersonInformation', 'personal_access_tokens', 'refresh_tokens'] as $table) {
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
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('token_hash', 64)->unique();
            $table->uuid('family_id')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->unsignedBigInteger('replaced_by_id')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuidMorphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->increments('PersonPhoneNumberID');
            $table->unsignedInteger('PersonID');
            $table->string('PersonPersonalMobileNumber')->nullable();
        });
    }

    public function test_refresh_rotates_token_and_reuse_kills_family(): void
    {
        $user = $this->staffUser();
        $login = $this->postJson('/api/login', [
            'id' => $user->PersonID,
            'password' => 'secret12',
        ])->assertOk();

        $refresh = $login->json('refresh_token');
        $this->assertNotEmpty($refresh);
        $this->assertContains('api.me.view', $login->json('permissions'));

        $first = $this->postJson('/api/refresh', ['refresh_token' => $refresh])->assertOk();
        $rotated = $first->json('refresh_token');
        $this->assertNotSame($refresh, $rotated);
        $this->assertNotEmpty($first->json('access_token'));

        $this->postJson('/api/refresh', ['refresh_token' => $refresh])
            ->assertUnauthorized();

        $this->assertSame(0, RefreshToken::where('user_id', $user->PersonID)->whereNull('revoked_at')->count());
    }

    public function test_rotated_refresh_token_can_be_used_once(): void
    {
        $user = $this->staffUser();
        $login = $this->postJson('/api/login', [
            'id' => $user->PersonID,
            'password' => 'secret12',
        ])->assertOk();

        $first = $this->postJson('/api/refresh', [
            'refresh_token' => $login->json('refresh_token'),
        ])->assertOk();

        $second = $this->postJson('/api/refresh', [
            'refresh_token' => $first->json('refresh_token'),
        ])->assertOk();

        $this->assertNotSame($first->json('refresh_token'), $second->json('refresh_token'));
        $this->assertSame(1, RefreshToken::where('user_id', $user->PersonID)->whereNull('revoked_at')->count());
    }

    public function test_revoked_refresh_reuse_kills_that_family(): void
    {
        $user = $this->staffUser();
        $login = $this->postJson('/api/login', [
            'id' => $user->PersonID,
            'password' => 'secret12',
        ])->assertOk();

        $refresh = $login->json('refresh_token');
        $access = $login->json('access_token');
        $row = RefreshToken::where('token_hash', hash('sha256', $refresh))->first();
        $this->assertNotNull($row?->family_id);

        $row->update(['revoked_at' => now()]);
        $this->assertSame(1, $user->tokens()->where('name', TokenSessionService::FAMILY_PREFIX.$row->family_id)->count());

        $this->postJson('/api/refresh', ['refresh_token' => $refresh])
            ->assertUnauthorized();

        $this->assertNotNull(RefreshToken::where('id', $row->id)->value('revoked_at'));
        $this->assertSame(0, $user->tokens()->where('name', TokenSessionService::FAMILY_PREFIX.$row->family_id)->count());
        $this->assertNotEmpty($access);
    }

    public function test_legacy_null_family_is_assigned_on_refresh(): void
    {
        $user = $this->staffUser();
        $plain = 'legacy-refresh-token-plain-value-32chars-min';
        RefreshToken::create([
            'user_id' => $user->PersonID,
            'token_hash' => hash('sha256', $plain),
            'family_id' => null,
            'expires_at' => now()->addDays(30),
        ]);

        $this->postJson('/api/refresh', ['refresh_token' => $plain])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertNotNull(RefreshToken::where('token_hash', hash('sha256', $plain))->value('family_id'));
    }

    public function test_admin_password_change_revokes_all_families(): void
    {
        $user = $this->staffUser();
        $this->postJson('/api/login', [
            'id' => $user->PersonID,
            'password' => 'secret12',
        ])->assertOk();

        $request = Request::create('/admin/passwords/'.$user->PersonID.'/update', 'POST', [
            'password' => 'newpass12',
        ]);
        app(AdminPasswordController::class)->update($request, $user->PersonID);

        $this->assertSame(0, RefreshToken::where('user_id', $user->PersonID)->whereNull('revoked_at')->count());
        $this->assertSame(0, $user->fresh()->tokens()->count());
    }

    private function staffUser(): User
    {
        $user = User::create([
            'FirstName' => 'Staff',
            'SecondName' => 'User',
            'ThirdName' => 'X',
            'ShamandoraCode' => 'ST'.uniqid(),
        ]);
        $roleId = DB::table('Roles')->insertGetId(['RoleName' => 'Khadem']);
        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);
        DB::table('PersonSystemPassword')->insert([
            'PersonID' => $user->PersonID,
            'Password' => Hash::make('secret12'),
        ]);

        return $user;
    }
}
