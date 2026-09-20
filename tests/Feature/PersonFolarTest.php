<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonFolarTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('PersonFolarTest requires sqlite in-memory.');
        }

        foreach ([
            'PersonFolar',
            'Folar',
            'PersonGroup',
            'GroupQetaa',
            'PersonSanaMarhala',
            'PersonQetaa',
            'SanaMarhala',
            'Qetaa',
            'PersonRole',
            'Roles',
            'PersonImages',
            'PersonInformation',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
        });

        Schema::create('PersonImages', function (Blueprint $table) {
            $table->increments('PersonImageID');
            $table->unsignedInteger('PersonID')->nullable();
            $table->string('PersonSystemImagePath')->nullable();
            $table->string('PersonSystemImageThumbnailPath')->nullable();
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

        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName');
        });

        Schema::create('SanaMarhala', function (Blueprint $table) {
            $table->increments('SanaMarhalaID');
            $table->string('SanaMarhalaName');
        });

        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
            $table->primary(['PersonID', 'QetaaID']);
        });

        Schema::create('PersonSanaMarhala', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('SanaMarhalaID');
            $table->primary(['PersonID', 'SanaMarhalaID']);
        });

        Schema::create('GroupQetaa', function (Blueprint $table) {
            $table->unsignedInteger('GroupID');
            $table->unsignedInteger('QetaaID');
            $table->primary(['GroupID', 'QetaaID']);
        });

        Schema::create('PersonGroup', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('GroupID');
            $table->primary(['PersonID', 'GroupID']);
        });

        $migration = require base_path('database/migrations/2026_09_21_000001_create_folar_tables.php');
        $migration->up();
    }

    private function createKhademAndScout(): array
    {
        $khadem = User::create([
            'FirstName' => 'Khadem',
            'SecondName' => 'User',
            'ShamandoraCode' => 'K1',
        ]);
        $this->grantStaffRole($khadem, 'Khadem');

        $qetaaId = DB::table('Qetaa')->insertGetId(['QetaaName' => 'أشبال']);
        $sanaId = DB::table('SanaMarhala')->insertGetId(['SanaMarhalaName' => 'ثالثة إعدادي']);

        $scout = User::create([
            'FirstName' => 'John',
            'SecondName' => 'Edward',
            'ShamandoraCode' => 'S1',
        ]);

        DB::table('PersonQetaa')->insert(['PersonID' => $scout->PersonID, 'QetaaID' => $qetaaId]);
        DB::table('PersonSanaMarhala')->insert(['PersonID' => $scout->PersonID, 'SanaMarhalaID' => $sanaId]);

        $groupId = 1;
        DB::table('GroupQetaa')->insert(['GroupID' => $groupId, 'QetaaID' => $qetaaId]);
        DB::table('PersonGroup')->insert(['PersonID' => $khadem->PersonID, 'GroupID' => $groupId]);
        DB::table('PersonGroup')->insert(['PersonID' => $scout->PersonID, 'GroupID' => $groupId]);

        $folarId = (int) DB::table('Folar')->where('FolarName', 'فولار ساده')->value('FolarID');

        return compact('khadem', 'scout', 'folarId');
    }

    public function test_migration_seeds_five_folar_names(): void
    {
        $this->assertSame(5, DB::table('Folar')->count());
        $this->assertDatabaseHas('Folar', ['FolarName' => 'فولار براعم']);
        $this->assertDatabaseHas('Folar', ['FolarName' => 'فولار الخشبيه']);
    }

    public function test_khadem_can_assign_and_clear_folar_for_served_person(): void
    {
        ['khadem' => $khadem, 'scout' => $scout, 'folarId' => $folarId] = $this->createKhademAndScout();

        $this->actingAs($khadem)
            ->get(route('person.folar'))
            ->assertOk()
            ->assertSee('فولار ساده', false);

        $this->actingAs($khadem)
            ->post(route('person.folar.sync'), [
                'folar' => [$scout->PersonID => $folarId],
            ])
            ->assertRedirect(route('person.folar'));

        $this->assertDatabaseHas('PersonFolar', [
            'PersonID' => $scout->PersonID,
            'FolarID' => $folarId,
        ]);

        $this->actingAs($khadem)
            ->post(route('person.folar.sync'), [
                'folar' => [$scout->PersonID => ''],
            ])
            ->assertRedirect(route('person.folar'));

        $this->assertDatabaseMissing('PersonFolar', [
            'PersonID' => $scout->PersonID,
        ]);
    }

    public function test_rejects_out_of_scope_person_without_writing(): void
    {
        ['khadem' => $khadem, 'scout' => $scout, 'folarId' => $folarId] = $this->createKhademAndScout();

        $otherQetaaId = DB::table('Qetaa')->insertGetId(['QetaaName' => 'جوالة']);
        $victim = User::create([
            'FirstName' => 'Other',
            'SecondName' => 'Scout',
            'ShamandoraCode' => 'V1',
        ]);
        DB::table('PersonQetaa')->insert(['PersonID' => $victim->PersonID, 'QetaaID' => $otherQetaaId]);
        DB::table('PersonFolar')->insert([
            'PersonID' => $victim->PersonID,
            'FolarID' => $folarId,
        ]);

        $this->actingAs($khadem)
            ->from(route('person.folar'))
            ->post(route('person.folar.sync'), [
                'folar' => [
                    $scout->PersonID => $folarId,
                    $victim->PersonID => '',
                ],
            ])
            ->assertRedirect(route('person.folar'))
            ->assertSessionHasErrors('folar');

        $this->assertDatabaseMissing('PersonFolar', ['PersonID' => $scout->PersonID]);
        $this->assertDatabaseHas('PersonFolar', [
            'PersonID' => $victim->PersonID,
            'FolarID' => $folarId,
        ]);
    }

    public function test_rejects_invalid_folar_id(): void
    {
        ['khadem' => $khadem, 'scout' => $scout] = $this->createKhademAndScout();

        $this->actingAs($khadem)
            ->from(route('person.folar'))
            ->post(route('person.folar.sync'), [
                'folar' => [$scout->PersonID => 9999],
            ])
            ->assertRedirect(route('person.folar'))
            ->assertSessionHasErrors('folar');

        $this->assertDatabaseCount('PersonFolar', 0);
    }

    public function test_guest_and_mkhdom_cannot_open_folar_page(): void
    {
        $this->get(route('person.folar'))->assertRedirect();

        $mkhdom = User::create([
            'FirstName' => 'Served',
            'SecondName' => 'Person',
            'ShamandoraCode' => 'M1',
        ]);
        $this->grantStaffRole($mkhdom, 'Mkhdom');

        $this->actingAs($mkhdom)
            ->get(route('person.folar'))
            ->assertRedirect(route('login-auth'));
    }
}
