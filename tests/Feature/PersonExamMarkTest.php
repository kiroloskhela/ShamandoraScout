<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonExamMarkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('PersonExamMarkTest requires sqlite in-memory.');
        }

        foreach ([
            'PersonExamMark',
            'PersonGroup',
            'GroupQetaa',
            'PersonSanaMarhala',
            'PersonQetaa',
            'SanaMarhala',
            'Qetaa',
            'PersonRole',
            'Roles',
            'PersonImages',
            'PersonPhoneNumbers',
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

        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('PersonPersonalMobileNumber')->nullable();
            $table->string('FatherMobileNumber')->nullable();
            $table->string('MotherMobileNumber')->nullable();
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
            $table->unsignedInteger('SanaID')->nullable();
            $table->unsignedInteger('MarhalaID')->nullable();
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

        Schema::create('PersonExamMark', function (Blueprint $table) {
            $table->increments('ExamMarkID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('ServentID');
            $table->unsignedInteger('QetaaID');
            $table->unsignedInteger('SanaMarhalaID');
            $table->integer('TheoreticalMark');
            $table->integer('PracticalMark');
            $table->date('ExamDate');
            $table->string('Note', 500)->nullable();
        });
    }

    private function createAdminAndScout(): array
    {
        $roleId = DB::table('Roles')->insertGetId([
            'RoleName' => 'SuperAdmin',
            'RoleDescription' => 'test',
        ]);

        $admin = User::create([
            'FirstName' => 'Admin',
            'SecondName' => 'User',
            'ShamandoraCode' => 'A1',
        ]);
        DB::table('PersonRole')->insert([
            'PersonID' => $admin->PersonID,
            'RoleID' => $roleId,
        ]);

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
        DB::table('PersonGroup')->insert(['PersonID' => $admin->PersonID, 'GroupID' => $groupId]);
        DB::table('PersonGroup')->insert(['PersonID' => $scout->PersonID, 'GroupID' => $groupId]);

        return compact('admin', 'scout', 'qetaaId', 'sanaId');
    }

    public function test_can_store_whole_number_marks_including_over_100(): void
    {
        ['admin' => $admin, 'scout' => $scout, 'qetaaId' => $qetaaId, 'sanaId' => $sanaId] = $this->createAdminAndScout();

        $response = $this->actingAs($admin)->post(route('personexammark.insert'), [
            'person_id' => $scout->PersonID,
            'qetaa_id' => $qetaaId,
            'sana_marhala_id' => $sanaId,
            'theoretical_mark' => 110,
            'practical_mark' => 70,
            'exam_date' => '2024-05-01',
            'note' => 'نهائي',
        ]);

        $response->assertRedirect(route('personexammark.index'));

        $this->assertDatabaseHas('PersonExamMark', [
            'PersonID' => $scout->PersonID,
            'ServentID' => $admin->PersonID,
            'QetaaID' => $qetaaId,
            'SanaMarhalaID' => $sanaId,
            'TheoreticalMark' => 110,
            'PracticalMark' => 70,
            'Note' => 'نهائي',
        ]);
    }

    public function test_rejects_decimal_marks(): void
    {
        ['admin' => $admin, 'scout' => $scout, 'qetaaId' => $qetaaId, 'sanaId' => $sanaId] = $this->createAdminAndScout();

        $response = $this->actingAs($admin)->from(route('personexammark.create'))->post(route('personexammark.insert'), [
            'person_id' => $scout->PersonID,
            'qetaa_id' => $qetaaId,
            'sana_marhala_id' => $sanaId,
            'theoretical_mark' => 80.5,
            'practical_mark' => 70,
            'exam_date' => '2024-05-01',
        ]);

        $response->assertRedirect(route('personexammark.create'));
        $response->assertSessionHasErrors('theoretical_mark');
        $this->assertDatabaseCount('PersonExamMark', 0);
    }

    public function test_index_lists_exam_marks_page(): void
    {
        ['admin' => $admin, 'scout' => $scout, 'qetaaId' => $qetaaId, 'sanaId' => $sanaId] = $this->createAdminAndScout();

        DB::table('PersonExamMark')->insert([
            'PersonID' => $scout->PersonID,
            'ServentID' => $admin->PersonID,
            'QetaaID' => $qetaaId,
            'SanaMarhalaID' => $sanaId,
            'TheoreticalMark' => 80,
            'PracticalMark' => 70,
            'ExamDate' => '2023-06-01',
            'Note' => null,
        ]);

        $this->actingAs($admin)
            ->get(route('personexammark.index'))
            ->assertOk()
            ->assertSee('تسجيل درجات الامتحان', false);

        $row = DB::table('PersonExamMark')
            ->join('SanaMarhala', 'SanaMarhala.SanaMarhalaID', '=', 'PersonExamMark.SanaMarhalaID')
            ->where('PersonExamMark.PersonID', $scout->PersonID)
            ->select('PersonExamMark.*', 'SanaMarhala.SanaMarhalaName')
            ->first();

        $this->assertSame('ثالثة إعدادي', $row->SanaMarhalaName);
        $this->assertSame(80, (int) $row->TheoreticalMark);
        $this->assertSame(70, (int) $row->PracticalMark);
    }
}
