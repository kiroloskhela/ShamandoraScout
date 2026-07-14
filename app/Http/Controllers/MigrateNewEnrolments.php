<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Session;
use Throwable;

class MigrateNewEnrolments extends Controller
{
 public function migrate($qetaaID)
{
$personsBeforeMigration = DB::select("
    SELECT  NewUsersInformation.*,
            GROUP_CONCAT(CONCAT(NewUsersPersonEntryQuestions.QuestionID, ':', NewUsersPersonEntryQuestions.Answer) SEPARATOR ', ') AS AnsweredQuestions
    FROM    NewUsersInformation
    LEFT JOIN NewUsersPersonEntryQuestions 
           ON NewUsersInformation.PersonID = NewUsersPersonEntryQuestions.PersonID
    WHERE   IsApproved = 1 AND NewUsersInformation.QetaaID = ?
    GROUP BY NewUsersInformation.PersonID
", [$qetaaID]);

    // helper: split comma / Arabic comma / semicolon / new lines into clean unique array
    $splitList = function ($value) {
        if ($value === null) return [];
        $value = trim((string)$value);
        if ($value === '') return [];

        $value = str_replace(["\r\n", "\n", "،", ";"], ",", $value);
        $parts = array_filter(array_map('trim', explode(',', $value)), fn($x) => $x !== '');
        $parts = array_values(array_unique($parts));
        return $parts;
    };

    foreach ($personsBeforeMigration as $person) {

        try {
            $questionsAnswersPairs = $person->AnsweredQuestions ? explode(', ', $person->AnsweredQuestions) : [];

            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $pass = [];
            $alphaLength = strlen($alphabet) - 1;
            for ($i = 0; $i < 8; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }
            $passString = implode($pass);

            DB::beginTransaction();

            // ================== Person tables ==================
            // Use AUTO_INCREMENT PersonID (do not compute MAX+1 — race-prone).
            $thisPersonID = (int) DB::table('PersonInformation')->insertGetId([
                // Temporary unique placeholder until AUTO_INCREMENT PersonID is known.
                // ShamandoraCode is varchar(10) NOT NULL, so this must fit in 10 chars.
                'ShamandoraCode' => bin2hex(random_bytes(5)),
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
            DB::table('PersonInformation')
                ->where('PersonID', $thisPersonID)
                ->update(['ShamandoraCode' => $shamandoraCode]);

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
                'QetaaID' => $person->QetaaID
            ]);

            DB::table('PersonSanaMarhala')->insert([
                'PersonID' => $thisPersonID,
                'SanaMarhalaID' => $person->SanaMarhalaID
            ]);

            DB::table('PersonSpiritualFatherInformation')->insert([
                'PersonID' => $thisPersonID,
                'SpiritualFatherName' => $person->SpiritualFatherName,
                'SpiritualFatherChurchName' => $person->SpiritualFatherChurchName
            ]);

            DB::table('PersonSystemPassword')->insert([
                'PersonID' => $thisPersonID,
                'Password' => $passString
            ]);

            DB::table('PersonImages')->insert([
                'PersonID' => $thisPersonID,
                'PersonSystemImagePath' => $person->PersonalImagePath,
                'ScoutOfficialUniformImagePath' => $person->ScoutImagePath
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
                'NearestLandmark' => $person->NearestLandmark
            ]);

            // ================== NEW: Allergies ==================
            // expects NewUsersInformation columns: AllergyFood, AllergyMedicine
            $foodAllergies = $splitList($person->AllergyFood ?? null);
            foreach ($foodAllergies as $a) {
                DB::table('PeopleAllergies')->insert([
                    'PersonID' => $thisPersonID,
                    'AllergyType' => 'Food',
                    'AllergyName' => $a,
                ]);
            }

            $medAllergies = $splitList($person->AllergyMedicine ?? null);
            foreach ($medAllergies as $a) {
                DB::table('PeopleAllergies')->insert([
                    'PersonID' => $thisPersonID,
                    'AllergyType' => 'Medicine',
                    'AllergyName' => $a,
                ]);
            }

            // ================== NEW: Medical History ==================
            // expects NewUsersInformation columns: MedicalDiseases, MedicalMedications, HasEmergencyCase, EmergencyDetails
            $diseases = $splitList($person->MedicalDiseases ?? null);
            $medications = $splitList($person->MedicalMedications ?? null);

            // Make pairs if possible; otherwise store what exists.
            $max = max(count($diseases), count($medications), 1);

            $hasEmergency = (int)($person->HasEmergencyCase ?? 0);
            $emergencyDetails = $hasEmergency ? ($person->EmergencyDetails ?? null) : null;

            for ($i = 0; $i < $max; $i++) {
                $d = $diseases[$i] ?? null;
                $m = $medications[$i] ?? null;

                // If both null, skip (unless you want a single "emergency-only" row)
                if ($d === null && $m === null) continue;

                DB::table('PeopleMedicalHistory')->insert([
                    'PersonID' => $thisPersonID,
                    'Disease' => $d ?? 'غير محدد',
                    'Medication' => $m,
                    'HasEmergencyCase' => $hasEmergency,
                    'EmergencyDetails' => $emergencyDetails,
                ]);
            }

            // If user has emergency but no disease/medication, still store 1 row
            if ($hasEmergency === 1 && count($diseases) === 0 && count($medications) === 0) {
                DB::table('PeopleMedicalHistory')->insert([
                    'PersonID' => $thisPersonID,
                    'Disease' => 'غير محدد',
                    'Medication' => null,
                    'HasEmergencyCase' => 1,
                    'EmergencyDetails' => $emergencyDetails,
                ]);
            }

            // ================== Entry Questions migration ==================
            foreach ($questionsAnswersPairs as $pair) {
                if (strpos($pair, ':') === false) continue;

                [$questionID, $answer] = explode(':', $pair, 2);

                DB::table('PersonEntryQuestions')->insert([
                    'PersonID' => $thisPersonID,
                    'QuestionID' => $questionID,
                    'Answer' => $answer
                ]);

                DB::table('NewUsersPersonEntryQuestions')
                    ->where('PersonID', $person->PersonID)
                    ->where('QuestionID', $questionID)
                    ->delete();
            }

            // delete new user row after everything migrated
            DB::table('NewUsersInformation')->where('PersonID', $person->PersonID)->delete();

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
            return view('person.entry-error');
        }
    }

    return view('person.migrate-new-enrolments-status');
}

}