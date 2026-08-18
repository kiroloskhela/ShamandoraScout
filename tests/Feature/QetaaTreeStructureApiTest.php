<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QetaaTreeStructureApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'GroupQetaa',
            'PersonGroup',
            'GroupTable',
            'Qetaa',
            'PersonRole',
            'Roles',
            'PersonInformation',
            'personal_access_tokens',
        ] as $table) {
            Schema::dropIfExists($table);
        }

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

        Schema::create('Qetaa', function (Blueprint $table) {
            $table->unsignedInteger('QetaaID')->primary();
            $table->string('QetaaName')->nullable();
        });

        Schema::create('GroupTable', function (Blueprint $table) {
            $table->unsignedInteger('GroupID')->primary();
            $table->unsignedInteger('GroupTypeID');
            $table->unsignedInteger('IncludedUnderGroupID')->default(0);
            $table->string('GroupName')->nullable();
        });

        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->increments('PersonGroupID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
        });

        Schema::create('GroupQetaa', function (Blueprint $table) {
            $table->increments('GroupQetaaID');
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('QetaaID');
        });
    }

    public function test_guest_cannot_read_team_structure(): void
    {
        $this->getJson('/api/team-structure')->assertUnauthorized();
    }

    public function test_mkhdom_cannot_read_team_structure(): void
    {
        $user = $this->createUserWithRole('Mkhdom');

        $this->actingAsApi($user)->getJson('/api/team-structure')->assertForbidden();
    }

    public function test_khadem_with_no_served_qetaa_gets_empty_list(): void
    {
        $user = $this->createUserWithRole('Khadem');

        $this->actingAsApi($user)
            ->getJson('/api/team-structure')
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'person_id' => $user->PersonID,
                'qetaas' => [],
            ]);
    }

    public function test_khadem_gets_nested_served_teams_and_talaea_only(): void
    {
        $user = $this->createUserWithRole('Khadem');
        $other = $this->createUserWithRole('Khadem', 'OTHER');

        DB::table('Qetaa')->insert([
            ['QetaaID' => 5, 'QetaaName' => 'أشبال'],
            ['QetaaID' => 9, 'QetaaName' => 'جوالة'],
        ]);

        DB::table('GroupTable')->insert([
            ['GroupID' => 10, 'GroupTypeID' => 2, 'IncludedUnderGroupID' => 0, 'GroupName' => 'فريق الأمل'],
            ['GroupID' => 21, 'GroupTypeID' => 3, 'IncludedUnderGroupID' => 10, 'GroupName' => 'طليعة النسور'],
            ['GroupID' => 22, 'GroupTypeID' => 3, 'IncludedUnderGroupID' => 0, 'GroupName' => 'طليعة مباشرة'],
            ['GroupID' => 30, 'GroupTypeID' => 2, 'IncludedUnderGroupID' => 0, 'GroupName' => 'فريق آخر'],
            ['GroupID' => 99, 'GroupTypeID' => 3, 'IncludedUnderGroupID' => 10, 'GroupName' => 'طليعة مسربة'],
        ]);

        DB::table('GroupQetaa')->insert([
            ['GroupID' => 10, 'QetaaID' => 5],
            ['GroupID' => 21, 'QetaaID' => 5],
            ['GroupID' => 22, 'QetaaID' => 5],
            ['GroupID' => 30, 'QetaaID' => 9],
            ['GroupID' => 99, 'QetaaID' => 9],
        ]);

        DB::table('PersonGroup')->insert([
            ['PersonID' => $user->PersonID, 'GroupID' => 10],
            ['PersonID' => $other->PersonID, 'GroupID' => 30],
        ]);

        $response = $this->actingAsApi($user)
            ->getJson('/api/team-structure?id='.$other->PersonID)
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('person_id', $user->PersonID)
            ->assertJsonMissingPath('qetaas.0.teams.0.people')
            ->json();

        $this->assertCount(1, $response['qetaas']);
        $this->assertSame(5, $response['qetaas'][0]['qetaa_id']);
        $this->assertSame('أشبال', $response['qetaas'][0]['qetaa_name']);
        $this->assertSame(10, $response['qetaas'][0]['teams'][0]['group_id']);
        $this->assertSame([['group_id' => 21, 'group_name' => 'طليعة النسور']], $response['qetaas'][0]['teams'][0]['talaea']);
        $this->assertSame([['group_id' => 22, 'group_name' => 'طليعة مباشرة']], $response['qetaas'][0]['direct_talaea']);
        $this->assertStringNotContainsString('جوالة', json_encode($response, JSON_UNESCAPED_UNICODE));
        $this->assertStringNotContainsString('طليعة مسربة', json_encode($response, JSON_UNESCAPED_UNICODE));
    }

    public function test_team_without_patrols_returns_empty_talaea(): void
    {
        $user = $this->createUserWithRole('Khadem');

        DB::table('Qetaa')->insert(['QetaaID' => 5, 'QetaaName' => 'أشبال']);
        DB::table('GroupTable')->insert([
            'GroupID' => 10,
            'GroupTypeID' => 2,
            'IncludedUnderGroupID' => 0,
            'GroupName' => 'فريق بلا قطاع آخر',
        ]);
        DB::table('GroupQetaa')->insert(['GroupID' => 10, 'QetaaID' => 5]);
        DB::table('PersonGroup')->insert(['PersonID' => $user->PersonID, 'GroupID' => 10]);

        $this->actingAsApi($user)
            ->getJson('/api/team-structure')
            ->assertOk()
            ->assertJsonPath('qetaas.0.qetaa_id', 5)
            ->assertJsonPath('qetaas.0.teams.0.talaea', [])
            ->assertJsonPath('qetaas.0.direct_talaea', []);
    }

    private function createUserWithRole(string $roleName, string $code = 'TS'): User
    {
        $user = User::create([
            'FirstName' => 'Api',
            'SecondName' => $roleName,
            'ThirdName' => 'Test',
            'ShamandoraCode' => $code.uniqid(),
        ]);

        $roleId = (int) DB::table('Roles')->where('RoleName', $roleName)->value('RoleID');
        if (! $roleId) {
            $roleId = (int) DB::table('Roles')->insertGetId(['RoleName' => $roleName]);
        }

        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);

        return $user->fresh();
    }

    private function actingAsApi(User $user): self
    {
        $token = $user->createToken('test-token')->plainTextToken;

        return $this->withHeaders(['Authorization' => "Bearer {$token}"]);
    }
}
