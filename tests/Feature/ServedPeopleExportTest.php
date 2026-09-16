<?php

namespace Tests\Feature;

use App\Domain\Person\ServedPeopleExportService;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServedPeopleExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->createSchema();
    }

    public function test_khadem_dropdown_is_served_qetaas_only(): void
    {
        $khadem = $this->createUserWithRole('Khadem');
        $this->seedQetaa(1, 'كشافة');
        $this->seedQetaa(2, 'جوالة');
        DB::table('PersonGroup')->insert(['PersonID' => $khadem->PersonID, 'GroupID' => 10]);
        DB::table('GroupQetaa')->insert(['GroupID' => 10, 'QetaaID' => 1]);

        $this->actingAs($khadem)
            ->get(route('export.served-people'))
            ->assertOk()
            ->assertSee('كشافة', false)
            ->assertDontSee('جوالة', false);
    }

    public function test_super_admin_sees_all_qetaas(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        $this->seedQetaa(1, 'كشافة');
        $this->seedQetaa(2, 'جوالة');

        $this->actingAs($admin)
            ->get(route('export.served-people'))
            ->assertOk()
            ->assertSee('كشافة', false)
            ->assertSee('جوالة', false);
    }

    public function test_admin_qetaa_uses_person_qetaa_without_groups(): void
    {
        $admin = $this->createUserWithRole('AdminQetaa');
        $this->seedQetaa(1, 'كشافة');
        $this->seedQetaa(2, 'جوالة');
        DB::table('PersonQetaa')->insert(['PersonID' => $admin->PersonID, 'QetaaID' => 1]);

        $this->actingAs($admin)
            ->get(route('export.served-people'))
            ->assertOk()
            ->assertSee('كشافة', false)
            ->assertDontSee('جوالة', false);
    }

    public function test_khadem_cannot_download_foreign_qetaa(): void
    {
        $khadem = $this->createUserWithRole('Khadem');
        $this->seedQetaa(1, 'كشافة');
        $this->seedQetaa(2, 'جوالة');
        $seasonId = $this->seedSeason();
        DB::table('PersonGroup')->insert(['PersonID' => $khadem->PersonID, 'GroupID' => 10]);
        DB::table('GroupQetaa')->insert(['GroupID' => 10, 'QetaaID' => 1]);

        $this->actingAs($khadem)
            ->post(route('export.served-people.download'), [
                'qetaa_id' => 2,
                'season_id' => $seasonId,
            ])
            ->assertForbidden();
    }

    public function test_unmarked_attendance_stays_blank_and_booking_status_is_used(): void
    {
        $admin = $this->createUserWithRole('SuperAdmin');
        $this->seedQetaa(1, 'كشافة');
        $seasonId = $this->seedSeason();
        $person = $this->seedPersonInQetaa(1, 'Booked', 'Scout');
        $unmarked = $this->seedPersonInQetaa(1, 'Quiet', 'Scout');

        $meetingId = $this->seedSeasonEvent($seasonId, 1, 'Meeting', takesReservation: false);
        $campId = $this->seedSeasonEvent($seasonId, 1, 'Camp', takesReservation: true);

        DB::table('Attendance')->insert([
            'SeasonEventID' => $meetingId,
            'ServedID' => $person->PersonID,
            'ServentID' => $admin->PersonID,
            'AttendanceStatus' => 'present',
        ]);

        $bookingId = (int) DB::table('SeasonEventParticipantFinance')->insertGetId([
            'SeasonEventID' => $campId,
            'PersonID' => $person->PersonID,
            'IsRefunded' => 0,
        ]);
        DB::table('SeasonEventBookingAttendance')->insert([
            'SeasonEventParticipantFinanceID' => $bookingId,
            'SeasonEventID' => $campId,
            'AttendanceStatus' => 'outside',
            'ServentID' => $admin->PersonID,
        ]);

        $workbook = app(ServedPeopleExportService::class)->build(1, $seasonId);
        $attendance = collect($workbook['sheets'][3]['rows'])->keyBy('FullName');

        $this->assertSame('present', $attendance['Booked Scout']['Meeting (2026-01-01)'] ?? null);
        $this->assertSame('outside', $attendance['Booked Scout']['Camp (2026-01-01)'] ?? null);
        $this->assertSame('', $attendance['Quiet Scout']['Meeting (2026-01-01)'] ?? 'missing');
        $this->assertSame('', $attendance['Quiet Scout']['Camp (2026-01-01)'] ?? 'missing');
        $this->assertSame(2, $workbook['people_count']);
        $this->assertArrayHasKey('PersonID', $attendance['Booked Scout']);
        $this->assertArrayHasKey('ShamandoraCode', $attendance['Booked Scout']);
    }

    public function test_medical_sheet_omits_people_without_allergies_or_disease(): void
    {
        $this->seedQetaa(1, 'كشافة');
        $seasonId = $this->seedSeason();
        $withAllergy = $this->seedPersonInQetaa(1, 'Allergic', 'Scout');
        $this->seedPersonInQetaa(1, 'Healthy', 'Scout');
        DB::table('PeopleAllergies')->insert([
            'PersonID' => $withAllergy->PersonID,
            'AllergyType' => 'Food',
            'AllergyName' => 'Peanuts',
        ]);

        $workbook = app(ServedPeopleExportService::class)->build(1, $seasonId);
        $medical = collect($workbook['sheets'][1]['rows']);
        $questions = collect($workbook['sheets'][2]['rows']);

        $this->assertSame(['Allergic Scout'], $medical->pluck('FullName')->all());
        $this->assertContains('Allergic Scout', $questions->pluck('FullName')->all());
        $this->assertContains('Healthy Scout', $questions->pluck('FullName')->all());
    }

    public function test_person_sheet_includes_father_phone(): void
    {
        $this->seedQetaa(1, 'كشافة');
        $seasonId = $this->seedSeason();
        $person = $this->seedPersonInQetaa(1, 'Named', 'Scout');
        DB::table('PersonPhoneNumbers')->insert([
            'PersonID' => $person->PersonID,
            'PersonPersonalMobileNumber' => '01011112222',
            'FatherMobileNumber' => '01099998888',
            'MotherMobileNumber' => '01033334444',
        ]);

        $workbook = app(ServedPeopleExportService::class)->build(1, $seasonId);
        $row = collect($workbook['sheets'][0]['rows'])->first();

        $this->assertSame('01099998888', $row['FatherMobileNumber'] ?? null);
        $this->assertSame('01033334444', $row['MotherMobileNumber'] ?? null);
    }

    public function test_season_grades_sheet_uses_latest_mark_for_selected_season_and_qetaa(): void
    {
        $this->seedQetaa(1, 'كشافة');
        $this->seedQetaa(2, 'جوالة');
        $seasonId = $this->seedSeason();
        $otherSeasonId = (int) DB::table('Season')->insertGetId([
            'SeasonName' => '2025',
            'SeasonYear' => 2025,
        ]);
        $admin = $this->createUserWithRole('SuperAdmin');
        $graded = $this->seedPersonInQetaa(1, 'Graded', 'Scout');
        $this->seedPersonInQetaa(1, 'Blank', 'Scout');
        $sanaId = (int) DB::table('SanaMarhala')->insertGetId(['SanaMarhalaName' => 'أولى']);

        DB::table('PersonExamMark')->insert([
            [
                'PersonID' => $graded->PersonID,
                'ServentID' => $admin->PersonID,
                'QetaaID' => 1,
                'SanaMarhalaID' => $sanaId,
                'SeasonID' => $seasonId,
                'TheoreticalMark' => 50,
                'PracticalMark' => 40,
                'ExamDate' => '2026-01-01',
                'Note' => 'older',
            ],
            [
                'PersonID' => $graded->PersonID,
                'ServentID' => $admin->PersonID,
                'QetaaID' => 1,
                'SanaMarhalaID' => $sanaId,
                'SeasonID' => $seasonId,
                'TheoreticalMark' => 90,
                'PracticalMark' => 80,
                'ExamDate' => '2026-06-01',
                'Note' => 'latest',
            ],
            [
                'PersonID' => $graded->PersonID,
                'ServentID' => $admin->PersonID,
                'QetaaID' => 1,
                'SanaMarhalaID' => $sanaId,
                'SeasonID' => $otherSeasonId,
                'TheoreticalMark' => 10,
                'PracticalMark' => 10,
                'ExamDate' => '2025-06-01',
                'Note' => 'other season',
            ],
            [
                'PersonID' => $graded->PersonID,
                'ServentID' => $admin->PersonID,
                'QetaaID' => 2,
                'SanaMarhalaID' => $sanaId,
                'SeasonID' => $seasonId,
                'TheoreticalMark' => 1,
                'PracticalMark' => 1,
                'ExamDate' => '2026-07-01',
                'Note' => 'other qetaa',
            ],
        ]);

        $workbook = app(ServedPeopleExportService::class)->build(1, $seasonId);
        $gradeSheet = collect($workbook['sheets'])->firstWhere('title', 'درجات الموسم');
        $this->assertNotNull($gradeSheet);
        $this->assertCount($workbook['people_count'], $gradeSheet['rows']);

        $grades = collect($gradeSheet['rows'])->keyBy('FullName');
        $this->assertSame(90, (int) $grades['Graded Scout']['TheoreticalMark']);
        $this->assertSame(80, (int) $grades['Graded Scout']['PracticalMark']);
        $this->assertSame(170, (int) $grades['Graded Scout']['TotalMark']);
        $this->assertSame('latest', $grades['Graded Scout']['Note']);
        $this->assertArrayHasKey('PersonID', $grades['Graded Scout']);
        $this->assertArrayHasKey('ShamandoraCode', $grades['Graded Scout']);
        $this->assertArrayNotHasKey('FirstName', $grades['Graded Scout']);
        $this->assertArrayNotHasKey('SecondName', $grades['Graded Scout']);
        $this->assertArrayNotHasKey('ThirdName', $grades['Graded Scout']);
        $this->assertArrayNotHasKey('FourthName', $grades['Graded Scout']);
        $this->assertSame('', $grades['Blank Scout']['TheoreticalMark'] ?? 'missing');
        $this->assertSame('', $grades['Blank Scout']['PracticalMark'] ?? 'missing');
        $this->assertSame('', $grades['Blank Scout']['TotalMark'] ?? 'missing');
    }

    public function test_personal_photos_sheet_links_stored_image_only(): void
    {
        $this->seedQetaa(1, 'كشافة');
        $seasonId = $this->seedSeason();
        $withPhoto = $this->seedPersonInQetaa(1, 'Photo', 'Scout');
        $this->seedPersonInQetaa(1, 'No', 'Photo');
        DB::table('PersonImages')->insert([
            'PersonID' => $withPhoto->PersonID,
            'PersonSystemImagePath' => 'persons/personal/demo.jpg',
        ]);

        $workbook = app(ServedPeopleExportService::class)->build(1, $seasonId);
        $photos = collect($workbook['sheets'])->firstWhere('title', 'Personal photos');
        $this->assertNotNull($photos);
        $this->assertCount(6, $workbook['sheets']);

        $rows = collect($photos['rows'])->keyBy('FullName');
        $this->assertArrayHasKey('PersonID', $rows['Photo Scout']);
        $this->assertArrayHasKey('ShamandoraCode', $rows['Photo Scout']);
        $this->assertStringContainsString('storage/persons/personal/demo.jpg', (string) $rows['Photo Scout']['ImageLink']);
        $this->assertStringNotContainsString('default-male.png', (string) $rows['Photo Scout']['ImageLink']);
        $this->assertSame('', $rows['No Photo']['ImageLink'] ?? 'missing');
        $this->assertSame([], $photos['photos'] ?? []);
    }

    private function seedQetaa(int $id, string $name): void
    {
        DB::table('Qetaa')->insert(['QetaaID' => $id, 'QetaaName' => $name]);
    }

    private function seedSeason(): int
    {
        return (int) DB::table('Season')->insertGetId([
            'SeasonName' => '2026',
            'SeasonYear' => 2026,
        ]);
    }

    private function seedPersonInQetaa(int $qetaaId, string $first, string $second): User
    {
        $person = User::create([
            'FirstName' => $first,
            'SecondName' => $second,
            'ThirdName' => '',
            'ShamandoraCode' => 'P'.uniqid(),
        ]);
        DB::table('PersonQetaa')->insert([
            'PersonID' => $person->PersonID,
            'QetaaID' => $qetaaId,
        ]);

        return $person;
    }

    private function seedSeasonEvent(int $seasonId, int $qetaaId, string $name, bool $takesReservation): int
    {
        $typeId = (int) DB::table('EventType')->insertGetId([
            'EventTypeName' => $name.' type',
            'TakesReservation' => $takesReservation ? 1 : 0,
        ]);
        $eventId = (int) DB::table('Event')->insertGetId([
            'EventTypeID' => $typeId,
            'EventName' => $name,
            'EventStartDate' => '2026-01-01',
            'EventEndDate' => '2026-01-01',
        ]);
        $seasonEventId = (int) DB::table('SeasonEvent')->insertGetId([
            'SeasonID' => $seasonId,
            'EventID' => $eventId,
        ]);
        DB::table('EventQetaa')->insert([
            'EventID' => $eventId,
            'QetaaID' => $qetaaId,
        ]);

        return $seasonEventId;
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::create([
            'FirstName' => 'Export',
            'SecondName' => $roleName,
            'ThirdName' => 'Test',
            'ShamandoraCode' => 'E'.uniqid(),
        ]);
        $roleId = (int) DB::table('Roles')->insertGetId(['RoleName' => $roleName]);
        DB::table('PersonRole')->insert([
            'PersonID' => $user->PersonID,
            'RoleID' => $roleId,
        ]);

        return $user->fresh();
    }

    private function createSchema(): void
    {
        foreach ([
            'SeasonEventBookingAttendance',
            'SeasonEventParticipantFinance',
            'Attendance',
            'PersonExamMark',
            'PersonEntryQuestions',
            'MarhalaEntryQuestions',
            'PeopleMedicalHistory',
            'PeopleAllergies',
            'PersonPhoneNumbers',
            'PersonSanaMarhala',
            'SanaMarhala',
            'PersonQetaa',
            'EventQetaa',
            'GroupQetaa',
            'PersonGroup',
            'GroupTable',
            'SeasonEvent',
            'Event',
            'EventType',
            'Qetaa',
            'Season',
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
            $table->string('ScoutJoiningYear')->nullable();
            $table->string('RaqamQawmy')->nullable();
        });
        Schema::create('PersonImages', function (Blueprint $table) {
            $table->increments('PersonImageID');
            $table->unsignedInteger('PersonID')->nullable();
            $table->string('PersonSystemImagePath')->nullable();
            $table->string('PersonSystemImageThumbnailPath')->nullable();
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
        Schema::create('Qetaa', function (Blueprint $table) {
            $table->increments('QetaaID');
            $table->string('QetaaName')->nullable();
        });
        Schema::create('Season', function (Blueprint $table) {
            $table->increments('SeasonID');
            $table->string('SeasonName')->nullable();
            $table->integer('SeasonYear')->nullable();
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
            $table->increments('GroupID');
            $table->unsignedInteger('GroupTypeID')->nullable();
            $table->string('GroupName')->nullable();
            $table->unsignedInteger('IncludedUnderGroupID')->nullable();
        });
        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->increments('PersonQetaaID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('SanaMarhala', function (Blueprint $table) {
            $table->increments('SanaMarhalaID');
            $table->string('SanaMarhalaName')->nullable();
        });
        Schema::create('PersonSanaMarhala', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('SanaMarhalaID');
        });
        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->increments('PersonPhoneNumberID');
            $table->unsignedInteger('PersonID');
            $table->string('PersonPersonalMobileNumber')->nullable();
            $table->string('FatherMobileNumber')->nullable();
            $table->string('MotherMobileNumber')->nullable();
        });
        Schema::create('PeopleAllergies', function (Blueprint $table) {
            $table->increments('PeopleAllergyID');
            $table->unsignedInteger('PersonID');
            $table->string('AllergyType')->nullable();
            $table->string('AllergyName')->nullable();
        });
        Schema::create('PeopleMedicalHistory', function (Blueprint $table) {
            $table->increments('PeopleMedicalHistoryID');
            $table->unsignedInteger('PersonID');
            $table->string('Disease')->nullable();
            $table->string('Medication')->nullable();
            $table->unsignedTinyInteger('HasEmergencyCase')->nullable();
            $table->string('EmergencyDetails')->nullable();
        });
        Schema::create('MarhalaEntryQuestions', function (Blueprint $table) {
            $table->increments('QuestionID');
            $table->unsignedInteger('QetaaID');
            $table->string('QuestionText')->nullable();
        });
        Schema::create('PersonEntryQuestions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QuestionID');
            $table->string('Answer')->nullable();
        });
        Schema::create('EventType', function (Blueprint $table) {
            $table->increments('EventTypeID');
            $table->string('EventTypeName')->nullable();
            $table->boolean('TakesReservation')->default(false);
        });
        Schema::create('Event', function (Blueprint $table) {
            $table->increments('EventID');
            $table->unsignedInteger('EventTypeID')->nullable();
            $table->string('EventName')->nullable();
            $table->string('EventStartDate')->nullable();
            $table->string('EventEndDate')->nullable();
        });
        Schema::create('SeasonEvent', function (Blueprint $table) {
            $table->increments('SeasonEventID');
            $table->unsignedInteger('SeasonID');
            $table->unsignedInteger('EventID');
        });
        Schema::create('EventQetaa', function (Blueprint $table) {
            $table->increments('EventQetaaID');
            $table->unsignedInteger('EventID');
            $table->unsignedInteger('QetaaID');
        });
        Schema::create('Attendance', function (Blueprint $table) {
            $table->increments('AttendanceID');
            $table->unsignedInteger('SeasonEventID');
            $table->unsignedInteger('ServedID');
            $table->unsignedInteger('ServentID')->nullable();
            $table->string('AttendanceStatus');
        });
        Schema::create('SeasonEventParticipantFinance', function (Blueprint $table) {
            $table->increments('SeasonEventParticipantFinanceID');
            $table->unsignedInteger('SeasonEventID');
            $table->unsignedInteger('PersonID')->nullable();
            $table->unsignedTinyInteger('IsRefunded')->default(0);
        });
        Schema::create('SeasonEventBookingAttendance', function (Blueprint $table) {
            $table->increments('SeasonEventBookingAttendanceID');
            $table->unsignedInteger('SeasonEventParticipantFinanceID');
            $table->unsignedInteger('SeasonEventID');
            $table->string('AttendanceStatus', 20);
            $table->unsignedInteger('ServentID')->nullable();
        });
        Schema::create('PersonExamMark', function (Blueprint $table) {
            $table->increments('ExamMarkID');
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('ServentID');
            $table->unsignedInteger('QetaaID');
            $table->unsignedInteger('SanaMarhalaID');
            $table->unsignedInteger('SeasonID')->nullable();
            $table->integer('TheoreticalMark');
            $table->integer('PracticalMark');
            $table->date('ExamDate');
            $table->string('Note', 500)->nullable();
        });
    }
}
