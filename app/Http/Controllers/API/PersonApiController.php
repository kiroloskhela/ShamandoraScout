<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PersonApiController extends Controller
{


    /**
 * @OA\Tag(
 *   name="Person",
 *   description="Person endpoints (list persons, profile, calendar)"
 * )
 *
 * @OA\Post(
 *   path="/api/persons",
 *   operationId="showPersons",
 *   tags={"Person"},
 *   summary="List persons accessible by the user",
 *   description="Returns persons in groups/qetaat visible to the provided user id. Adds full_name in response.",
 *   @OA\RequestBody(
 *     required=true,
 *     @OA\JsonContent(
 *       type="object",
 *       required={"id"},
 *       @OA\Property(property="id", type="integer", example=55, description="User/Person ID used to filter accessible persons")
 *     )
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(
 *         property="persons",
 *         type="array",
 *         @OA\Items(
 *           type="object",
 *           @OA\Property(property="PersonID", type="integer", example=101),
 *           @OA\Property(property="ShamandoraCode", type="string", example="SC-001"),
 *           @OA\Property(property="FirstName", type="string", example="John"),
 *           @OA\Property(property="SecondName", type="string", example="A."),
 *           @OA\Property(property="ThirdName", type="string", example="B."),
 *           @OA\Property(property="FourthName", type="string", example="C."),
 *           @OA\Property(property="full_name", type="string", example="John A. B. C."),
 *           @OA\Property(property="QetaaName", type="string", example="Qetaa A"),
 *           @OA\Property(property="ScoutJoiningYear", type="integer", example=2020),
 *           @OA\Property(property="SanaMarhalaName", type="string", example="Level 1"),
 *           @OA\Property(property="RaqamQawmy", type="string", example="12345678901234"),
 *           @OA\Property(property="PersonPersonalMobileNumber", type="string", example="01000000000"),
 *           @OA\Property(property="QetaaID", type="integer", example=1),
 *           @OA\Property(property="GroupPersonID", type="integer", example=101),
 *           @OA\Property(property="HasAnsweredQuestions", type="string", example="نعم"),
 *           @OA\Property(property="SanaMarhalaID", type="integer", example=2)
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Response(response=422, description="Validation error", @OA\JsonContent(type="object"))
 * )
 *
 * @OA\Get(
 *   path="/api/person/{id}",
 *   operationId="showProfile",
 *   tags={"Person"},
 *   summary="Get person profile",
 *   description="Returns person full profile data (joined tables) and entry questions/answers.",
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="PersonID",
 *     @OA\Schema(type="integer", example=101)
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="person", type="object"),
 *       @OA\Property(
 *         property="questions",
 *         type="array",
 *         @OA\Items(
 *           type="object",
 *           @OA\Property(property="QuestionText", type="string", example="Why do you want to join?"),
 *           @OA\Property(property="Answer", type="string", example="To learn and serve.")
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Response(
 *     response=404,
 *     description="Not found",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(property="error", type="string", example="Person not found")
 *     )
 *   )
 * )
 *
 * @OA\Get(
 *   path="/api/person/{id}/calendar",
 *   operationId="showCalendar",
 *   tags={"Person"},
 *   summary="Get person calendar events",
 *   description="Returns events accessible for a person via their group/qetaa membership.",
 *   @OA\Parameter(
 *     name="id",
 *     in="path",
 *     required=true,
 *     description="PersonID",
 *     @OA\Schema(type="integer", example=101)
 *   ),
 *   @OA\Response(
 *     response=200,
 *     description="Success",
 *     @OA\JsonContent(
 *       type="object",
 *       @OA\Property(
 *         property="events",
 *         type="array",
 *         @OA\Items(
 *           type="object",
 *           @OA\Property(property="EventID", type="integer", example=10),
 *           @OA\Property(property="EventName", type="string", example="Weekly Meeting"),
 *           @OA\Property(property="EventStartDate", type="string", format="date-time", example="2025-09-01 18:00:00"),
 *           @OA\Property(property="EventEndDate", type="string", format="date-time", example="2025-09-01 20:00:00"),
 *           @OA\Property(property="EventTypeName", type="string", example="Meeting"),
 *           @OA\Property(property="SeasonName", type="string", example="Season A"),
 *           @OA\Property(property="SeasonYear", type="integer", example=2025)
 *         )
 *       )
 *     )
 *   )
 * )
 */


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


public function ShowCalendar($id)
{
    $events = DB::select("
            SELECT e.EventID, e.EventName, e.EventStartDate,e.EventEndDate , et.EventTypeName , S.SeasonName , S.SeasonYear
            FROM PersonGroup pg
            JOIN GroupQetaa gq ON pg.GroupID = gq.GroupID
            JOIN Qetaa q ON gq.QetaaID = q.QetaaID
            JOIN EventQetaa eq ON q.QetaaID = eq.QetaaID
            JOIN Event e ON eq.EventID = e.EventID
            JOIN EventType et ON e.EventTypeID = et.EventTypeID
            JOIN SeasonEvent se on se.EventID = e.EventID
            JOIN Season S on S.SeasonID = se.SeasonID
            WHERE pg.PersonID = ?
            ORDER BY e.EventStartDate ASC
    ", [$id]);

    return response()->json(['events' => $events]);
}
}