<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * These legacy tables (Games/Roles/PersonRole/PersonInformation) are not
 * managed by Laravel migrations in this app (see schema.sql), so the test
 * creates a minimal in-memory sqlite version of each just for this suite.
 */
class GamesApiAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('PersonInformation')) {
            Schema::create('PersonInformation', function (Blueprint $table) {
                $table->increments('PersonID');
                $table->string('ShamandoraCode')->nullable();
                $table->string('FirstName')->nullable();
                $table->string('SecondName')->nullable();
                $table->string('ThirdName')->nullable();
            });
        }

        if (!Schema::hasTable('Roles')) {
            Schema::create('Roles', function (Blueprint $table) {
                $table->increments('RoleID');
                $table->string('RoleName');
                $table->text('RoleDescription')->nullable();
            });
        }

        if (!Schema::hasTable('PersonRole')) {
            Schema::create('PersonRole', function (Blueprint $table) {
                $table->increments('PersonRoleID');
                $table->unsignedInteger('PersonID');
                $table->unsignedInteger('RoleID');
                $table->unsignedInteger('RequestPersonID')->nullable();
            });
        }

        if (!Schema::hasTable('Games')) {
            Schema::create('Games', function (Blueprint $table) {
                $table->increments('GameID');
                $table->string('Title');
                $table->text('GameDescription')->nullable();
                $table->text('Rules')->nullable();
                $table->text('PointSystem')->nullable();
                $table->string('AgeGroup')->nullable();
                $table->string('Target')->nullable();
                $table->string('ReferenceLink')->nullable();
                $table->string('RequireCustody')->nullable();
            });
        }
    }

    private function createUserWithRoles(array $roleNames): User
    {
        $user = User::create([
            'FirstName' => 'Test',
            'SecondName' => 'Test',
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'TST' . uniqid(),
        ]);

        foreach ($roleNames as $roleName) {
            $roleId = DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');

            if (!$roleId) {
                $roleId = DB::table('Roles')->insertGetId([
                    'RoleName' => $roleName,
                    'RoleDescription' => $roleName,
                ]);
            }

            DB::table('PersonRole')->insert([
                'PersonID' => $user->PersonID,
                'RoleID' => $roleId,
            ]);
        }

        return $user;
    }

    /**
     * Creates a user with the given roles and returns the Authorization
     * header for a real Sanctum token, since the API also runs a
     * `token.expiry` middleware that inspects the raw bearer token.
     */
    private function authHeadersForRoles(array $roleNames): array
    {
        $user = $this->createUserWithRoles($roleNames);
        $token = $user->createToken('test-token')->plainTextToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    private function seedGame(): int
    {
        return DB::table('Games')->insertGetId([
            'Title' => 'Original Title',
            'GameDescription' => 'Original description',
        ]);
    }

    public function test_store_is_forbidden_for_user_without_required_role(): void
    {
        $headers = $this->authHeadersForRoles(['Servant']);

        $response = $this->withHeaders($headers)->postJson('/api/games', [
            'title' => 'New Game',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('Games', 0);
    }

    public function test_store_is_allowed_for_super_admin(): void
    {
        $headers = $this->authHeadersForRoles(['SuperAdmin']);

        $response = $this->withHeaders($headers)->postJson('/api/games', [
            'title' => 'New Game',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('ok', true);
        $this->assertDatabaseCount('Games', 1);
    }

    public function test_store_is_allowed_for_admin_qetaa(): void
    {
        $headers = $this->authHeadersForRoles(['AdminQetaa']);

        $response = $this->withHeaders($headers)->postJson('/api/games', [
            'title' => 'New Game',
        ]);

        $response->assertStatus(201);
    }

    public function test_update_is_forbidden_for_user_without_required_role(): void
    {
        $gameId = $this->seedGame();
        $headers = $this->authHeadersForRoles(['Servant']);

        $response = $this->withHeaders($headers)->putJson("/api/games/{$gameId}", [
            'title' => 'Hacked Title',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('Games', [
            'GameID' => $gameId,
            'Title' => 'Original Title',
        ]);
    }

    public function test_update_is_allowed_for_authorized_role(): void
    {
        $gameId = $this->seedGame();
        $headers = $this->authHeadersForRoles(['SuperAdmin']);

        $response = $this->withHeaders($headers)->putJson("/api/games/{$gameId}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('Games', [
            'GameID' => $gameId,
            'Title' => 'Updated Title',
        ]);
    }

    public function test_destroy_is_forbidden_for_user_without_required_role(): void
    {
        $gameId = $this->seedGame();
        $headers = $this->authHeadersForRoles(['Servant']);

        $response = $this->withHeaders($headers)->deleteJson("/api/games/{$gameId}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('Games', ['GameID' => $gameId]);
    }

    public function test_destroy_is_allowed_for_authorized_role(): void
    {
        $gameId = $this->seedGame();
        $headers = $this->authHeadersForRoles(['AdminQetaa']);

        $response = $this->withHeaders($headers)->deleteJson("/api/games/{$gameId}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('Games', ['GameID' => $gameId]);
    }

    public function test_index_remains_accessible_to_any_authenticated_user(): void
    {
        $this->seedGame();
        $headers = $this->authHeadersForRoles(['Servant']);

        $response = $this->withHeaders($headers)->getJson('/api/games');

        $response->assertStatus(200)
            ->assertJsonPath('ok', true);
    }
}
