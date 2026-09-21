<?php

namespace Tests\Unit;

use App\Domain\Enrolment\MigrateEnrolmentService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class MigrateEnrolmentServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('NewUsersInformation');

        Schema::create('NewUsersInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->unsignedInteger('QetaaID');
            $table->boolean('IsApproved')->default(false);
        });
    }

    public function test_one_failure_does_not_stop_other_migrations(): void
    {
        DB::table('NewUsersInformation')->insert([
            ['PersonID' => 1, 'QetaaID' => 10, 'IsApproved' => 1],
            ['PersonID' => 2, 'QetaaID' => 10, 'IsApproved' => 1],
            ['PersonID' => 3, 'QetaaID' => 10, 'IsApproved' => 1],
        ]);

        $service = $this->partialMock(MigrateEnrolmentService::class, function ($mock) {
            $mock->shouldReceive('migrateOneById')
                ->with(1)->once()->andReturn(101)
                ->shouldReceive('migrateOneById')
                ->with(2)->once()->andThrow(new RuntimeException('duplicate key'))
                ->shouldReceive('migrateOneById')
                ->with(3)->once()->andReturn(103);
        });

        $result = $service->migrateApprovedForQetaa(10);

        $this->assertSame(2, $result->migrated_count);
        $this->assertSame(1, $result->failed_count);
        $this->assertCount(1, $result->failures);
        $this->assertSame(2, $result->failures[0]['person_id']);
        $this->assertSame('duplicate key', $result->failures[0]['message']);
    }

    public function test_migrate_copies_enrolment_folar_onto_person(): void
    {
        $this->createMigrateOneSchema();

        $folarId = (int) DB::table('Folar')->insertGetId(['FolarName' => 'فولار ساده'], 'FolarID');

        DB::table('NewUsersInformation')->insert($this->enrolmentRow([
            'PersonID' => 7,
            'FolarID' => $folarId,
        ]));

        $newId = (new MigrateEnrolmentService)->migrateOneById(7);

        $this->assertGreaterThan(0, $newId);
        $this->assertDatabaseHas('PersonFolar', [
            'PersonID' => $newId,
            'FolarID' => $folarId,
        ]);
        $this->assertSame(0, DB::table('NewUsersInformation')->count());
    }

    public function test_migrate_skips_person_folar_when_enrolment_has_none(): void
    {
        $this->createMigrateOneSchema();

        DB::table('NewUsersInformation')->insert($this->enrolmentRow([
            'PersonID' => 8,
            'FolarID' => null,
        ]));

        $newId = (new MigrateEnrolmentService)->migrateOneById(8);

        $this->assertGreaterThan(0, $newId);
        $this->assertSame(0, DB::table('PersonFolar')->count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function enrolmentRow(array $overrides): array
    {
        return array_merge([
            'QetaaID' => 10,
            'SanaMarhalaID' => 3,
            'IsApproved' => 1,
            'FirstName' => 'A',
            'SecondName' => 'B',
            'ThirdName' => 'C',
            'FourthName' => 'D',
            'Gender' => 'Male',
            'DateOfBirth' => '2010-01-01',
            'RaqamQawmy' => '12345678901234',
            'ScoutJoiningYear' => 2024,
            'BloodTypeID' => 1,
            'FacebookProfileURL' => null,
            'InstagramProfileURL' => null,
            'PersonalEmail' => null,
            'PersonPersonalMobileNumber' => '01000000000',
            'FatherMobileNumber' => null,
            'MotherMobileNumber' => null,
            'HomePhoneNumber' => null,
            'IsOPersonalPhoneNumberHavingWhatsapp' => 0,
            'SchoolName' => null,
            'SchoolGraduationYear' => null,
            'SpiritualFatherName' => null,
            'SpiritualFatherChurchName' => null,
            'PersonalImagePath' => null,
            'ScoutImagePath' => null,
            'BuildingNumber' => '1',
            'FloorNumber' => '1',
            'AppartmentNumber' => '1',
            'MainStreetName' => null,
            'SubStreetName' => 'x',
            'ManteqaID' => 1,
            'DistrictID' => 1,
            'NearestLandmark' => null,
            'AllergyFood' => null,
            'AllergyMedicine' => null,
            'MedicalDiseases' => null,
            'MedicalMedications' => null,
            'HasEmergencyCase' => 0,
            'EmergencyDetails' => null,
            'FolarID' => null,
        ], $overrides);
    }

    private function createMigrateOneSchema(): void
    {
        foreach ([
            'NewUsersPersonEntryQuestions',
            'NewUsersInformation',
            'PersonEntryQuestions',
            'PersonFolar',
            'Folar',
            'PeopleMedicalHistory',
            'PeopleAllergies',
            'PersonalPhysicalAddress',
            'PersonImages',
            'PersonSystemPassword',
            'PersonSpiritualFatherInformation',
            'PersonSanaMarhala',
            'PersonQetaa',
            'PersonLearningInformation',
            'PersonPhoneNumbers',
            'PersonInformation',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('NewUsersInformation', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->unsignedInteger('QetaaID')->nullable();
            $table->unsignedInteger('SanaMarhalaID')->nullable();
            $table->boolean('IsApproved')->default(false);
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
            $table->string('Gender')->nullable();
            $table->string('DateOfBirth')->nullable();
            $table->string('RaqamQawmy')->nullable();
            $table->integer('ScoutJoiningYear')->nullable();
            $table->unsignedInteger('BloodTypeID')->nullable();
            $table->string('FacebookProfileURL')->nullable();
            $table->string('InstagramProfileURL')->nullable();
            $table->string('PersonalEmail')->nullable();
            $table->string('PersonPersonalMobileNumber')->nullable();
            $table->string('FatherMobileNumber')->nullable();
            $table->string('MotherMobileNumber')->nullable();
            $table->string('HomePhoneNumber')->nullable();
            $table->boolean('IsOPersonalPhoneNumberHavingWhatsapp')->nullable();
            $table->string('SchoolName')->nullable();
            $table->string('SchoolGraduationYear')->nullable();
            $table->string('SpiritualFatherName')->nullable();
            $table->string('SpiritualFatherChurchName')->nullable();
            $table->string('PersonalImagePath')->nullable();
            $table->string('ScoutImagePath')->nullable();
            $table->string('BuildingNumber')->nullable();
            $table->string('FloorNumber')->nullable();
            $table->string('AppartmentNumber')->nullable();
            $table->string('MainStreetName')->nullable();
            $table->string('SubStreetName')->nullable();
            $table->unsignedInteger('ManteqaID')->nullable();
            $table->unsignedInteger('DistrictID')->nullable();
            $table->string('NearestLandmark')->nullable();
            $table->string('AllergyFood')->nullable();
            $table->string('AllergyMedicine')->nullable();
            $table->string('MedicalDiseases')->nullable();
            $table->string('MedicalMedications')->nullable();
            $table->boolean('HasEmergencyCase')->nullable();
            $table->string('EmergencyDetails')->nullable();
            $table->unsignedInteger('FolarID')->nullable();
        });

        Schema::create('NewUsersPersonEntryQuestions', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QuestionID');
            $table->string('Answer')->nullable();
        });

        Schema::create('PersonInformation', function (Blueprint $table) {
            $table->increments('PersonID');
            $table->string('ShamandoraCode')->nullable();
            $table->string('FirstName')->nullable();
            $table->string('SecondName')->nullable();
            $table->string('ThirdName')->nullable();
            $table->string('FourthName')->nullable();
            $table->string('Gender')->nullable();
            $table->string('DateOfBirth')->nullable();
            $table->string('RaqamQawmy')->nullable();
            $table->integer('ScoutJoiningYear')->nullable();
            $table->unsignedInteger('BloodTypeID')->nullable();
            $table->string('FacebookProfileURL')->nullable();
            $table->string('InstagramProfileURL')->nullable();
            $table->string('PersonalEmail')->nullable();
            $table->unsignedInteger('RequestPersonID')->nullable();
        });

        Schema::create('PersonPhoneNumbers', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('PersonPersonalMobileNumber')->nullable();
            $table->string('FatherMobileNumber')->nullable();
            $table->string('MotherMobileNumber')->nullable();
            $table->string('HomePhoneNumber')->nullable();
            $table->boolean('IsOPersonalPhoneNumberHavingWhatsapp')->nullable();
        });

        Schema::create('PersonLearningInformation', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('SchoolName')->nullable();
            $table->string('SchoolGraduationYear')->nullable();
        });

        Schema::create('PersonQetaa', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QetaaID');
        });

        Schema::create('PersonSanaMarhala', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('SanaMarhalaID');
        });

        Schema::create('PersonSpiritualFatherInformation', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('SpiritualFatherName')->nullable();
            $table->string('SpiritualFatherChurchName')->nullable();
        });

        Schema::create('PersonSystemPassword', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('Password')->nullable();
        });

        Schema::create('PersonImages', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('PersonSystemImagePath')->nullable();
            $table->string('PersonSystemImageThumbnailPath')->nullable();
            $table->string('ScoutOfficialUniformImagePath')->nullable();
        });

        Schema::create('PersonalPhysicalAddress', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->string('BuildingNumber')->nullable();
            $table->string('FloorNumber')->nullable();
            $table->string('AppartmentNumber')->nullable();
            $table->string('MainStreetName')->nullable();
            $table->string('SubStreetName')->nullable();
            $table->unsignedInteger('ManteqaID')->nullable();
            $table->unsignedInteger('DistrictID')->nullable();
            $table->string('NearestLandmark')->nullable();
        });

        Schema::create('PeopleAllergies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID');
            $table->string('AllergyType')->nullable();
            $table->string('AllergyName')->nullable();
        });

        Schema::create('PeopleMedicalHistory', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('PersonID');
            $table->string('Disease')->nullable();
            $table->string('Medication')->nullable();
            $table->boolean('HasEmergencyCase')->nullable();
            $table->string('EmergencyDetails')->nullable();
        });

        Schema::create('PersonEntryQuestions', function (Blueprint $table) {
            $table->unsignedInteger('PersonID');
            $table->unsignedInteger('QuestionID');
            $table->string('Answer')->nullable();
        });

        Schema::create('Folar', function (Blueprint $table) {
            $table->increments('FolarID');
            $table->string('FolarName');
        });

        Schema::create('PersonFolar', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->unsignedInteger('FolarID');
        });
    }
}
