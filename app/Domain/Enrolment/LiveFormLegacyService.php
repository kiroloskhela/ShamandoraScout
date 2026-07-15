<?php

namespace App\Domain\Enrolment;

use Illuminate\Support\Facades\DB;

/**
 * Legacy liveform finalize paths (waiting-list move + answer upsert).
 */
class LiveFormLegacyService
{
    public function __construct(
        private readonly LiveFormSubmitService $submit,
    ) {
    }

    /**
     * Copy NewUsers row + answers into waiting-list tables and delete from main.
     *
     * Remints waiting-list id/PersonID so Package A invariant PersonID === id holds.
     *
     * @param  iterable<object>  $questions  MarhalaEntryQuestions rows
     * @param  array<int|string, mixed>  $answers  questionId => answer
     */
    public function moveToWaitingList(object $person, iterable $questions, array $answers): void
    {
        $oldPersonId = (int) $person->PersonID;

        $row = [
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
            'BuildingNumber' => $person->BuildingNumber,
            'FloorNumber' => $person->FloorNumber,
            'AppartmentNumber' => $person->AppartmentNumber,
            'MainStreetName' => $person->MainStreetName,
            'SubStreetName' => $person->SubStreetName,
            'ManteqaID' => $person->ManteqaID,
            'DistrictID' => $person->DistrictID,
            'NearestLandmark' => $person->NearestLandmark,
            'SanaMarhalaID' => $person->SanaMarhalaID,
            'SpiritualFatherName' => $person->SpiritualFatherName,
            'SpiritualFatherChurchName' => $person->SpiritualFatherChurchName,
            'Password' => $person->Password,
            'PersonPersonalMobileNumber' => $person->PersonPersonalMobileNumber,
            'FatherMobileNumber' => $person->FatherMobileNumber,
            'MotherMobileNumber' => $person->MotherMobileNumber,
            'HomePhoneNumber' => $person->HomePhoneNumber,
            'IsOPersonalPhoneNumberHavingWhatsapp' => $person->IsOPersonalPhoneNumberHavingWhatsapp,
            'SchoolName' => $person->SchoolName,
            'SchoolGraduationYear' => $person->SchoolGraduationYear,
            'QetaaID' => $person->QetaaID,
            'QetaaName' => $person->QetaaName,
            'FacultyID' => $person->FacultyID,
            'UniversityID' => $person->UniversityID,
            'UniversityGraduationYear' => $person->UniversityGraduationYear,
            'PersonalImagePath' => $person->PersonalImagePath,
            'ScoutImagePath' => $person->ScoutImagePath,
            'AllergyFood' => $person->AllergyFood,
            'AllergyMedicine' => $person->AllergyMedicine,
            'MedicalDiseases' => $person->MedicalDiseases,
            'MedicalMedications' => $person->MedicalMedications,
            'HasEmergencyCase' => $person->HasEmergencyCase,
            'EmergencyDetails' => $person->EmergencyDetails,
        ];

        $newId = $this->submit->allocateNewEnrolmentRecord('NewUsersInformationWaitinglist', $row);

        foreach ($questions as $question) {
            DB::table('NewUsersPersonEntryQuestionsWaitinglist')->insert([
                'PersonID' => $newId,
                'QuestionID' => $question->QuestionID,
                'Answer' => $answers[$question->QuestionID] ?? null,
            ]);
        }

        DB::table('NewUsersPersonEntryQuestions')
            ->where('PersonID', $oldPersonId)
            ->delete();

        DB::table('NewUsersInformation')
            ->where('PersonID', $oldPersonId)
            ->delete();
    }

    /**
     * @param  iterable<object>  $questions
     * @param  array<int|string, mixed>  $answers
     */
    public function upsertAnswers(int $personId, iterable $questions, array $answers): void
    {
        foreach ($questions as $question) {
            $answer = $answers[$question->QuestionID] ?? null;

            $exists = DB::table('NewUsersPersonEntryQuestions')
                ->where('PersonID', $personId)
                ->where('QuestionID', $question->QuestionID)
                ->exists();

            if ($exists) {
                DB::table('NewUsersPersonEntryQuestions')
                    ->where('PersonID', $personId)
                    ->where('QuestionID', $question->QuestionID)
                    ->update(['Answer' => $answer]);
            } else {
                DB::table('NewUsersPersonEntryQuestions')->insert([
                    'PersonID' => $personId,
                    'QuestionID' => $question->QuestionID,
                    'Answer' => $answer,
                ]);
            }
        }
    }
}
