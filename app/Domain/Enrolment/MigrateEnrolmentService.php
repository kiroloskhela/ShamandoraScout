<?php

namespace App\Domain\Enrolment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class MigrateEnrolmentService
{
    public function migrateApprovedForQetaa(int $qetaaId): void
    {
        $personsBeforeMigration = DB::select("
            SELECT  NewUsersInformation.*,
                    GROUP_CONCAT(CONCAT(NewUsersPersonEntryQuestions.QuestionID, ':', NewUsersPersonEntryQuestions.Answer) SEPARATOR ', ') AS AnsweredQuestions
            FROM    NewUsersInformation
            LEFT JOIN NewUsersPersonEntryQuestions
                   ON NewUsersInformation.PersonID = NewUsersPersonEntryQuestions.PersonID
            WHERE   IsApproved = 1 AND NewUsersInformation.QetaaID = ?
            GROUP BY NewUsersInformation.PersonID
        ", [$qetaaId]);

        foreach ($personsBeforeMigration as $person) {
            try {
                $this->migrateOne($person);
            } catch (Throwable $e) {
                Log::error('New enrolment migration failed', [
                    'new_user_person_id' => $person->PersonID ?? null,
                    'message' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    public function migrateOne(object $person): int
    {
        $questionsAnswersPairs = $person->AnsweredQuestions ? explode(', ', $person->AnsweredQuestions) : [];

        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $pass[] = $alphabet[rand(0, $alphaLength)];
        }
        $passString = implode($pass);

        return (int) DB::transaction(function () use ($person, $questionsAnswersPairs, $passString) {
            $thisPersonID = (int) DB::table('PersonInformation')->insertGetId([
                'ShamandoraCode' => bin2hex(random_bytes(5)), // varchar(10) placeholder until real SH- code is set
                'FirstName' => $person->FirstName,
                'SecondName' => $person->SecondName,
                'ThirdName' => $person->ThirdName,
                'FourthName' => $person->FourthName,
                'Gender' => $person->Gender,
                'DateOfBirth' => $person->DateOfBirth,
                'RaqamQawmy' => $person->RaqamQawmy,
                'ScoutJoiningYear' => $person->ScoutJoiningYear,
                'BloodTypeID' => $person->BloodTypeID,
                'FacebookProfileURL' => $person->FacebookProfileURL,
                'InstagramProfileURL' => $person->InstagramProfileURL,
                'PersonalEmail' => $person->PersonalEmail,
                'RequestPersonID' => 0,
            ], 'PersonID');

            $shamandoraCode = \App\Support\ShamandoraCode::fromPersonId($thisPersonID);
            DB::table('PersonInformation')->where('PersonID', $thisPersonID)->update([
                'ShamandoraCode' => $shamandoraCode,
            ]);

            DB::table('PersonPhoneNumbers')->insert([
                'PersonID' => $thisPersonID,
                'PersonPersonalMobileNumber' => $person->PersonPersonalMobileNumber,
                'FatherMobileNumber' => $person->FatherMobileNumber,
                'MotherMobileNumber' => $person->MotherMobileNumber,
                'HomePhoneNumber' => $person->HomePhoneNumber,
                'IsOPersonalPhoneNumberHavingWhatsapp' => $person->IsOPersonalPhoneNumberHavingWhatsapp,
            ]);

            DB::table('PersonLearningInformation')->insert([
                'PersonID' => $thisPersonID,
                'SchoolName' => $person->SchoolName,
                'SchoolGraduationYear' => $person->SchoolGraduationYear,
            ]);

            DB::table('PersonQetaa')->insert([
                'PersonID' => $thisPersonID,
                'QetaaID' => $person->QetaaID,
            ]);

            DB::table('PersonSanaMarhala')->insert([
                'PersonID' => $thisPersonID,
                'SanaMarhalaID' => $person->SanaMarhalaID,
            ]);

            DB::table('PersonSpiritualFatherInformation')->insert([
                'PersonID' => $thisPersonID,
                'SpiritualFatherName' => $person->SpiritualFatherName,
                'SpiritualFatherChurchName' => $person->SpiritualFatherChurchName,
            ]);

            DB::table('PersonSystemPassword')->insert([
                'PersonID' => $thisPersonID,
                'Password' => Hash::make($passString),
            ]);

            DB::table('PersonImages')->insert([
                'PersonID' => $thisPersonID,
                'PersonSystemImagePath' => $person->PersonalImagePath,
                'ScoutOfficialUniformImagePath' => $person->ScoutImagePath,
            ]);

            DB::table('PersonalPhysicalAddress')->insert([
                'PersonID' => $thisPersonID,
                'BuildingNumber' => $person->BuildingNumber,
                'FloorNumber' => $person->FloorNumber,
                'AppartmentNumber' => $person->AppartmentNumber,
                'MainStreetName' => $person->MainStreetName,
                'SubStreetName' => $person->SubStreetName,
                'ManteqaID' => $person->ManteqaID,
                'DistrictID' => is_null($person->DistrictID) ? 1 : $person->DistrictID,
                'NearestLandmark' => $person->NearestLandmark,
            ]);

            foreach ($this->splitList($person->AllergyFood ?? null) as $a) {
                DB::table('PeopleAllergies')->insert([
                    'PersonID' => $thisPersonID,
                    'AllergyType' => 'Food',
                    'AllergyName' => $a,
                ]);
            }
            foreach ($this->splitList($person->AllergyMedicine ?? null) as $a) {
                DB::table('PeopleAllergies')->insert([
                    'PersonID' => $thisPersonID,
                    'AllergyType' => 'Medicine',
                    'AllergyName' => $a,
                ]);
            }

            $diseases = $this->splitList($person->MedicalDiseases ?? null);
            $medications = $this->splitList($person->MedicalMedications ?? null);
            $max = max(count($diseases), count($medications), 1);
            $hasEmergency = (int) ($person->HasEmergencyCase ?? 0);
            $emergencyDetails = $hasEmergency ? ($person->EmergencyDetails ?? null) : null;

            for ($i = 0; $i < $max; $i++) {
                $d = $diseases[$i] ?? null;
                $m = $medications[$i] ?? null;
                if ($d === null && $m === null) {
                    continue;
                }
                DB::table('PeopleMedicalHistory')->insert([
                    'PersonID' => $thisPersonID,
                    'Disease' => $d ?? 'غير محدد',
                    'Medication' => $m,
                    'HasEmergencyCase' => $hasEmergency,
                    'EmergencyDetails' => $emergencyDetails,
                ]);
            }

            if ($hasEmergency === 1 && count($diseases) === 0 && count($medications) === 0) {
                DB::table('PeopleMedicalHistory')->insert([
                    'PersonID' => $thisPersonID,
                    'Disease' => 'غير محدد',
                    'Medication' => null,
                    'HasEmergencyCase' => 1,
                    'EmergencyDetails' => $emergencyDetails,
                ]);
            }

            foreach ($questionsAnswersPairs as $pair) {
                if (strpos($pair, ':') === false) {
                    continue;
                }
                [$questionID, $answer] = explode(':', $pair, 2);
                DB::table('PersonEntryQuestions')->insert([
                    'PersonID' => $thisPersonID,
                    'QuestionID' => $questionID,
                    'Answer' => $answer,
                ]);
                DB::table('NewUsersPersonEntryQuestions')
                    ->where('PersonID', $person->PersonID)
                    ->where('QuestionID', $questionID)
                    ->delete();
            }

            DB::table('NewUsersInformation')->where('PersonID', $person->PersonID)->delete();

            return $thisPersonID;
        });
    }

    private function splitList($value): array
    {
        if ($value === null) {
            return [];
        }
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }
        $value = str_replace(["\r\n", "\n", "،", ";"], ",", $value);
        $parts = array_filter(array_map('trim', explode(',', $value)), fn ($x) => $x !== '');

        return array_values(array_unique($parts));
    }
}
