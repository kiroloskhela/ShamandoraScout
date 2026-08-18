<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Games writes require SuperAdmin (or api.games.mutate). Staff may list/view.
 * These legacy tables are not managed by Laravel migrations, so the test
 * builds a minimal sqlite schema for this suite only.
 */
class GamesApiAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('Games');
        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('PersonInformation');
        Schema::dropIfExists('personal_access_tokens');

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
            $table->text('RoleDescription')->nullable();
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
            $table->unsignedInteger('RequestPersonID')->nullable();
        });

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

    public function test_unauthenticated_store_is_rejected(): void
    {
        $response = $this->postJson('/api/games', [
            'title' => 'New Game',
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseCount('Games', 0);
    }

    public function test_store_is_forbidden_for_non_superadmin(): void
    {
        $headers = $this->authHeadersForRoles(['Servant']);

        $response = $this->withHeaders($headers)->postJson('/api/games', [
            'title' => 'New Game',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('Games', 0);
    }

    public function test_store_is_allowed_for_superadmin(): void
    {
        $headers = $this->authHeadersForRoles(['SuperAdmin']);

        $response = $this->withHeaders($headers)->postJson('/api/games', [
            'title' => 'New Game',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('ok', true);
        $this->assertDatabaseCount('Games', 1);
    }

    public function test_update_is_forbidden_for_non_superadmin(): void
    {
        $gameId = $this->seedGame();
        $headers = $this->authHeadersForRoles(['Finance']);

        $response = $this->withHeaders($headers)->putJson("/api/games/{$gameId}", [
            'title' => 'Updated Title',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('Games', [
            'GameID' => $gameId,
            'Title' => 'Original Title',
        ]);
    }

    public function test_destroy_is_forbidden_for_non_superadmin(): void
    {
        $gameId = $this->seedGame();
        $headers = $this->authHeadersForRoles(['Inventory']);

        $response = $this->withHeaders($headers)->deleteJson("/api/games/{$gameId}");

        $response->assertForbidden();
        $this->assertDatabaseHas('Games', ['GameID' => $gameId]);
    }

    public function test_index_is_forbidden_without_staff_permission(): void
    {
        $this->seedGame();
        $headers = $this->authHeadersForRoles(['Servant']);

        $this->withHeaders($headers)->getJson('/api/games')->assertForbidden();
    }

    public function test_index_is_accessible_to_staff_role(): void
    {
        $this->seedGame();
        $headers = $this->authHeadersForRoles(['Khadem']);

        $response = $this->withHeaders($headers)->getJson('/api/games');

        $response->assertStatus(200)
            ->assertJsonPath('ok', true);
    }
}
