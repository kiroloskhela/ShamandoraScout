<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QetaaTreeRenameGroupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['GroupQetaa', 'PersonGroup', 'GroupTable', 'PersonRole', 'Roles', 'PersonInformation'] as $table) {
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
            $table->string('RoleName')->nullable();
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
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

        Schema::create('GroupTable', function (Blueprint $table) {
            $table->unsignedInteger('GroupID')->primary();
            $table->unsignedInteger('GroupTypeID');
            $table->unsignedInteger('IncludedUnderGroupID')->default(0);
            $table->string('GroupName')->nullable();
        });
    }

    /**
     * @dataProvider servedRenameTypes
     */
    public function test_served_user_can_rename_team_or_patrol(int $typeId): void
    {
        $user = $this->createUser();
        $this->seedServedGroup($user->PersonID, groupId: 20, qetaaId: 5, typeId: $typeId, parentId: 4, name: 'Old');

        $this->actingAs($user)
            ->patchJson(route('qetaa.renameGroup', ['groupId' => 20]), [
                'GroupName' => 'New name',
                'GroupTypeID' => 1,
                'IncludedUnderGroupID' => 99,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $row = DB::table('GroupTable')->where('GroupID', 20)->first();
        $this->assertSame('New name', $row->GroupName);
        $this->assertSame($typeId, (int) $row->GroupTypeID);
        $this->assertSame(4, (int) $row->IncludedUnderGroupID);
    }

    public static function servedRenameTypes(): array
    {
        return [
            'team' => [2],
            'patrol' => [3],
        ];
    }

    public function test_user_cannot_rename_group_in_unserved_qetaa(): void
    {
        $user = $this->createUser();
        DB::table('PersonGroup')->insert(['PersonID' => $user->PersonID, 'GroupID' => 1]);
        DB::table('GroupQetaa')->insert(['GroupID' => 1, 'QetaaID' => 5]);
        DB::table('GroupTable')->insert([
            'GroupID' => 9,
            'GroupTypeID' => 2,
            'IncludedUnderGroupID' => 0,
            'GroupName' => 'Foreign',
        ]);
        DB::table('GroupQetaa')->insert(['GroupID' => 9, 'QetaaID' => 99]);

        $this->actingAs($user)
            ->patchJson(route('qetaa.renameGroup', ['groupId' => 9]), ['GroupName' => 'Hacked'])
            ->assertStatus(403);

        $this->assertSame('Foreign', DB::table('GroupTable')->where('GroupID', 9)->value('GroupName'));
    }

    public function test_wrong_group_type_is_rejected(): void
    {
        $user = $this->createUser();
        $this->seedServedGroup($user->PersonID, groupId: 8, qetaaId: 5, typeId: 1, parentId: 0, name: 'Sector');

        $this->actingAs($user)
            ->patchJson(route('qetaa.renameGroup', ['groupId' => 8]), ['GroupName' => 'Nope'])
            ->assertStatus(422);

        $this->assertSame('Sector', DB::table('GroupTable')->where('GroupID', 8)->value('GroupName'));
    }

    public function test_guest_cannot_rename(): void
    {
        $this->patchJson(route('qetaa.renameGroup', ['groupId' => 20]), ['GroupName' => 'X'])
            ->assertUnauthorized();
    }

    private function createUser(): User
    {
        return User::create([
            'FirstName' => 'Tree',
            'SecondName' => 'Rename',
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'RN'.uniqid(),
        ]);
    }

    private function seedServedGroup(int $personId, int $groupId, int $qetaaId, int $typeId, int $parentId, string $name): void
    {
        DB::table('PersonGroup')->insert(['PersonID' => $personId, 'GroupID' => $groupId]);
        DB::table('GroupQetaa')->insert(['GroupID' => $groupId, 'QetaaID' => $qetaaId]);
        DB::table('GroupTable')->insert([
            'GroupID' => $groupId,
            'GroupTypeID' => $typeId,
            'IncludedUnderGroupID' => $parentId,
            'GroupName' => $name,
        ]);
    }
}
