<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlaceholderMedicalAnswerCleanerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'sqlite') {
            $this->markTestSkipped('PlaceholderMedicalAnswerCleanerTest requires sqlite.');
        }

        $this->dropTables();
        $this->createTables();
    }

    protected function tearDown(): void
    {
        $this->dropTables();
        parent::tearDown();
    }

    public function test_command_deletes_placeholder_answers_and_keeps_real_medical_content(): void
    {
        DB::table('NewUsersInformation')->insert([
            'PersonID' => 1,
            'AllergyFood' => 'لا',
            'AllergyMedicine' => 'لبن, لا, لا يوجد',
            'MedicalDiseases' => 'لا يوجد',
            'MedicalMedications' => 'none',
            'EmergencyDetails' => 'No',
        ]);
        DB::table('NewUsersInformation')->insert([
            'PersonID' => 2,
            'AllergyFood' => 'سمك',
            'AllergyMedicine' => null,
            'MedicalDiseases' => 'ربو',
            'MedicalMedications' => 'ventolin',
            'EmergencyDetails' => 'حساسية شديدة',
        ]);
        DB::table('NewUsersInformationWaitinglist')->insert([
            'PersonID' => 3,
            'AllergyFood' => 'n/a',
            'AllergyMedicine' => 'مفيش',
            'MedicalDiseases' => 'بدون',
            'MedicalMedications' => 'لا شئ',
            'EmergencyDetails' => 'nothing',
        ]);

        DB::table('PeopleAllergies')->insert([
            ['PersonID' => 10, 'AllergyType' => 'Food', 'AllergyName' => 'لا'],
            ['PersonID' => 10, 'AllergyType' => 'Food', 'AllergyName' => 'لبن, لا'],
            ['PersonID' => 11, 'AllergyType' => 'Medicine', 'AllergyName' => 'Penicillin'],
        ]);

        DB::table('PeopleMedicalHistory')->insert([
            [
                'PersonID' => 10,
                'Disease' => 'لا',
                'Medication' => null,
                'HasEmergencyCase' => 0,
                'EmergencyDetails' => null,
            ],
            [
                'PersonID' => 11,
                'Disease' => 'لا',
                'Medication' => 'insulin',
                'HasEmergencyCase' => 0,
                'EmergencyDetails' => 'لا يوجد',
            ],
            [
                'PersonID' => 12,
                'Disease' => 'ربو',
                'Medication' => 'لا',
                'HasEmergencyCase' => 1,
                'EmergencyDetails' => 'لا',
            ],
        ]);

        $this->artisan('medical:purge-placeholder-answers')->assertSuccessful();

        $enrolment = DB::table('NewUsersInformation')->where('PersonID', 1)->first();
        $this->assertNull($enrolment->AllergyFood);
        $this->assertSame('لبن', $enrolment->AllergyMedicine);
        $this->assertNull($enrolment->MedicalDiseases);
        $this->assertNull($enrolment->MedicalMedications);
        $this->assertNull($enrolment->EmergencyDetails);

        $kept = DB::table('NewUsersInformation')->where('PersonID', 2)->first();
        $this->assertSame('سمك', $kept->AllergyFood);
        $this->assertSame('ربو', $kept->MedicalDiseases);
        $this->assertSame('ventolin', $kept->MedicalMedications);
        $this->assertSame('حساسية شديدة', $kept->EmergencyDetails);

        $waiting = DB::table('NewUsersInformationWaitinglist')->where('PersonID', 3)->first();
        $this->assertNull($waiting->AllergyFood);
        $this->assertNull($waiting->AllergyMedicine);
        $this->assertNull($waiting->MedicalDiseases);
        $this->assertNull($waiting->MedicalMedications);
        $this->assertNull($waiting->EmergencyDetails);

        $allergies = DB::table('PeopleAllergies')->orderBy('AllergyID')->get();
        $this->assertCount(2, $allergies);
        $this->assertSame('لبن', $allergies[0]->AllergyName);
        $this->assertSame('Penicillin', $allergies[1]->AllergyName);

        $this->assertFalse(
            DB::table('PeopleMedicalHistory')->where('PersonID', 10)->exists()
        );

        $insulin = DB::table('PeopleMedicalHistory')->where('PersonID', 11)->first();
        $this->assertNotNull($insulin);
        $this->assertTrue($insulin->Disease === null || $insulin->Disease === '');
        $this->assertSame('insulin', $insulin->Medication);
        $this->assertNull($insulin->EmergencyDetails);

        $asthma = DB::table('PeopleMedicalHistory')->where('PersonID', 12)->first();
        $this->assertNotNull($asthma);
        $this->assertSame('ربو', $asthma->Disease);
        $this->assertNull($asthma->Medication);
        $this->assertSame(1, (int) $asthma->HasEmergencyCase);
        $this->assertNull($asthma->EmergencyDetails);
    }

    private function createTables(): void
    {
        Schema::create('NewUsersInformation', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->text('AllergyFood')->nullable();
            $table->text('AllergyMedicine')->nullable();
            $table->text('MedicalDiseases')->nullable();
            $table->text('MedicalMedications')->nullable();
            $table->string('EmergencyDetails')->nullable();
        });
        Schema::create('NewUsersInformationWaitinglist', function (Blueprint $table) {
            $table->unsignedInteger('PersonID')->primary();
            $table->text('AllergyFood')->nullable();
            $table->text('AllergyMedicine')->nullable();
            $table->text('MedicalDiseases')->nullable();
            $table->text('MedicalMedications')->nullable();
            $table->string('EmergencyDetails')->nullable();
        });
        Schema::create('PeopleAllergies', function (Blueprint $table) {
            $table->increments('AllergyID');
            $table->unsignedInteger('PersonID');
            $table->string('AllergyType');
            $table->string('AllergyName');
        });
        Schema::create('PeopleMedicalHistory', function (Blueprint $table) {
            $table->increments('MedicalHistoryID');
            $table->unsignedInteger('PersonID');
            $table->string('Disease');
            $table->string('Medication')->nullable();
            $table->unsignedTinyInteger('HasEmergencyCase')->default(0);
            $table->string('EmergencyDetails')->nullable();
        });
    }

    private function dropTables(): void
    {
        Schema::dropIfExists('PeopleMedicalHistory');
        Schema::dropIfExists('PeopleAllergies');
        Schema::dropIfExists('NewUsersInformationWaitinglist');
        Schema::dropIfExists('NewUsersInformation');
    }
}
