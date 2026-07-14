<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PersonDirectoryController extends Controller
{

       public function index(Request $request)
            {
                // ✅ Get the user ID from the request
                $userId = $request->query('id') ?? Auth::id();
                Log::info("Request: " . $request);
                Log::info("Fetching persons for user ID: " . $userId);


                // ✅ Run the raw SQL with group filtering
                $rawPersons = DB::select("


    SELECT DISTINCT
    pi.PersonID,
    pi.ShamandoraCode,
    pi.FirstName, 
    pi.SecondName, 
    pi.ThirdName, 
    pi.FourthName, 
    q.QetaaName,
    pi.ScoutJoiningYear,
    sm.SanaMarhalaName, 
    pi.RaqamQawmy,
    ppn.PersonPersonalMobileNumber,
    q.QetaaID,
    PG.PersonID AS GroupPersonID,
    IF(peq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions,
    psm.SanaMarhalaID
FROM PersonInformation pi
LEFT JOIN PersonEntryQuestions peq ON pi.PersonID = peq.PersonID 
LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
LEFT JOIN PersonGroup PG ON PG.PersonID = pi.PersonID
JOIN GroupQetaa gq ON gq.QetaaID = q.QetaaID
JOIN PersonGroup pg2 ON pg2.GroupID = gq.GroupID
WHERE q.QetaaID IN (
    SELECT gq2.QetaaID
    FROM GroupQetaa gq2
    WHERE gq2.GroupID IN (
        SELECT pg3.GroupID
        FROM PersonGroup pg3
        WHERE pg3.PersonID = ?
    )
)
ORDER BY pi.ShamandoraCode ASC;

               ", [$userId]);

                // ✅ Convert to collection and add full_name field
                $persons = collect($rawPersons)->map(function ($person) {
                    $person->full_name = trim("{$person->FirstName} {$person->SecondName} {$person->ThirdName} {$person->FourthName}");
                    return $person;
                });

                // ✅ Return the view with filtered persons
                return view("person.person-index", ['persons' => $persons]);
                        }


public function ShowPersons(Request $request)
{


    $rawPersons = DB::select("
        SELECT DISTINCT
            pi.PersonID,
            pi.ShamandoraCode,
            pi.FirstName, 
            pi.SecondName, 
            pi.ThirdName, 
            pi.FourthName, 
            q.QetaaName,
            pi.ScoutJoiningYear,
            sm.SanaMarhalaName, 
            pi.RaqamQawmy,
            ppn.PersonPersonalMobileNumber,
            q.QetaaID,
            PG.PersonID AS GroupPersonID,
            IF(peq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions,
            psm.SanaMarhalaID
        FROM PersonInformation pi
        LEFT JOIN PersonEntryQuestions peq ON pi.PersonID = peq.PersonID 
        LEFT JOIN PersonSanaMarhala psm ON pi.PersonID = psm.PersonID
        LEFT JOIN SanaMarhala sm ON sm.SanaMarhalaID = psm.SanaMarhalaID
        LEFT JOIN PersonQetaa pq ON pi.PersonID = pq.PersonID
        LEFT JOIN Qetaa q ON pq.QetaaID = q.QetaaID
        LEFT JOIN PersonPhoneNumbers ppn ON pi.PersonID = ppn.PersonID
        LEFT JOIN PersonGroup PG ON PG.PersonID = pi.PersonID
 
        ORDER BY pi.ShamandoraCode ASC
    ");

    $persons = collect($rawPersons)->map(function ($person) {
 
        $person->full_name = trim("{$person->FirstName} {$person->SecondName} {$person->ThirdName} {$person->FourthName}");
        return $person;
    });


    return view("person.person-showAllPersons", ['persons' => $persons]);
}

public function show($id)
{
    $person = DB::table('PersonInformation')
        ->leftJoin('PersonImages', 'PersonImages.PersonID', '=', 'PersonInformation.PersonID')
        ->leftJoin('BloodType', 'BloodType.BloodTypeID', '=', 'PersonInformation.BloodTypeID')
        ->leftJoin('PersonEgazetBetakatTaqaddom', 'PersonEgazetBetakatTaqaddom.PersonID', '=', 'PersonInformation.PersonID')
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
        ->where('PersonInformation.PersonID', $id)
        ->select(
            'PersonInformation.*',
            'PersonImages.PersonSystemImagePath as PersonalImagePath',
            'PersonImages.ScoutOfficialUniformImagePath as ScoutImagePath',
            'BloodType.BloodTypeName',
            'EgazetBetakatTaqaddom.EgazetBetakatTaqaddomName',
            'PersonJob.JobName',
            'PersonJob.WorkPlace',
            'PersonLearningInformation.SchoolName',
            'PersonLearningInformation.SchoolGraduationYear',
            'PersonLearningInformation.ActualFacultyGraduationYear',
            'Faculty.FacultyName',
            'University.UniversityName',
            'PersonPhoneNumbers.PersonPersonalMobileNumber',
            'PersonPhoneNumbers.FatherMobileNumber',
            'PersonPhoneNumbers.MotherMobileNumber',
            'PersonPhoneNumbers.HomePhoneNumber',
            'PersonPhoneNumbers.IsOPersonalPhoneNumberHavingWhatsapp',
            'Qetaa.QetaaName',
            'RotbaInformation.RotbaName',
            'SanaMarhala.SanaMarhalaName',
            'PersonSpiritualFatherInformation.SpiritualFatherName',
            'PersonSpiritualFatherInformation.SpiritualFatherChurchName',
            'PersonalPhysicalAddress.BuildingNumber',
            'PersonalPhysicalAddress.FloorNumber',
            'PersonalPhysicalAddress.AppartmentNumber',
            'PersonalPhysicalAddress.MainStreetName',
            'PersonalPhysicalAddress.SubStreetName',
            'PersonalPhysicalAddress.NearestLandmark',
            'Manteqa.ManteqaName',
            'Districts.DistrictName'
        )
        ->first();

    $questions = DB::table('PersonEntryQuestions')
        ->join('MarhalaEntryQuestions', 'MarhalaEntryQuestions.QuestionID', '=', 'PersonEntryQuestions.QuestionID')
        ->select(
            'PersonEntryQuestions.QuestionID',
            'MarhalaEntryQuestions.QuestionText',
            'PersonEntryQuestions.Answer'
        )
        ->where('PersonEntryQuestions.PersonID', $id)
        ->get();

    return view('person.person-show', [
        'person' => $person,
        'questions' => $questions,
    ]);
}
    
        /**
            * Show the form for editing the specified resource.
            *
            * @param  int  $id
            * @return Response
            */


public function edit($id)
{
    $marahel = DB::table('Marhala')->get();
    $rotab = DB::table('RotbaInformation')->get();
    $seneen_marahel = DB::table('SanaMarhala')->get();
    $questionTypes = DB::table('QuestionsTypes')->get();
    $blood = DB::table('BloodType')->get();
    $betakat = DB::table('EgazetBetakatTaqaddom')->get();
    $manateq = DB::table('Manteqa')->get();
    $districts = DB::table('Districts')->get();
    $qetaat = DB::table('Qetaa')->get();
    $faculties = DB::table('Faculty')->get();
    $universities = DB::table('University')->get();

    $person = DB::table('PersonInformation')
        ->leftJoin('BloodType', 'BloodType.BloodTypeID', '=', 'PersonInformation.BloodTypeID')
        ->leftJoin('PersonEgazetBetakatTaqaddom', 'PersonEgazetBetakatTaqaddom.PersonID', '=', 'PersonInformation.PersonID')
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
        ->leftJoin('PersonImages', 'PersonImages.PersonID', '=', 'PersonInformation.PersonID')
        ->where('PersonInformation.PersonID', $id)
        ->select(
            'PersonInformation.*',
            'BloodType.BloodTypeName',
            'EgazetBetakatTaqaddom.EgazetBetakatTaqaddomID',
            'EgazetBetakatTaqaddom.EgazetBetakatTaqaddomName',
            'PersonJob.JobName',
            'PersonJob.WorkPlace',
            'PersonLearningInformation.SchoolName',
            'PersonLearningInformation.SchoolGraduationYear',
            'PersonLearningInformation.FacultyID',
            'PersonLearningInformation.UniversityID',
            'PersonLearningInformation.ActualFacultyGraduationYear',
            'Faculty.FacultyName',
            'University.UniversityName',
            'PersonPhoneNumbers.PersonPersonalMobileNumber',
            'PersonPhoneNumbers.FatherMobileNumber',
            'PersonPhoneNumbers.MotherMobileNumber',
            'PersonPhoneNumbers.HomePhoneNumber',
            'PersonPhoneNumbers.IsOPersonalPhoneNumberHavingWhatsapp',
            'Qetaa.QetaaID',
            'Qetaa.QetaaName',
            'RotbaInformation.RotbaID',
            'RotbaInformation.RotbaName',
            'SanaMarhala.SanaMarhalaID',
            'SanaMarhala.SanaMarhalaName',
            'PersonSpiritualFatherInformation.SpiritualFatherName',
            'PersonSpiritualFatherInformation.SpiritualFatherChurchName',
            'PersonalPhysicalAddress.BuildingNumber',
            'PersonalPhysicalAddress.FloorNumber',
            'PersonalPhysicalAddress.AppartmentNumber',
            'PersonalPhysicalAddress.MainStreetName',
            'PersonalPhysicalAddress.SubStreetName',
            'PersonalPhysicalAddress.NearestLandmark',
            'PersonalPhysicalAddress.ManteqaID',
            'PersonalPhysicalAddress.DistrictID',
            'Manteqa.ManteqaName',
            'Districts.DistrictName',
            'PersonImages.PersonSystemImagePath as PersonalImagePath',
            'PersonImages.ScoutOfficialUniformImagePath as ScoutImagePath',
        )
        ->first();

    if (!$person) {
        return view('person.entry-error');
    }

$questions = collect();

if (!empty($person->QetaaID)) {
    $answers = DB::table('PersonEntryQuestions')
        ->where('PersonID', $id)
        ->pluck('Answer', 'QuestionID');

    $questions = DB::table('MarhalaEntryQuestions')
        ->select(
            'QuestionID',
            'QetaaID',
            'QuestionText',
            'RequiredAnswerType',
            'MCAnswer',
            'NotToBeShown',
            'IsRequired'
        )
        ->where('QetaaID', $person->QetaaID)
        ->where('NotToBeShown', 0)
        ->orderBy('QuestionID', 'asc')
        ->get()
        ->map(function ($question) use ($answers) {
            $question->Answer = $answers[$question->QuestionID] ?? null;
            return $question;
        });
}

    return view('person.person-edit', [
        'marahel' => $marahel,
        'rotab' => $rotab,
        'seneen_marahel' => $seneen_marahel,
        'questionTypes' => $questionTypes,
        'blood' => $blood,
        'betakat' => $betakat,
        'manateq' => $manateq,
        'districts' => $districts,
        'qetaat' => $qetaat,
        'faculties' => $faculties,
        'universities' => $universities,
        'person' => $person,
        'questions' => $questions,
    ]);
}

public function updates(Request $request, $id)
{
    $personInfo = DB::table('PersonInformation')->where('PersonID', $id)->first();

    if (!$personInfo) {
        return view('person.entry-error');
    }

    if ($request->filled('input_raqam_qawmy')) {
        $raqamQawmyObject = DB::selectOne(
            'SELECT COUNT(*) AS counts FROM PersonInformation WHERE RaqamQawmy = ? AND PersonID != ?',
            [$request->input_raqam_qawmy, $id]
        );

        if (($raqamQawmyObject->counts ?? 0) > 0) {
            return view('person.person-already-exists');
        }
    }

    $validator = Validator::make($request->all(), [
        'first_name' => 'nullable|string|max:255',
        'second_name' => 'nullable|string|max:255',
        'third_name' => 'nullable|string|max:255',
        'fourth_name' => 'nullable|string|max:255',
        'gender' => 'nullable|in:Male,Female',
        'birthdate_input' => 'nullable|date',
        'joining_year_input' => 'nullable|integer',
        'input_raqam_qawmy' => 'nullable|digits:14',
        'blood_type_input' => 'nullable|integer',
        'email_input' => 'nullable|email|max:255',
        'inputFacebookLink' => 'nullable|max:1000',
        'inputInstagramLink' => 'nullable|max:1000',

        'personal_phone_number' => 'nullable|digits_between:11,11',
        'father_phone_number' => 'nullable|digits_between:11,11',
        'mother_phone_number' => 'nullable|digits_between:11,11',
        'home_phone_number' => 'nullable|string|max:50',
        'has_whatsapp' => 'nullable|in:0,1',

        'building_number' => 'nullable|string|max:255',
        'floor_number' => 'nullable|string|max:255',
        'appartment_number' => 'nullable|string|max:255',
        'main_street_name' => 'nullable|string|max:255',
        'sub_street_name' => 'nullable|string|max:255',
        'nearest_landmark' => 'nullable|string|max:1000',
        'manteqa_id' => 'nullable|integer',
        'district_id' => 'nullable|integer',

        'sana_marhala_id' => 'nullable|integer',
        'person_job' => 'nullable|string|max:255',
        'person_job_place' => 'nullable|string|max:255',
        'school_name' => 'nullable|string|max:255',
        'school_grad_year' => 'nullable|string|max:50',
        'person_faculty' => 'nullable|integer',
        'person_university' => 'nullable|integer',
        'university_grad_year' => 'nullable|string|max:50',
        'spiritual_father' => 'nullable|string|max:255',
        'spiritual_father_church' => 'nullable|string|max:255',

        'rotba_kashfeyya_id' => 'nullable|integer',
        'betaka_id' => 'nullable|integer',

        'personal_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
        'scout_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6144',

        'questions' => 'nullable|array',
        'questions.*' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    try {
        DB::beginTransaction();

        $oldImages = DB::table('PersonImages')->where('PersonID', $id)->first();

        $personSystemImagePath = $oldImages->PersonSystemImagePath ?? null;
        $scoutOfficialUniformImagePath = $oldImages->ScoutOfficialUniformImagePath ?? null;

        if ($request->hasFile('personal_image')) {
            if ($personSystemImagePath && Storage::disk('public')->exists($personSystemImagePath)) {
                Storage::disk('public')->delete($personSystemImagePath);
            }
            $personSystemImagePath = $request->file('personal_image')->store('persons/personal', 'public');
        }

        if ($request->hasFile('scout_image')) {
            if ($scoutOfficialUniformImagePath && Storage::disk('public')->exists($scoutOfficialUniformImagePath)) {
                Storage::disk('public')->delete($scoutOfficialUniformImagePath);
            }
            $scoutOfficialUniformImagePath = $request->file('scout_image')->store('persons/scout', 'public');
        }

  $affected = DB::table('PersonInformation')
    ->where('PersonID', $id)
    ->update([
        'FirstName' => $request->input('first_name'),
        'SecondName' => $request->input('second_name'),
        'ThirdName' => $request->input('third_name'),
        'FourthName' => $request->input('fourth_name'),
        'Gender' => $request->input('gender'),
        'DateOfBirth' => $request->input('birthdate_input'),
        'RaqamQawmy' => $request->input('input_raqam_qawmy'),
        'ScoutJoiningYear' => $request->input('joining_year_input'),
        'BloodTypeID' => $request->input('blood_type_input'),
        'FacebookProfileURL' => $request->input('inputFacebookLink'),
        'InstagramProfileURL' => $request->input('inputInstagramLink'),
        'PersonalEmail' => $request->input('email_input'),
        'RequestPersonID' => $request->input('RequestPersonID'),
    ]);

$after = DB::table('PersonInformation')
    ->where('PersonID', $id)
    ->first();



        $phoneData = array_filter([
            'PersonPersonalMobileNumber' => $request->input('personal_phone_number'),
            'FatherMobileNumber' => $request->input('father_phone_number'),
            'MotherMobileNumber' => $request->input('mother_phone_number'),
            'HomePhoneNumber' => $request->input('home_phone_number'),
            'IsOPersonalPhoneNumberHavingWhatsapp' => $request->input('has_whatsapp'),
        ], fn($value) => $value !== null && $value !== '');

        if (!empty($phoneData)) {
            DB::table('PersonPhoneNumbers')->updateOrInsert(
                ['PersonID' => $id],
                $phoneData
            );
        }

        $jobData = array_filter([
            'JobName' => $request->input('person_job'),
            'WorkPlace' => $request->input('person_job_place'),
        ], fn($value) => $value !== null && $value !== '');

        if (!empty($jobData)) {
            DB::table('PersonJob')->updateOrInsert(
                ['PersonID' => $id],
                $jobData
            );
        }

        $learningData = array_filter([
            'SchoolName' => $request->input('school_name'),
            'SchoolGraduationYear' => $request->input('school_grad_year'),
            'FacultyID' => $request->input('person_faculty'),
            'UniversityID' => $request->input('person_university'),
            'ActualFacultyGraduationYear' => $request->input('university_grad_year'),
        ], fn($value) => $value !== null && $value !== '');

        if (!empty($learningData)) {
            DB::table('PersonLearningInformation')->updateOrInsert(
                ['PersonID' => $id],
                $learningData
            );
        }

        if ($request->filled('rotba_kashfeyya_id')) {
            DB::table('PersonRotbaKashfeyya')->updateOrInsert(
                ['PersonID' => $id],
                ['RotbaID' => $request->input('rotba_kashfeyya_id')]
            );
        } else {
            DB::table('PersonRotbaKashfeyya')->where('PersonID', $id)->delete();
        }

        if ($request->filled('betaka_id')) {
            DB::table('PersonEgazetBetakatTaqaddom')->updateOrInsert(
                ['PersonID' => $id],
                ['EgazetBetakatTaqaddomID' => $request->input('betaka_id')]
            );
        } else {
            DB::table('PersonEgazetBetakatTaqaddom')->where('PersonID', $id)->delete();
        }

        if ($request->filled('sana_marhala_id')) {
            DB::table('PersonSanaMarhala')->updateOrInsert(
                ['PersonID' => $id],
                ['SanaMarhalaID' => $request->input('sana_marhala_id')]
            );
        } else {
            DB::table('PersonSanaMarhala')->where('PersonID', $id)->delete();
        }

        $spiritualData = array_filter([
            'SpiritualFatherName' => $request->input('spiritual_father'),
            'SpiritualFatherChurchName' => $request->input('spiritual_father_church'),
        ], fn($value) => $value !== null && $value !== '');

        if (!empty($spiritualData)) {
            DB::table('PersonSpiritualFatherInformation')->updateOrInsert(
                ['PersonID' => $id],
                $spiritualData
            );
        }

        $addressData = array_filter([
            'BuildingNumber' => $request->input('building_number'),
            'FloorNumber' => $request->input('floor_number'),
            'AppartmentNumber' => $request->input('appartment_number'),
            'MainStreetName' => $request->input('main_street_name'),
            'SubStreetName' => $request->input('sub_street_name'),
            'NearestLandmark' => $request->input('nearest_landmark'),
            'ManteqaID' => $request->input('manteqa_id'),
            'DistrictID' => $request->input('district_id'),
        ], fn($value) => $value !== null && $value !== '');

        if (!empty($addressData)) {
            DB::table('PersonalPhysicalAddress')->updateOrInsert(
                ['PersonID' => $id],
                $addressData
            );
        }

        if ($request->hasFile('personal_image') || $request->hasFile('scout_image')) {
            DB::table('PersonImages')->updateOrInsert(
                ['PersonID' => $id],
                [
                    'PersonSystemImagePath' => $personSystemImagePath,
                    'ScoutOfficialUniformImagePath' => $scoutOfficialUniformImagePath,
                ]
            );
        }
// Questions
$questions_debug = [];

if ($request->exists('questions')) {
    foreach (($request->questions ?? []) as $questionId => $answer) {
        if ($answer === null || trim($answer) === '') {
            DB::table('PersonEntryQuestions')
                ->where('PersonID', $id)
                ->where('QuestionID', $questionId)
                ->delete();

            $questions_debug[] = [
                'question_id' => $questionId,
                'action' => 'deleted',
                'answer' => $answer,
            ];

            continue;
        }

        DB::table('PersonEntryQuestions')->updateOrInsert(
            [
                'PersonID' => $id,
                'QuestionID' => $questionId,
            ],
            [
                'Answer' => $answer,
            ]
        );

        $saved = DB::table('PersonEntryQuestions')
            ->where('PersonID', $id)
            ->where('QuestionID', $questionId)
            ->get();

        $questions_debug[] = [
            'question_id' => $questionId,
            'action' => 'saved',
            'answer_sent' => $answer,
            'db_rows' => $saved,
        ];
    }
}



        DB::commit();
return redirect()->route('person.edit', $id)->with('status', 'تم تعديل البيانات بنجاح');

        //return redirect()->route('person.index')->with('status', 'تم تعديل البيانات بنجاح');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Person update failed', [
            'person_id' => $id,
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
        ]);
        return redirect()->back()->with('error', 'حدث خطأ أثناء تعديل البيانات')->withInput();
    }
}
    
        public function deletes($id)
        {
            $person = DB::table('PersonInformation')->where('PersonID','=',$id)->select('PersonInformation.PersonID', 'PersonInformation.ShamandoraCode')->first();

            return view("person.person-delete", array('person' => $person));
        }

        public function destroy($id)
        {
            DB::beginTransaction();

            DB::table('PersonEgazetBetakatTaqaddom')->where('PersonID',$id)->delete();
            DB::table('PersonJob')->where('PersonID',$id)->delete();
            DB::table('PersonLearningInformation')->where('PersonID',$id)->delete();
            DB::table('PersonPhoneNumbers')->where('PersonID',$id)->delete();
            DB::table('PersonQetaa')->where('PersonID',$id)->delete();
            DB::table('PersonRotbaKashfeyya')->where('PersonID',$id)->delete();
            DB::table('PersonalPhysicalAddress')->where('PersonID',$id)->delete();
            DB::table('PersonSystemPassword')->where('PersonID',$id)->delete();
            DB::table('PersonSanaMarhala')->where('PersonID',$id)->delete();
            DB::table('PersonSpiritualFatherInformation')->where('PersonID',$id)->delete();
            DB::table('PersonInformation')->where('PersonID',$id)->delete();
            DB::table('PersonEntryQuestions')->where('PersonID', $id)->delete();

            DB::commit();

            return redirect()->route('person.index');
        }

public function showChangeQetaa()
{
    $qetaaList = DB::table('Qetaa')
        ->select('QetaaID', 'QetaaName')
        ->orderBy('QetaaName')
        ->get();
 
    return view('person.ChangeQetaa', compact('qetaaList'));
}
 
// ── 2. AJAX search endpoint (returns JSON) ────────────────────────────────────









public function searchPerson(Request $request)
{
    $q = trim($request->input('q', ''));



    $words = array_filter(explode(' ', $q));

    $results = DB::table('PersonInformation as pi')
        ->leftJoin('PersonQetaa as pq', 'pi.PersonID', '=', 'pq.PersonID')
        ->leftJoin('Qetaa as qt',       'pq.QetaaID',  '=', 'qt.QetaaID')
        ->select(
            'pi.PersonID',
            'pi.ShamandoraCode',
            'pi.FirstName',
            'pi.SecondName',
            'pi.ThirdName',
            'pi.FourthName',
            'pi.RaqamQawmy',
            'pq.QetaaID',
            'qt.QetaaName'
        )
        // ── Strategy 1: name word matching ──
        ->where(function ($query) use ($words) {
            foreach ($words as $word) {
                $query->where(function ($q2) use ($word) {
                    $q2->where('pi.FirstName',   'like', "%{$word}%")
                       ->orWhere('pi.SecondName', 'like', "%{$word}%")
                       ->orWhere('pi.ThirdName',  'like', "%{$word}%")
                       ->orWhere('pi.FourthName', 'like', "%{$word}%");
                });
            }
        })
        // ── Strategy 2: ID field matching (top-level OR, not nested) ──
        ->orWhere('pi.RaqamQawmy',    'like', "%{$q}%")
        ->orWhere('pi.ShamandoraCode','like', "%{$q}%")
        ->orWhere('pi.PersonID',      'like', "%{$q}%")
        ->groupBy(
            'pi.PersonID',
            'pi.ShamandoraCode',
            'pi.FirstName',
            'pi.SecondName',
            'pi.ThirdName',
            'pi.FourthName',
            'pi.RaqamQawmy',
            'pq.QetaaID',
            'qt.QetaaName'
        )
        ->orderByRaw("
            CASE
                WHEN pi.FirstName   LIKE ? THEN 1
                WHEN pi.SecondName  LIKE ? THEN 2
                WHEN pi.ThirdName   LIKE ? THEN 3
                WHEN pi.RaqamQawmy  LIKE ? THEN 4
                ELSE 5
            END
        ", ["{$q}%", "{$q}%", "{$q}%", "{$q}%"])
        ->limit(15)
        ->get()
        ->map(function ($person) {
            $person->FullName = collect([
                $person->FirstName,
                $person->SecondName,
                $person->ThirdName,
                $person->FourthName,
            ])->filter()->implode(' ');

            return $person;
        });

    return response()->json($results);
}


 
// ── 3. Handle the POST — save the Qetaa change ───────────────────────────────
public function changePersonQetaa(Request $request, $id)
{
    $request->validate([
        'qetaa_id' => 'required|exists:Qetaa,QetaaID',
    ]);
 
    $person = DB::table('PersonInformation')->where('PersonID', $id)->first();
 
    if (!$person) {
        return view('person.entry-error');
    }
 
    $newQetaaId = $request->input('qetaa_id');
 
    DB::table('PersonQetaa')->updateOrInsert(
        ['PersonID' => $id],
        ['QetaaID'  => $newQetaaId]
    );
 
    return redirect()
        ->route('person.changeQetaa')
        ->with('status', 'تم تغيير القطاع بنجاح');
}

}
