<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Special-case API requires SuperAdmin or AdminQetaa.
 */
class PersonSpecialCaseApiAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('PersonSpecialCase');
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
            $table->string('FourthName')->nullable();
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

        Schema::create('PersonSpecialCase', function (Blueprint $table) {
            $table->increments('SpecialCaseID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('ServentID');
            $table->timestamp('CaseDate')->nullable();
            $table->text('Note')->nullable();
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
            'SecondName' => 'User',
            'ThirdName' => 'A',
            'FourthName' => 'B',
            'ShamandoraCode' => 'TST'.uniqid(),
        ]);

        foreach ($roleNames as $roleName) {
            $roleId = DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');
            if (! $roleId) {
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

        return [
            'Authorization' => "Bearer {$token}",
            '_user' => $user,
        ];
    }

    public function test_unauthenticated_index_is_rejected(): void
    {
        $this->getJson('/api/person-special-cases')->assertStatus(401);
    }

    public function test_servant_is_forbidden(): void
    {
        $headers = $this->authHeadersForRoles(['Servant']);
        unset($headers['_user']);

        $this->withHeaders($headers)
            ->getJson('/api/person-special-cases')
            ->assertForbidden()
            ->assertJsonPath('message', 'Forbidden');
    }

    public function test_superadmin_can_store_and_update(): void
    {
        $headers = $this->authHeadersForRoles(['SuperAdmin']);
        /** @var User $admin */
        $admin = $headers['_user'];
        unset($headers['_user']);

        $target = User::create([
            'FirstName' => 'Scout',
            'SecondName' => 'One',
            'ThirdName' => 'X',
            'FourthName' => 'Y',
            'ShamandoraCode' => 'SC'.uniqid(),
        ]);

        $create = $this->withHeaders($headers)->postJson('/api/person-special-cases', [
            'person_id' => $target->PersonID,
            'note' => 'Medical note',
        ]);

        $create->assertStatus(201)
            ->assertJsonPath('ok', true)
            ->assertJsonPath('case.PersonID', $target->PersonID)
            ->assertJsonPath('case.Note', 'Medical note')
            ->assertJsonPath('case.ServentID', $admin->PersonID);

        $caseId = (int) $create->json('case.SpecialCaseID');

        $this->withHeaders($headers)
            ->putJson("/api/person-special-cases/{$caseId}", [
                'note' => 'Updated note',
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('case.Note', 'Updated note');
    }

    public function test_superadmin_same_day_duplicate_is_conflict(): void
    {
        $headers = $this->authHeadersForRoles(['SuperAdmin']);
        unset($headers['_user']);

        $target = User::create([
            'FirstName' => 'Scout',
            'SecondName' => 'Two',
            'ThirdName' => 'X',
            'FourthName' => 'Y',
            'ShamandoraCode' => 'SC'.uniqid(),
        ]);

        $payload = [
            'person_id' => $target->PersonID,
            'note' => 'First',
        ];

        $this->withHeaders($headers)
            ->postJson('/api/person-special-cases', $payload)
            ->assertStatus(201);

        $this->withHeaders($headers)
            ->postJson('/api/person-special-cases', $payload)
            ->assertStatus(409)
            ->assertJsonPath('message', 'This person already has a special case today');
    }
}
