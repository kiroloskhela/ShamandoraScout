<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\LookupCache;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LookupCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Cache::flush();

        Schema::dropIfExists('PersonFolar');
        Schema::dropIfExists('PersonRole');
        Schema::dropIfExists('Roles');
        Schema::dropIfExists('Folar');
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

        Schema::create('Folar', function (Blueprint $table) {
            $table->increments('FolarID');
            $table->string('FolarName')->unique();
        });

        Schema::create('PersonFolar', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->unsignedInteger('FolarID');
        });
    }

    public function test_lookup_store_requires_display_name(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin)
            ->from(route('blood.create'))
            ->post(route('blood.insert'), ['blood_name' => ''])
            ->assertRedirect(route('blood.create'))
            ->assertSessionHasErrors('blood_name');
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

    public function test_lookup_write_busts_lookup_cache(): void
    {
        $admin = $this->createSuperAdmin();
        DB::table('BloodType')->insert(['BloodTypeName' => 'Cached']);
        LookupCache::all('BloodType');

        $this->actingAs($admin)
            ->post(route('blood.insert'), ['blood_name' => 'Fresh'])
            ->assertRedirect(route('blood.index'));

        $names = LookupCache::all('BloodType')->pluck('BloodTypeName')->all();
        $this->assertContains('Fresh', $names);
        $this->assertContains('Cached', $names);
    }

    public function test_super_admin_can_create_update_and_delete_folar_lookup(): void
    {
        $admin = $this->createSuperAdmin();

        $this->actingAs($admin)
            ->post(route('folar.insert'), ['folar_name' => 'فولار تجريبي'])
            ->assertRedirect(route('folar.index'));

        $folarId = DB::table('Folar')->where('FolarName', 'فولار تجريبي')->value('FolarID');
        $this->assertNotNull($folarId);

        $this->actingAs($admin)
            ->patch(route('folar.update', $folarId), ['folar_name' => 'فولار معدل'])
            ->assertRedirect(route('folar.index'));

        $this->assertDatabaseHas('Folar', [
            'FolarID' => $folarId,
            'FolarName' => 'فولار معدل',
        ]);

        $this->actingAs($admin)
            ->delete(route('folar.destroy', $folarId))
            ->assertRedirect(route('folar.index'));

        $this->assertDatabaseMissing('Folar', ['FolarID' => $folarId]);
    }

    public function test_cannot_delete_folar_assigned_to_a_person(): void
    {
        $admin = $this->createSuperAdmin();
        $folarId = DB::table('Folar')->insertGetId(['FolarName' => 'فولار مستخدم']);
        DB::table('PersonFolar')->insert([
            'PersonID' => $admin->PersonID,
            'FolarID' => $folarId,
        ]);

        $this->actingAs($admin)
            ->from(route('folar.index'))
            ->delete(route('folar.destroy', $folarId))
            ->assertRedirect(route('folar.index'))
            ->assertSessionHasErrors('folar');

        $this->assertDatabaseHas('Folar', ['FolarID' => $folarId]);
    }

    private function createSuperAdmin(): User
    {
        $user = User::create([
            'FirstName' => 'Super',
            'SecondName' => 'Admin',
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'L'.uniqid(),
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
