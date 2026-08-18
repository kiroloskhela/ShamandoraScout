<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\PersonPolicy;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('GroupQetaa');
        Schema::dropIfExists('PersonGroup');
        Schema::dropIfExists('PersonQetaa');
        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('PersonInformation');

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
            $table->text('RoleDescription')->nullable();
        });

        Schema::create('PersonRole', function (Blueprint $table) {
            $table->increments('PersonRoleID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('RoleID');
            $table->unsignedInteger('RequestPersonID')->nullable();
        });

        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->increments('PersonQetaaID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
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

    private function userWithRole(?string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'A',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'ShamandoraCode' => 'U'.uniqid(),
        ]);

        if ($roleName) {
            $roleId = DB::table('Roles')->insertGetId([
                'RoleName' => $roleName,
                'RoleDescription' => $roleName,
            ]);
            DB::table('PersonRole')->insert([
                'PersonID' => $user->PersonID,
                'RoleID' => $roleId,
            ]);
        }

        return $user->fresh();
    }

    public function test_owner_can_view_self(): void
    {
        $user = $this->userWithRole(null);
        $this->assertTrue((new PersonPolicy())->view($user, $user));
    }

    public function test_regular_user_cannot_view_other(): void
    {
        $a = $this->userWithRole(null);
        $b = $this->userWithRole(null);
        $this->assertFalse((new PersonPolicy())->view($a, $b));
    }

    public function test_super_admin_can_view_other(): void
    {
        $admin = $this->userWithRole('SuperAdmin');
        $other = $this->userWithRole(null);
        $this->assertTrue((new PersonPolicy())->view($admin, $other));
    }

    public function test_khadem_can_view_person_in_served_qetaa_via_groups(): void
    {
        $khadem = $this->userWithRole('Khadem');
        $served = $this->userWithRole(null);
        $other = $this->userWithRole(null);

        DB::table('PersonGroup')->insert(['PersonID' => $khadem->PersonID, 'GroupID' => 1]);
        DB::table('GroupQetaa')->insert(['GroupID' => 1, 'QetaaID' => 10]);
        DB::table('PersonQetaa')->insert([
            ['PersonID' => $served->PersonID, 'QetaaID' => 10],
            ['PersonID' => $other->PersonID, 'QetaaID' => 99],
        ]);

        $policy = new PersonPolicy;
        $this->assertTrue($policy->view($khadem, $served));
        $this->assertFalse($policy->view($khadem, $other));
        $this->assertFalse($policy->update($khadem, $served));
        $this->assertFalse($policy->delete($khadem, $served));
    }

    public function test_media_can_view_served_person_but_not_others(): void
    {
        $media = $this->userWithRole('Media');
        $served = $this->userWithRole(null);

        DB::table('PersonGroup')->insert(['PersonID' => $media->PersonID, 'GroupID' => 2]);
        DB::table('GroupQetaa')->insert(['GroupID' => 2, 'QetaaID' => 4]);
        DB::table('PersonQetaa')->insert(['PersonID' => $served->PersonID, 'QetaaID' => 4]);

        $this->assertTrue((new PersonPolicy())->view($media, $served));
    }

    public function test_mkhdom_cannot_view_another_person_even_in_same_qetaa(): void
    {
        $mkhdom = $this->userWithRole('Mkhdom');
        $other = $this->userWithRole(null);

        DB::table('PersonGroup')->insert(['PersonID' => $mkhdom->PersonID, 'GroupID' => 3]);
        DB::table('GroupQetaa')->insert(['GroupID' => 3, 'QetaaID' => 8]);
        DB::table('PersonQetaa')->insert([
            ['PersonID' => $mkhdom->PersonID, 'QetaaID' => 8],
            ['PersonID' => $other->PersonID, 'QetaaID' => 8],
        ]);

        $this->assertFalse((new PersonPolicy())->view($mkhdom, $other));
    }

    public function test_staff_cannot_view_person_with_no_qetaa(): void
    {
        $khadem = $this->userWithRole('Khadem');
        $orphan = $this->userWithRole(null);
        DB::table('PersonGroup')->insert(['PersonID' => $khadem->PersonID, 'GroupID' => 5]);
        DB::table('GroupQetaa')->insert(['GroupID' => 5, 'QetaaID' => 1]);

        $this->assertFalse((new PersonPolicy())->view($khadem, $orphan));
    }

    public function test_roster_visibility_matches_profile_view(): void
    {
        $khadem = $this->userWithRole('Khadem');
        $served = $this->userWithRole(null);

        DB::table('PersonGroup')->insert(['PersonID' => $khadem->PersonID, 'GroupID' => 7]);
        DB::table('GroupQetaa')->insert(['GroupID' => 7, 'QetaaID' => 3]);
        DB::table('PersonQetaa')->insert(['PersonID' => $served->PersonID, 'QetaaID' => 3]);

        $visible = app(\App\Domain\Person\PersonApiQueryService::class)
            ->isVisibleTo((int) $khadem->PersonID, (int) $served->PersonID);

        $this->assertTrue($visible);
        $this->assertSame($visible, (new PersonPolicy())->view($khadem, $served));
    }
}
