<?php

namespace Tests\Feature;

use App\Domain\Auth\TokenSessionService;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RefreshFamilyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['PersonSystemPassword', 'PersonRole', 'Roles', 'PersonInformation', 'personal_access_tokens', 'refresh_tokens'] as $table) {
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
    }

    public function test_refresh_keeps_the_same_refresh_token_and_issues_new_access(): void
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
        $second = $this->postJson('/api/refresh', ['refresh_token' => $refresh])->assertOk();

        $this->assertSame($refresh, $first->json('refresh_token'));
        $this->assertSame($refresh, $second->json('refresh_token'));
        $this->assertNotSame($first->json('access_token'), $second->json('access_token'));
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

        $this->withHeaders(['Authorization' => "Bearer {$access}"])
            ->postJson('/api/logout')
            ->assertOk();

        $this->postJson('/api/refresh', ['refresh_token' => $refresh])
            ->assertUnauthorized();

        $this->assertNotNull(RefreshToken::where('id', $row->id)->value('revoked_at'));
        $this->assertSame(0, $user->tokens()->where('name', TokenSessionService::FAMILY_PREFIX.$row->family_id)->count());
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

        app(TokenSessionService::class)->revokeAllForUser((int) $user->PersonID);

        $this->assertSame(0, RefreshToken::where('user_id', $user->PersonID)->whereNull('revoked_at')->count());
        $this->assertSame(0, $user->tokens()->count());
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
