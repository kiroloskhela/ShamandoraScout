<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LookupCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('BloodType');
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

        Schema::create('BloodType', function (Blueprint $table) {
            $table->increments('BloodTypeID');
            $table->string('BloodTypeName');
        });
    }

    public function test_super_admin_can_create_update_and_delete_blood_type_lookup(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin)
            ->post(route('blood.insert'), ['blood_name' => 'Test Blood'])
            ->assertRedirect(route('blood.index'));

        $bloodTypeId = DB::table('BloodType')->where('BloodTypeName', 'Test Blood')->value('BloodTypeID');

        $this->assertNotNull($bloodTypeId);
        $this->assertDatabaseHas('BloodType', [
            'BloodTypeID' => $bloodTypeId,
            'BloodTypeName' => 'Test Blood',
        ]);

        $this->actingAs($admin)
            ->patch(route('blood.update', $bloodTypeId), ['blood_name' => 'Updated Blood'])
            ->assertRedirect(route('blood.index'));

        $this->assertDatabaseHas('BloodType', [
            'BloodTypeID' => $bloodTypeId,
            'BloodTypeName' => 'Updated Blood',
        ]);

        $this->actingAs($admin)
            ->delete(route('blood.destroy', $bloodTypeId))
            ->assertRedirect(route('blood.index'));

        $this->assertDatabaseMissing('BloodType', [
            'BloodTypeID' => $bloodTypeId,
        ]);
    }

    private function createSuperAdmin(): User
    {
        $user = User::create([
            'FirstName' => 'Super',
            'SecondName' => 'Admin',
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'L' . uniqid(),
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
}
