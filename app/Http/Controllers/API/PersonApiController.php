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



public function showPersons(Request $request)
{
    $userId = (int) $request->input('id');

    if ($userId <= 0) {
        return response()->json([
            'message' => 'Invalid id provided.',
            'persons' => [],
        ], 422);
    }

    $persons = DB::table('PersonInformation as pi')
        ->leftJoin('PersonEntryQuestions as peq', 'pi.PersonID', '=', 'peq.PersonID')
        ->leftJoin('PersonSanaMarhala as psm', 'pi.PersonID', '=', 'psm.PersonID')
        ->leftJoin('SanaMarhala as sm', 'sm.SanaMarhalaID', '=', 'psm.SanaMarhalaID')
        ->leftJoin('PersonQetaa as pq', 'pi.PersonID', '=', 'pq.PersonID')
        ->leftJoin('Qetaa as q', 'pq.QetaaID', '=', 'q.QetaaID')
        ->leftJoin('PersonPhoneNumbers as ppn', 'pi.PersonID', '=', 'ppn.PersonID')
        ->leftJoin('PersonGroup as pg_main', 'pg_main.PersonID', '=', 'pi.PersonID')
         ->leftJoin( 'PersonImages as pi_img', 'pi_img.PersonID', '=', 'pi.PersonID')
        ->join('GroupQetaa as gq', 'gq.QetaaID', '=', 'q.QetaaID')
        ->join('PersonGroup as pg2', 'pg2.GroupID', '=', 'gq.GroupID')
        ->where('pg2.PersonID', $userId)
        ->select([
            'pi.PersonID',
            'pi.ShamandoraCode',
            'pi.FirstName',
            'pi.SecondName',
            'pi.ThirdName',
            'pi.FourthName',
            'q.QetaaName',
            'pi.ScoutJoiningYear',
            'sm.SanaMarhalaName',
            'pi.RaqamQawmy',
            'ppn.PersonPersonalMobileNumber',
            'q.QetaaID',
            'pi_img.PersonSystemImagePath',
            DB::raw('pg_main.PersonID AS GroupPersonID'),
            DB::raw("IF(peq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions"),
            'psm.SanaMarhalaID',
        ])
        ->distinct()
        ->orderBy('pi.ShamandoraCode', 'asc')
        ->get()
        ->map(function ($person) {
            $person->full_name = trim(
                "{$person->FirstName} {$person->SecondName} {$person->ThirdName} {$person->FourthName}"
            );
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
            ->leftJoin('PersonalPhysicalAddress', 'PersonalPhysicalAddress.PersonID', '=', 'PersonInformation.PersonID')
            ->leftJoin('Manteqa', 'Manteqa.ManteqaID', '=', 'PersonalPhysicalAddress.ManteqaID')
            ->leftJoin('Districts', 'Districts.DistrictID', '=', 'PersonalPhysicalAddress.DistrictID')
            ->leftJoin( 'PersonImages', 'PersonImages.PersonID', '=', 'PersonInformation.PersonID')
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
                'PersonalPhysicalAddress.BuildingNumber', 'PersonalPhysicalAddress.FloorNumber', 'PersonalPhysicalAddress.AppartmentNumber', 'PersonalPhysicalAddress.MainStreetName', 'PersonalPhysicalAddress.SubStreetName', 'PersonalPhysicalAddress.NearestLandmark',
                'Manteqa.ManteqaName', 'Districts.DistrictName' , 'PersonImages.PersonSystemImagePath'
            )
            ->first();

        if (!$person) {
            return response()->json(['error' => 'Person not found'], 404);
        }

        $questions = DB::table('PersonEntryQuestions')
        ->join('MarhalaEntryQuestions', 'MarhalaEntryQuestions.QuestionID', '=', 'PersonEntryQuestions.QuestionID')
        ->select('MarhalaEntryQuestions.QuestionText', 'PersonEntryQuestions.Answer')
        ->where('PersonEntryQuestions.PersonID', $id)
        ->get();


        

        $attendance = DB::table('PersonQetaa as pq')
        ->join('EventQetaa as eq',     'eq.QetaaID',      '=', 'pq.QetaaID')
        ->join('SeasonEvent as se',    'se.EventID',      '=', 'eq.EventID')
        ->join('Event as e',           'e.EventID',       '=', 'se.EventID')
        ->join('Season as s',          's.SeasonID',      '=', 'se.SeasonID')
        ->leftJoin('Attendance as a',  function ($join) {
            $join->on('a.SeasonEventID', '=', 'se.SeasonEventID')
                 ->on('a.ServedID',      '=', 'pq.PersonID');
        })
        ->where('pq.PersonID', $id)
                ->select(
                    'se.SeasonEventID',
                    'e.EventID',
                    'e.EventName',
                    'e.EventStartDate',
                    'e.EventEndDate',
                    's.SeasonName',
                    's.SeasonYear',
                    DB::raw("COALESCE(a.AttendanceStatus, 'absent') AS Status"),
                    'a.Excuse'
                )
                ->orderBy('e.EventStartDate', 'asc')
                ->get();


        $summary = [
            'total'   => $attendance->count(),
            'present' => $attendance->where('Status', 'present')->count(),
            'absent'  => $attendance->where('Status', 'absent')->count(),
            'excused' => $attendance->where('Status', 'excused')->count(),
            'rate'    => $attendance->count()
                ? round($attendance->where('Status', 'present')->count() / $attendance->count() * 100, 1)
                : 0,
        ];
    return response()->json([
        'person'     => $person,
        'questions'  => $questions,
        'attendance' => [
            'summary' => $summary,
            'events'  => $attendance,
        ],
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