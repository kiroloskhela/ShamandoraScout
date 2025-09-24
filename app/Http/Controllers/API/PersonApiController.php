<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PersonApiController extends Controller
{

    public function ShowPersons(Request $request)
    {
        $userId = $request->input('id');
        $rawPersons = DB::select("\nSELECT  Distinct\n    pi.PersonID,\n    pi.ShamandoraCode,\n    pi.FirstName, \n    pi.SecondName, \n    pi.ThirdName, \n    pi.FourthName, \n    q.QetaaName,\n    pi.ScoutJoiningYear,\n    sm.SanaMarhalaName, \n    pi.RaqamQawmy,\n    ppn.PersonPersonalMobileNumber,\n    q.QetaaID,\n    PG.PersonID AS GroupPersonID,\n    IF(peq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions,\n    psm.SanaMarhalaID\nFROM PersonInformation pi\nLEFT JOIN PersonEntryQuestions peq ON pi.PersonID = peq.PersonID \nLEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID\nLEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID\nLEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID\nLEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID\nLEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID\nLEFT JOIN PersonGroup PG ON PG.PersonID = pi.PersonID\nJOIN GroupQetaa gq ON gq.QetaaID = q.QetaaID\nJOIN PersonGroup pg2 ON pg2.GroupID = gq.GroupID\nWHERE pg2.PersonID = ?\nORDER BY pi.ShamandoraCode ASC;\n    ", [$userId]);

        $persons = collect($rawPersons)->map(function ($person) {
            $person->full_name = trim("{$person->FirstName} {$person->SecondName} {$person->ThirdName} {$person->FourthName}");
            return $person;
        });

        return response()->json(['persons' => $persons]);
    }

    // GET /api/person/{id}
    public function ShowProfile($id)
    {
        $person = DB::table('PersonInformation')
            ->leftJoin('BloodType', 'BloodType.BloodTypeID', '=', 'PersonInformation.BloodTypeID')
            ->leftJoin('PersonEgazetBetakatTaqaddom', 'PersonEgazetBetakatTaqaddom.PersonID' , '=', 'PersonInformation.PersonID')
            ->leftJoin('EgazetBetakatTaqaddom', 'PersonEgazetBetakatTaqaddom.EgazetBetakatTaqaddomID', '=', 'EgazetBetakatTaqaddom.EgazetBetakatTaqaddomID')
            ->leftJoin('PersonJob', 'PersonInformation.PersonID', '=', 'PersonJob.PersonID')
            ->leftJoin('PersonLearningInformation', 'PersonLearningInformation.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('Faculty', 'PersonLearningInformation.FacultyID', '=', 'Faculty.FacultyID')
            ->leftJoin('University', 'PersonLearningInformation.UniversityID', '=', 'University.UniversityID')
            ->leftJoin('PersonPhoneNumbers', 'PersonPhoneNumbers.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('PersonQetaa', 'PersonQetaa.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('Qetaa', 'Qetaa.QetaaID', '=', 'PersonQetaa.QetaaID')
            ->leftJoin('PersonRotbaKashfeyya', 'PersonRotbaKashfeyya.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('RotbaInformation', 'PersonRotbaKashfeyya.RotbaID', '=', 'RotbaInformation.RotbaID')
            ->leftJoin('PersonSanaMarhala', 'PersonSanaMarhala.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('SanaMarhala', 'SanaMarhala.SanaMarhalaID', '=', 'PersonSanaMarhala.SanaMarhalaID')
            ->leftJoin('PersonSpiritualFatherInformation', 'PersonSpiritualFatherInformation.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('PersonSystemPassword', 'PersonInformation.PersonID', '=', 'PersonSystemPassword.PersonID')
            ->leftJoin('PersonalPhysicalAddress', 'PersonalPhysicalAddress.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('Manteqa', 'Manteqa.ManteqaID', '=', 'PersonalPhysicalAddress.ManteqaID')
            ->leftJoin('Districts', 'Districts.DistrictID', '=', 'PersonalPhysicalAddress.DistrictID')
            ->where('PersonInformation.PersonID', $id)
            ->select('PersonInformation.*',
                'BloodType.BloodTypeName',
                'EgazetBetakatTaqaddom.EgazetBetakatTaqaddomName',
                'PersonJob.JobName', 'PersonJob.WorkPlace',
                'PersonLearningInformation.SchoolName', 'PersonLearningInformation.SchoolGraduationYear',
                'Faculty.FacultyName', 'University.UniversityName', 'PersonLearningInformation.ActualFacultyGraduationYear',
                'PersonPhoneNumbers.PersonPersonalMobileNumber', 'PersonPhoneNumbers.FatherMobileNumber', 'PersonPhoneNumbers.MotherMobileNumber', 'PersonPhoneNumbers.HomePhoneNumber', 'PersonPhoneNumbers.IsOPersonalPhoneNumberHavingWhatsapp',
                'Qetaa.QetaaName',
                'RotbaInformation.RotbaName',
                'SanaMarhala.SanaMarhalaName',
                'PersonSpiritualFatherInformation.SpiritualFatherName', 'PersonSpiritualFatherInformation.SpiritualFatherChurchName',
                'PersonSystemPassword.Password',
                'PersonalPhysicalAddress.BuildingNumber', 'PersonalPhysicalAddress.FloorNumber', 'PersonalPhysicalAddress.AppartmentNumber', 'PersonalPhysicalAddress.MainStreetName', 'PersonalPhysicalAddress.SubStreetName', 'PersonalPhysicalAddress.NearestLandmark',
                'Manteqa.ManteqaName', 'Districts.DistrictName'
            )
            ->first();

        if (!$person) {
            return response()->json(['error' => 'Person not found'], 404);
        }

        $questions = DB::table('PersonEntryQuestions')
            ->join('MarhalaEntryQuestions', 'MarhalaEntryQuestions.QuestionID', '=', 'PersonEntryQuestions.QuestionID')
            ->select('MarhalaEntryQuestions.QuestionText','PersonEntryQuestions.Answer')
            ->where('PersonEntryQuestions.PersonID', $id)->get();

        return response()->json([
            'person' => $person,
            'questions' => $questions
        ]);
    }
}