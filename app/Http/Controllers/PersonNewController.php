<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Http\Response;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Http\Response;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PersonNewController extends Controller
{


/**
        * Display a listing of the resource.
        *
        * @return Response
        */
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

        public function indexNewEnrolmentsAndMigrations()
        {        
            $persons = DB::select("SELECT DISTINCT nui.PersonID, 
                                                    nui.FirstName, 
                                                    nui.SecondName, 
                                                    nui.ThirdName, 
                                                    nui.FourthName, 
                                                    nui.QetaaName, 
                                                    sm.SanaMarhalaName, 
                                                    nui.RaqamQawmy,
                                                    nui.IsApproved,
                                                    nui.PersonPersonalMobileNumber, 
                                                    IF(nupq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions 
                                                FROM NewUsersInformation nui 
                                                LEFT JOIN NewUsersPersonEntryQuestions nupq ON nui.PersonID = nupq.PersonID 
                                                LEFT JOIN SanaMarhala sm ON nui.SanaMarhalaID = sm.SanaMarhalaID;");
            //return $questionsDistinctCodes;
            return view("person.new-enrolments-migrate-index", array('persons' => $persons));
        }

        public function indexNewEnrolments()
        {        
            $persons = DB::select("SELECT DISTINCT nui.PersonID, 
                                                    nui.FirstName, 
                                                    nui.SecondName, 
                                                    nui.ThirdName, 
                                                    nui.FourthName, 


                                                            CONCAT_WS(' ',
                                                        nui.FirstName,
                                                        nui.SecondName,
                                                        nui.ThirdName,
                                                        nui.FourthName
                                                    ) AS FullName,
                                                    nui.QetaaName, 
                                                    sm.SanaMarhalaName, 
                                                    nui.RaqamQawmy,
                                                    nui.IsApproved,
                                                    nui.PersonPersonalMobileNumber, 
                                                    IF(nupq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions 
                                                FROM NewUsersInformation nui 
                                                LEFT JOIN NewUsersPersonEntryQuestions nupq ON nui.PersonID = nupq.PersonID 
                                                LEFT JOIN SanaMarhala sm ON nui.SanaMarhalaID = sm.SanaMarhalaID;");
            //return $questionsDistinctCodes;
            return view("person.new-enrolments-index", array('persons' => $persons));
        }

        public function showNewEnrolmentsByQetaaID($id)
        {
            $persons = DB::select("SELECT DISTINCT nui.PersonID, 
                                        nui.FirstName, 
                                        nui.SecondName, 
                                        nui.ThirdName, 
                                        nui.FourthName, 
                                        nui.QetaaName, 
                                        sm.SanaMarhalaName, 
                                        nui.RaqamQawmy,
                                        nui.IsApproved,
                                        nui.PersonPersonalMobileNumber, 
                                        IF(nupq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions 
                                    FROM NewUsersInformation nui 
                                    LEFT JOIN NewUsersPersonEntryQuestions nupq ON nui.PersonID = nupq.PersonID 
                                    LEFT JOIN SanaMarhala sm ON nui.SanaMarhalaID = sm.SanaMarhalaID
                                    WHERE nui.QetaaID = ?", [$id]);
            //return $persons;
            return view("person.new-enrolments-index", array('persons' => $persons));
        }

        public function analyticsNewEnrolments()
        {
            $analytics = DB::select("SELECT NewUsersInformation.QetaaID,
                                            NewUsersInformation.QetaaName,
                                            COUNT(*) AS CountOfRequests,
                                            COUNT(IF(NewUsersInformation.IsApproved = 1, 1, NULL)) AS CountOfApprovedRequests
                                    FROM NewUsersInformation
                                    LEFT JOIN SanaMarhala ON SanaMarhala.SanaMarhalaID = NewUsersInformation.SanaMarhalaID
                                    GROUP BY NewUsersInformation.QetaaID
                                    ORDER BY NewUsersInformation.QetaaID ASC");
            //return $analytics;
            return view('person.new-enrolments-analytics', array('analytics'=>$analytics));
        }


        public function showNewEnrolments($id)
        {
            $person = DB::table('NewUsersInformation')->where('PersonID',$id)
            ->leftJoin('BloodType', 'BloodType.BloodTypeID','=','NewUsersInformation.BloodTypeID')
            ->leftJoin('Qetaa', 'Qetaa.QetaaID', '=', 'NewUsersInformation.QetaaID')
            ->leftJoin('SanaMarhala', 'SanaMarhala.SanaMarhalaID', '=', 'NewUsersInformation.SanaMarhalaID')
            ->leftJoin('Manteqa', 'Manteqa.ManteqaID', '=', 'NewUsersInformation.ManteqaID')
            ->leftJoin('Districts', 'Districts.DistrictID', '=', 'NewUsersInformation.DistrictID')

            ->get()->first();
            
            $questions = DB::table('NewUsersPersonEntryQuestions')
            ->join('MarhalaEntryQuestions', 'MarhalaEntryQuestions.QuestionID', '=', 'NewUsersPersonEntryQuestions.QuestionID')

            ->select('MarhalaEntryQuestions.QuestionText','NewUsersPersonEntryQuestions.Answer')
            ->where('NewUsersPersonEntryQuestions.PersonID', $id)->get();

            //return $person->PersonID;
            return view('person.new-enrolments-show', array('person'=>$person, 'questions'=>$questions));
        }

        public function deleteNewEnrolments($id)
        {
            $person = DB::table('NewUsersInformation')->where('PersonID','=',$id)->select('NewUsersInformation.PersonID', 'NewUsersInformation.ShamandoraCode' ,   DB::raw("CONCAT(FirstName, ' ', SecondName, ' ', ThirdName, ' ', FourthName) as FullName"))->first();

            return view("person.new-enrolments-delete", array('person' => $person));
        }

        public function destroyNewEnrolments($id)
        {

            $person = DB::table('NewUsersInformation')->where('PersonID','=',$id)->select('NewUsersInformation.PersonID', 'NewUsersInformation.QetaaID')->first();

            DB::beginTransaction();

            DB::table('NewUsersInformation')->where('PersonID',$id)->delete();
            DB::table('NewUsersPersonEntryQuestions')->where('PersonID', $id)->delete();

            DB::commit();

            

            return redirect()->route('person.new-enrolments-index');
        }

 public function approveNewEnrolments($id)
{
    DB::table('NewUsersInformation')
        ->where('PersonID', $id)
        ->update(['IsApproved' => 1]);

    return redirect()->back()->with('success', 'تمت الموافقة بنجاح');
}

        // public function approveAgainNewEnrolments($id)
        // {
        //     $approvedInt = 1;
        //     DB::table('NewUsersInformation')->where('PersonID', $id)->update(['IsApproved' => $approvedInt]);
        //     $qetaa_id = DB::table('NewUsersInformation')->where('PersonID', $id)->first()->QetaaID;
        //     return redirect()->route('person.new-enrolments-index', $qetaa_id);
        // }


        public function editNewEnrolments($id)
{
    $person = DB::table('NewUsersInformation')
        ->where('PersonID', $id)
        ->leftJoin('BloodType', 'BloodType.BloodTypeID','=','NewUsersInformation.BloodTypeID')
        ->leftJoin('Qetaa', 'Qetaa.QetaaID', '=', 'NewUsersInformation.QetaaID')
        ->leftJoin('SanaMarhala', 'SanaMarhala.SanaMarhalaID', '=', 'NewUsersInformation.SanaMarhalaID')
        ->leftJoin('Manteqa', 'Manteqa.ManteqaID', '=', 'NewUsersInformation.ManteqaID')
        ->leftJoin('Districts', 'Districts.DistrictID', '=', 'NewUsersInformation.DistrictID')
        ->first();

    $questions = DB::table('NewUsersPersonEntryQuestions')
        ->join('MarhalaEntryQuestions', 'MarhalaEntryQuestions.QuestionID', '=', 'NewUsersPersonEntryQuestions.QuestionID')
        ->select('MarhalaEntryQuestions.QuestionText','NewUsersPersonEntryQuestions.Answer', 'MarhalaEntryQuestions.QuestionID')
        ->where('NewUsersPersonEntryQuestions.PersonID', $id)->get();

    $blood = DB::table('BloodType')->get();
    $manateq = DB::table('Manteqa')->get();
    $districts = DB::table('Districts')->get();
    $seneen_marahel = DB::table('SanaMarhala')->get();

    return view('person.new-enrolments-edit', [
        'person' => $person,
        'questions' => $questions,
        'blood' => $blood,
        'manateq' => $manateq,
        'districts' => $districts,
        'seneen_marahel' => $seneen_marahel,
    ]);
        }

        public function updateNewEnrolments(Request $request, $id)
        {
            // ✅ Check duplicate national ID
            $exists = DB::selectOne(
                'SELECT COUNT(*) as count FROM NewUsersInformation WHERE RaqamQawmy = ? AND PersonID != ?',
                [$request->input_raqam_qawmy, $id]
            );

            if ($exists->count > 0) {
                return view('person.person-already-exists');
            }

            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'second_name' => 'required',
                'third_name' => 'required',
                'gender' => 'required',
                'birthdate_input' => 'required',
                'input_raqam_qawmy' => 'required|min_digits:14|max_digits:14',
                'personal_phone_number' => 'required|min_digits:11|max_digits:11',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // ✅ National ID validation with birth date
            $birthDate = $request->birthdate_input;
            $nid = $request->input_raqam_qawmy;
            if (strlen($nid) == 14) {
                $year = substr($birthDate, 2, 2); // 13 from 2013
                $month = substr($birthDate, 5, 2); // 11
                $day = substr($birthDate, 8, 2); // 15
                $expected = substr($nid, 1, 2) . substr($nid, 3, 2) . substr($nid, 5, 2); // positions 2-3,4-5,6-7
                $actual = $year . $month . $day;
                if ($expected !== $actual) {
                    return redirect()->back()->withErrors(['input_raqam_qawmy' => 'الرقم القومي لا يتطابق مع تاريخ الميلاد'])->withInput();
                }
            }

            DB::beginTransaction();

            try {

                DB::table('NewUsersInformation')
                    ->where('PersonID', $id)
                    ->update([
                        'FirstName' => $request->first_name,
                        'SecondName' => $request->second_name,
                        'ThirdName' => $request->third_name,
                        'FourthName' => $request->fourth_name,
                        'Gender' => $request->gender,
                        'DateOfBirth' => $request->birthdate_input,
                        'RaqamQawmy' => $request->input_raqam_qawmy,
                        'ScoutJoiningYear' => $request->joining_year_input,
                        'BloodTypeID' => $request->blood_type_input,
                        'PersonPersonalMobileNumber' => $request->personal_phone_number,
                        'PersonalEmail' => $request->personal_email,
                        'FacebookProfileURL' => $request->facebook_profile_url,
                        'InstagramProfileURL' => $request->instagram_profile_url,
                        'FatherMobileNumber' => $request->father_mobile_number,
                        'MotherMobileNumber' => $request->mother_mobile_number,
                        'HomePhoneNumber' => $request->home_phone_number,
                        'IsOPersonalPhoneNumberHavingWhatsapp' => $request->is_personal_phone_has_whatsapp,
                        'BuildingNumber' => $request->building_number,
                        'FloorNumber' => $request->floor_number,
                        'AppartmentNumber' => $request->appartment_number,
                        'SubStreetName' => $request->sub_street_name,
                        'MainStreetName' => $request->main_street_name,
                        'NearestLandmark' => $request->nearest_landmark,
                        'ManteqaID' => $request->manteqa_id,
                        'DistrictID' => $request->district_id,
                        'SanaMarhalaID' => $request->sana_marhala_id,
                        'SchoolName' => $request->school_name,
                        'SchoolGraduationYear' => $request->school_graduation_year,
                        'SpiritualFatherName' => $request->spiritual_father_name,
                        'SpiritualFatherChurchName' => $request->spiritual_father_church_name,

                        // 🔥 NEW FIELDS (important)
                        'AllergyFood' => $request->allergy_food,
                        'AllergyMedicine' => $request->allergy_medicine,
                        'MedicalDiseases' => $request->medical_diseases,
                        'MedicalMedications' => $request->medical_medications,
                        'HasEmergencyCase' => $request->has_emergency_case ? 1 : 0,
                        'EmergencyDetails' => $request->emergency_details,
                    ]);

                // Update questions
                $questions = DB::table('MarhalaEntryQuestions')
                    ->join('NewUsersPersonEntryQuestions', 'MarhalaEntryQuestions.QuestionID', '=', 'NewUsersPersonEntryQuestions.QuestionID')
                    ->where('NewUsersPersonEntryQuestions.PersonID', $id)
                    ->select('MarhalaEntryQuestions.QuestionID')
                    ->get();

                foreach ($questions as $question) {
                    $answer = $request->input('question_' . $question->QuestionID);
                    if ($answer !== null) {
                        DB::table('NewUsersPersonEntryQuestions')
                            ->where('PersonID', $id)
                            ->where('QuestionID', $question->QuestionID)
                            ->update(['Answer' => $answer]);
                    }
                }

                DB::commit();

                return redirect()->route('person.new-enrolments-index');

            } catch (\Exception $e) {

                DB::rollBack();

                Log::error("Update New Enrolment Error", [
                    'message' => $e->getMessage()
                ]);

                return back()->with('error', 'Something went wrong');
            }
        }

public function createLiveForm()
{
    $seneen_marahel = DB::table('SanaMarhala')->get();

    return view('person.person-create-liveform-1', [
        'seneen_marahel' => $seneen_marahel,
    ]);
}

public function insertLiveForm(Request $request)
{
    $request->validate([
        'sana_marhala_id' => 'required',
        'gender' => 'required|in:Male,Female',
        'newLeadersSchool' => 'nullable',
    ]);

    [$qetaa_id, $qetaa_name, $gender] = $this->resolveLiveFormQetaa(
        (int) $request->sana_marhala_id,
        $request->gender,
        (bool) $request->newLeadersSchool
    );

    $marhala_limit = DB::table('MarhalaLiveFormLimit')
        ->where('QetaaID', $qetaa_id)
        ->where('SanaMarhalaID', $request->sana_marhala_id)
        ->value('MaxLimit') ?? 0;

    $numberOfStudentsCurrentlySubmittedInSanaMarhala = DB::table('NewUsersInformation')
        ->where('QetaaID', $qetaa_id)
        ->where('SanaMarhalaID', $request->sana_marhala_id)
        ->count();

    if ($marhala_limit == 0 || $numberOfStudentsCurrentlySubmittedInSanaMarhala >= $marhala_limit) {
       return view('person.liveform-limit-exceeded', [
    'qetaa_name' => $qetaa_name,
    'sana_marhala_name' => $sana_marhala_name ?? null,
    'current_count' => $numberOfStudentsCurrentlySubmittedInSanaMarhala,
    'max_limit' => $marhala_limit,
]);
    }

    $sana_marhala_name = DB::table('SanaMarhala')
        ->where('SanaMarhalaID', $request->sana_marhala_id)
        ->value('SanaMarhalaName');

    session([
        'liveform.step1' => [
            'sana_marhala_id' => (int) $request->sana_marhala_id,
            'sana_marhala_name' => $sana_marhala_name,
            'gender' => $gender,
            'qetaa_id' => $qetaa_id,
            'qetaa_name' => $qetaa_name,
            'newLeadersSchool' => (bool) $request->newLeadersSchool,
        ],
    ]);

    return redirect()->route('person.liveform-step2');
}

public function showLiveFormStep2()
{
    $step1 = session('liveform.step1');

    if (!$step1) {
        return redirect()->route('person.liveform-create');
    }

    return view('person.person-create-liveform', array_merge(
        $this->getLiveFormStep2Lookups(),
        [
            'sana_marhala_id' => $step1['sana_marhala_id'],
            'sana_marhala_name' => $step1['sana_marhala_name'],
            'qetaa_id' => $step1['qetaa_id'],
            'qetaa_name' => $step1['qetaa_name'],
            'gender' => $step1['gender'],
        ]
    ));
}

public function saveLiveFormStep2(Request $request)
{
    $step1 = session('liveform.step1');

    if (!$step1) {
        return redirect()->route('person.liveform-create');
    }

    $rules = [
        'first_name' => 'required|string|max:255',
        'second_name' => 'required|string|max:255',
        'third_name' => 'required|string|max:255',
        'fourth_name' => 'nullable|string|max:255',
        'birthdate_input' => 'required|date',
        'joining_year_input' => 'required',
        'input_raqam_qawmy' => 'required|digits:14',
        'blood_type_input' => 'required',
        'personal_phone_number' => 'required|digits:11',
        'father_phone_number' => 'nullable',
        'mother_phone_number' => 'nullable',
        'home_phone_number' => 'nullable',
        'building_number' => 'required',
        'floor_number' => 'required',
        'appartment_number' => 'required',
        'main_street_name' => 'nullable|string|max:255',
        'sub_street_name' => 'required|string|max:255',
        'nearest_landmark' => 'nullable|string|max:255',
        'manteqa_id' => 'required',
        'district_id' => 'required',
        'inputFacebookLink' => 'nullable|string|max:500',
        'inputInstagramLink' => 'nullable|string|max:500',
        'email_input' => 'nullable|email|max:255',
        'spiritual_father' => 'nullable|string|max:255',
        'spiritual_father_church' => 'nullable|string|max:255',
        'person_school' => 'nullable|string|max:255',
        'school_grad_year' => 'nullable',
        'person_faculty' => 'nullable',
        'person_university' => 'nullable',
        'university_grad_year' => 'nullable',

        'profile_image' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',
        'scout_uniform_image' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:5120',

        'allergy_food' => 'nullable|string|max:2000',
        'allergy_medicine' => 'nullable|string|max:2000',
        'medical_diseases' => 'nullable|string|max:2000',
        'medical_medications' => 'nullable|string|max:2000',
        'has_emergency_case' => 'nullable',
        'emergency_details' => 'nullable|string|max:255',
    ];

    $validator = Validator::make($request->all(), $rules);

    $hasEmergency = $request->boolean('has_emergency_case');

    $validator->sometimes('emergency_details', 'required|string|max:255', function () use ($hasEmergency) {
        return $hasEmergency === true;
    });

    $validator->after(function ($validator) use ($request) {
        $birthDate = (string) $request->birthdate_input;
        $nid = (string) $request->input_raqam_qawmy;

        if (strlen($nid) === 14 && strlen($birthDate) >= 10) {
            $year = substr($birthDate, 2, 2);
            $month = substr($birthDate, 5, 2);
            $day = substr($birthDate, 8, 2);

            $expected = substr($nid, 1, 2) . substr($nid, 3, 2) . substr($nid, 5, 2);
            $actual = $year . $month . $day;

            if ($expected !== $actual) {
                $validator->errors()->add('input_raqam_qawmy', 'الرقم القومي لا يتطابق مع تاريخ الميلاد');
            }
        }
    });

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

 $raqam = $request->input('input_raqam_qawmy');

$existsInNewUsers = DB::table('NewUsersInformation')
    ->where('RaqamQawmy', $raqam)
    ->exists();

$existsInPersonInformation = DB::table('PersonInformation')
    ->where('RaqamQawmy', $raqam)
    ->exists();

if ($existsInNewUsers || $existsInPersonInformation) {
    return view('person.person-already-exists');
}
    $profileImagePath = session('liveform.step2.profile_image');
    $scoutImagePath = session('liveform.step2.scout_uniform_image');

    if ($request->hasFile('profile_image')) {
        $profileImagePath = $request->file('profile_image')->store('temp/liveform', 'public');
    }

    if ($request->hasFile('scout_uniform_image')) {
        $scoutImagePath = $request->file('scout_uniform_image')->store('temp/liveform', 'public');
    }

    session([
        'liveform.step2' => [
            'first_name' => $request->first_name,
            'second_name' => $request->second_name,
            'third_name' => $request->third_name,
            'fourth_name' => $request->fourth_name,
            'birthdate_input' => $request->birthdate_input,
            'joining_year_input' => $request->joining_year_input,
            'input_raqam_qawmy' => $request->input_raqam_qawmy,
            'blood_type_input' => $request->blood_type_input,
            'personal_phone_number' => $request->personal_phone_number,
            'father_phone_number' => $request->father_phone_number,
            'mother_phone_number' => $request->mother_phone_number,
            'home_phone_number' => $request->home_phone_number,
            'has_whatsapp' => $request->has_whatsapp,
            'building_number' => $request->building_number,
            'floor_number' => $request->floor_number,
            'appartment_number' => $request->appartment_number,
            'main_street_name' => $request->main_street_name,
            'sub_street_name' => $request->sub_street_name,
            'nearest_landmark' => $request->nearest_landmark,
            'manteqa_id' => $request->manteqa_id,
            'district_id' => $request->district_id,
            'inputFacebookLink' => $request->inputFacebookLink,
            'inputInstagramLink' => $request->inputInstagramLink,
            'email_input' => $request->email_input,
            'spiritual_father' => $request->spiritual_father,
            'spiritual_father_church' => $request->spiritual_father_church,
            'person_school' => $request->person_school,
            'school_grad_year' => $request->school_grad_year,
            'person_faculty' => $request->person_faculty,
            'person_university' => $request->person_university,
            'university_grad_year' => $request->university_grad_year,

            'allergy_food' => $request->allergy_food,
            'allergy_medicine' => $request->allergy_medicine,
            'medical_diseases' => $request->medical_diseases,
            'medical_medications' => $request->medical_medications,
            'has_emergency_case' => $hasEmergency ? 1 : 0,
            'emergency_details' => $request->emergency_details,

            'profile_image' => $profileImagePath,
            'scout_uniform_image' => $scoutImagePath,
        ],
    ]);

    return redirect()->route('person.entry-questions-liveform');
}

public function getLiveformQuestions()
{
    $step1 = session('liveform.step1');
    $step2 = session('liveform.step2');

    if (!$step1 || !$step2) {
        return redirect()->route('person.liveform-create');
    }

    $questions = DB::table('MarhalaEntryQuestions')
        ->where('QetaaID', $step1['qetaa_id'])
        ->where('NotToBeShown', 0)
        ->get();

    $person = (object) [
        'PersonID' => null,
        'QetaaID' => $step1['qetaa_id'],
        'QetaaName' => $step1['qetaa_name'],
        'Gender' => $step1['gender'],
        'SanaMarhalaID' => $step1['sana_marhala_id'],
        'SanaMarhalaName' => $step1['sana_marhala_name'],
        'FirstName' => $step2['first_name'],
        'SecondName' => $step2['second_name'],
        'ThirdName' => $step2['third_name'],
        'FourthName' => $step2['fourth_name'],
        'RaqamQawmy' => $step2['input_raqam_qawmy'],
    ];

 return view('person.person-questions-liveform', [
    'questions' => $questions,
    'person' => $person,
    'existingAnswers' => [],
    'is_resume_mode' => false,
]);
}

public function submitLiveformQuestions(Request $request)
{
    $step1 = session('liveform.step1');
    $step2 = session('liveform.step2');

    if (!$step1 || !$step2) {
        return redirect()->route('person.liveform-create');
    }

    $questions = DB::table('MarhalaEntryQuestions')
        ->where('QetaaID', $step1['qetaa_id'])
        ->where('NotToBeShown', 0)
        ->get();

    foreach ($questions as $question) {
        $q = $request->input($question->QuestionID);

        if ($question->IsRequired && ($q === null || $q === '')) {
            return view('person.entry-error-repeat-trial');
        }
    }

    DB::beginTransaction();

    try {
        $exists = DB::table('NewUsersInformation')
            ->where('RaqamQawmy', $step2['input_raqam_qawmy'])
            ->lockForUpdate()
            ->exists();

        if ($exists) {
            DB::rollBack();
            return view('person.person-already-exists');
        }

        $last = DB::table('NewUsersInformation')
            ->orderBy('PersonID', 'desc')
            ->lockForUpdate()
            ->first();

        $thisPersonID = is_null($last) ? 1 : ((int) $last->PersonID + 1);

        $shamandoraCode = 'SH-' . str_pad((string) $thisPersonID, 5, '0', STR_PAD_LEFT);

        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $pass[] = $alphabet[rand(0, $alphaLength)];
        }
        $passString = implode($pass);

        $personalImagePath = $this->finalizeTempLiveformFile($step2['profile_image'] ?? null);
        $scoutImagePath = $this->finalizeTempLiveformFile($step2['scout_uniform_image'] ?? null);

        DB::table('NewUsersInformation')->insert([
            'PersonID' => $thisPersonID,
            'ShamandoraCode' => $shamandoraCode,
            'FirstName' => $step2['first_name'],
            'SecondName' => $step2['second_name'],
            'ThirdName' => $step2['third_name'],
            'FourthName' => $step2['fourth_name'],
            'Gender' => $step1['gender'],
            'DateOfBirth' => $step2['birthdate_input'],
            'RaqamQawmy' => $step2['input_raqam_qawmy'],
            'ScoutJoiningYear' => $step2['joining_year_input'],
            'BloodTypeID' => $step2['blood_type_input'],
            'FacebookProfileURL' => $step2['inputFacebookLink'],
            'InstagramProfileURL' => $step2['inputInstagramLink'],
            'PersonalEmail' => $step2['email_input'],
            'BuildingNumber' => $step2['building_number'],
            'FloorNumber' => $step2['floor_number'],
            'AppartmentNumber' => $step2['appartment_number'],
            'MainStreetName' => $step2['main_street_name'],
            'SubStreetName' => $step2['sub_street_name'],
            'ManteqaID' => $step2['manteqa_id'],
            'DistrictID' => is_null($step2['district_id']) ? 1 : $step2['district_id'],
            'NearestLandmark' => $step2['nearest_landmark'],
            'SanaMarhalaID' => $step1['sana_marhala_id'],
            'SpiritualFatherName' => $step2['spiritual_father'],
            'SpiritualFatherChurchName' => $step2['spiritual_father_church'],
            'Password' => $passString,
            'PersonPersonalMobileNumber' => $step2['personal_phone_number'],
            'FatherMobileNumber' => $step2['father_phone_number'],
            'MotherMobileNumber' => $step2['mother_phone_number'],
            'HomePhoneNumber' => $step2['home_phone_number'],
            'IsOPersonalPhoneNumberHavingWhatsapp' => $step2['has_whatsapp'],
            'SchoolName' => $step2['person_school'],
            'SchoolGraduationYear' => $step2['school_grad_year'],
            'QetaaID' => $step1['qetaa_id'],
            'QetaaName' => $step1['qetaa_name'],
            'FacultyID' => $step2['person_faculty'],
            'UniversityID' => $step2['person_university'],
            'UniversityGraduationYear' => $step2['university_grad_year'],
            'PersonalImagePath' => $personalImagePath,
            'ScoutImagePath' => $scoutImagePath,
            'AllergyFood' => $this->cleanLiveFormList($step2['allergy_food'] ?? null),
            'AllergyMedicine' => $this->cleanLiveFormList($step2['allergy_medicine'] ?? null),
            'MedicalDiseases' => $this->cleanLiveFormList($step2['medical_diseases'] ?? null),
            'MedicalMedications' => $this->cleanLiveFormList($step2['medical_medications'] ?? null),
            'HasEmergencyCase' => !empty($step2['has_emergency_case']) ? 1 : 0,
            'EmergencyDetails' => !empty($step2['has_emergency_case'])
                ? trim((string) ($step2['emergency_details'] ?? ''))
                : null,
        ]);

        foreach ($questions as $question) {
            DB::table('NewUsersPersonEntryQuestions')->insert([
                'PersonID' => $thisPersonID,
                'QuestionID' => $question->QuestionID,
                'Answer' => $request->input($question->QuestionID),
            ]);
        }

        DB::commit();

        session()->forget('liveform');

        return view('person.liveform-finalize');
    } catch (\Throwable $e) {
        DB::rollBack();

        Log::error('submitLiveformQuestions failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return view('person.entry-error');
    }
}

private function resolveLiveFormQetaa(int $sanaMarhalaId, string $gender, bool $newLeadersSchool): array
{
    if ($newLeadersSchool) {
        return [10, 'اعداد قادة', $gender];
    }

    if ($sanaMarhalaId < 5 && $sanaMarhalaId > 2) {
        return [1, 'براعم', $gender];
    }

    if ($sanaMarhalaId < 9 && $sanaMarhalaId > 4) {
        return $gender === 'Male'
            ? [2, 'أشبال', 'Male']
            : [9, 'زهرات', 'Female'];
    }

    if ($sanaMarhalaId < 12 && $sanaMarhalaId > 8) {
        return $gender === 'Male'
            ? [8, 'كشافة', 'Male']
            : [6, 'مرشدات', 'Female'];
    }

    if ($sanaMarhalaId <= 14 && $sanaMarhalaId > 11) {
        return $gender === 'Male'
            ? [3, 'متقدم', 'Male']
            : [4, 'رائدات', 'Female'];
    }

    if ($sanaMarhalaId < 21 && $sanaMarhalaId > 14) {
        return [5, 'جوالة', $gender];
    }

    return [7, 'قادة', $gender];
}

private function getLiveFormStep2Lookups(): array
{
    return [
        'marahel' => DB::table('Marhala')->get(),
        'rotab' => DB::table('RotbaInformation')->get(),
        'questionTypes' => DB::table('QuestionsTypes')->get(),
        'blood' => DB::table('BloodType')->get(),
        'manateq' => DB::table('Manteqa')->get(),
        'districts' => DB::table('Districts')->get(),
        'faculties' => DB::table('Faculty')->get(),
        'universities' => DB::table('University')->get(),
    ];
}

private function cleanLiveFormList(?string $value): ?string
{
    if ($value === null) {
        return null;
    }

    $value = trim($value);

    if ($value === '') {
        return null;
    }

    $value = str_replace(["\r\n", "\n", "،", ";"], ',', $value);

    $parts = array_filter(array_map('trim', explode(',', $value)), function ($x) {
        return $x !== '';
    });

    $parts = array_values(array_unique($parts));

    return count($parts) ? implode(', ', $parts) : null;
}

private function finalizeTempLiveformFile(?string $path): ?string
{
    if (!$path) {
        return null;
    }

    if (!Storage::disk('public')->exists($path)) {
        return $path;
    }

    if (str_starts_with($path, 'person_images/')) {
        return $path;
    }

    $basename = basename($path);
    $target = 'person_images/' . $basename;

    if (Storage::disk('public')->exists($target)) {
        $target = 'person_images/' . uniqid() . '_' . $basename;
    }

    Storage::disk('public')->move($path, $target);

    return $target;
}
        /**
            * Display the specified resource.
            *
            * @param  int  $id
            * @return Response
            */
        public function show($id)
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
            ->where('PersonInformation.PersonID', $id)->get()->first();
            
            $questions = DB::table('PersonEntryQuestions')
                    ->join('MarhalaEntryQuestions', 'MarhalaEntryQuestions.QuestionID', '=', 'PersonEntryQuestions.QuestionID')
                    ->select('MarhalaEntryQuestions.QuestionText','PersonEntryQuestions.Answer')
                    ->where('PersonEntryQuestions.PersonID', $id)->get();
            
            //return $person;
            return view('person.person-show', array('person'=>$person, 'questions'=>$questions));
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
            $entryQuestions = DB::table('MarhalaEntryQuestions')->where('QuestionID', $id)->first();
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
            ->where('PersonInformation.PersonID', $id)->get()->first();
            
            $questions = DB::table('PersonEntryQuestions')
                    ->join('MarhalaEntryQuestions', 'MarhalaEntryQuestions.QuestionID', '=', 'PersonEntryQuestions.QuestionID')
                    ->select('MarhalaEntryQuestions.QuestionText','PersonEntryQuestions.Answer')
                    ->where('PersonEntryQuestions.PersonID', $id)->get();
            
            return view('person.person-edit', 
                        array(
                            'marahel'=>$marahel, 
                            'rotab'=>$rotab,
                            'seneen_marahel'=>$seneen_marahel,
                            'questionTypes'=>$questionTypes,
                            'blood'=>$blood,
                            'betakat'=>$betakat,
                            'manateq'=>$manateq,
                            'districts'=>$districts,
                            'faculties'=>$faculties,
                            'universities'=>$universities,
                            'questionTypes'=>$questionTypes,
                            'entryQuestions'=>$entryQuestions,
                            'person'=>$person,
                            'questions'=>$questions,
                        ));
            
        }
    
        public function updates(Request $request, $id)
        {
            

            $raqamQawmyObject = DB::selectOne('SELECT COUNT(*) AS `counts` FROM PersonInformation WHERE RaqamQawmy=? AND PersonID!=?;', [$request->input_raqam_qawmy,$id]);
            $raqamQawmyCounts = $raqamQawmyObject->counts;
              
            
              if($raqamQawmyCounts>0)
              {
                  return view('person.person-already-exists');
              }
            

            

            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'second_name' => 'required',
                'third_name' => 'required',
                'gender'=>'required',
                'birthdate_input' => 'required',
                'joining_year_input' => 'required',
                'input_raqam_qawmy' => 'required|min_digits:14|max_digits:14',
                'blood_type_input'=>'required',
                'personal_phone_number'=>'required|min_digits:11|max_digits:11',
                'building_number'=>'required',
                'floor_number'=>'required',
                'appartment_number' =>'required',
                'sub_street_name' => 'required',
                'manteqa_id'=>'required',
                'district_id'=>'required',
                'sana_marhala_id'=>'required'
            ]);
     
            if ($validator->fails()) {
                return view('person.entry-error-repeat-trial');
            }

            //return $request;

            try{
            DB::beginTransaction();
            
                DB::table('PersonInformation')->where('PersonID',$id)->update(
                    array(
                        'FirstName' => $request->first_name,
                        'SecondName' => $request->second_name,
                        'ThirdName'   => $request->third_name,
                        'FourthName' => $request->fourth_name,
                        'Gender' => $request->gender,
                        'DateOfBirth' => $request->birthdate_input,
                        'RaqamQawmy' => $request->input_raqam_qawmy,
                        'ScoutJoiningYear'  => $request->joining_year_input,
                        'BloodTypeID' => $request->blood_type_input,
                        'FacebookProfileURL' =>$request->inputFacebookLink,
                        'InstagramProfileURL' =>$request->inputInstagramLink,
                        'PersonalEmail' => $request->email_input,
                        'RequestPersonID' => $request->RequestPersonID,
                    )
                );
    
    
                DB::table('PersonPhoneNumbers')->where('PersonID',$id)->update(
                    array(
                        'PersonPersonalMobileNumber' => $request->personal_phone_number,
                        'FatherMobileNumber' => $request->father_phone_number,
                        'MotherMobileNumber'   => $request->mother_phone_number,
                        'HomePhoneNumber' => $request->home_phone_number,
                        'IsOPersonalPhoneNumberHavingWhatsapp' => $request->has_whatsapp,
                    )
                );
    
                DB::table('PersonJob')->where('PersonID',$id)->update(
                    array(
                        'JobName'=>$request->person_job,
                        'WorkPlace'=>$request->person_job_place
                    )
                );
    
                DB::table('PersonLearningInformation')->where('PersonID',$id)->update(
                    array(
                        'SchoolName'=>$request->school_name,
                        'SchoolGraduationYear'=>$request->school_grad_year,
                        'FacultyID'=>$request->person_faculty,
                        'UniversityID'=>$request->person_university,
                        'ActualFacultyGraduationYear'=>$request->university_grad_year
                    )
                );

    
                DB::table('PersonRotbaKashfeyya')->where('PersonID',$id)->update(
                    array(
                        'RotbaID'=>$request->rotba_kashfeyya_id
                    )
                );
    
    
    
                DB::table('PersonEgazetBetakatTaqaddom')->where('PersonID',$id)->update(
                    array(
                        'EgazetBetakatTaqaddomID'=>$request->betaka_id
                    )
                );
    
                DB::table('PersonSanaMarhala')->where('PersonID',$id)->update(
                    array(

                        'SanaMarhalaID'=>$request->sana_marhala_id
                    )
                );
    
                DB::table('PersonSpiritualFatherInformation')->where('PersonID',$id)->update(
                    array(
                        'SpiritualFatherName'=>$request->spiritual_father,
                        'SpiritualFatherChurchName'=>$request->spiritual_father_church
                    )
                );
    
    
                DB::table('PersonalPhysicalAddress')->where('PersonID',$id)->update(
                    array(
                        'BuildingNumber'=>$request->building_number,
                        'FloorNumber'=>$request->floor_number,
                        'AppartmentNumber'=>$request->appartment_number,
                        'MainStreetName'=>$request->main_street_name,
                        'SubStreetName'=>$request->sub_street_name,
                        'ManteqaID'=>$request->manteqa_id,
                        'DistrictID'=>is_null($request->district_id)?1:$request->district_id,
                        'NearestLandmark'=>$request->nearest_landmark
                    )
                );
            }
            catch(Exception $e)
            {
                dd($e->getMessage());
                DB::rollBack();
                return view('person.entry-error');
            }

            DB::commit();
            
            return redirect()->route('person.index');
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



public function resumeLegacyLiveformQuestions($id)
{
    $person = DB::table('NewUsersInformation')
        ->where('PersonID', $id)
        ->first();

    if (!$person) {
        abort(404);
    }

    $questions = DB::table('MarhalaEntryQuestions')
        ->where('QetaaID', $person->QetaaID)
        ->where('NotToBeShown', 0)
        ->get();

    $existingAnswers = DB::table('NewUsersPersonEntryQuestions')
        ->where('PersonID', $id)
        ->pluck('Answer', 'QuestionID');

    return view('person.person-questions-liveform', [
        'person' => $person,
        'questions' => $questions,
        'existingAnswers' => $existingAnswers,
        'is_resume_mode' => true,
    ]);
}

public function submitLegacyLiveformQuestions(Request $request, $id)
{
    $person = DB::table('NewUsersInformation')
        ->where('PersonID', $id)
        ->first();

    if (!$person) {
        abort(404);
    }

    $questions = DB::table('MarhalaEntryQuestions')
        ->where('QetaaID', $person->QetaaID)
        ->where('NotToBeShown', 0)
        ->get();

    foreach ($questions as $question) {
        $answer = $request->input($question->QuestionID);

        if ($question->IsRequired && ($answer === null || $answer === '')) {
            return view('person.entry-error-repeat-trial');
        }
    }

    DB::beginTransaction();

    try {
        foreach ($questions as $question) {
            $answer = $request->input($question->QuestionID);

            $exists = DB::table('NewUsersPersonEntryQuestions')
                ->where('PersonID', $id)
                ->where('QuestionID', $question->QuestionID)
                ->exists();

            if ($exists) {
                DB::table('NewUsersPersonEntryQuestions')
                    ->where('PersonID', $id)
                    ->where('QuestionID', $question->QuestionID)
                    ->update([
                        'Answer' => $answer,
                    ]);
            } else {
                DB::table('NewUsersPersonEntryQuestions')->insert([
                    'PersonID' => $id,
                    'QuestionID' => $question->QuestionID,
                    'Answer' => $answer,
                ]);
            }
        }

        DB::commit();

        return view('person.liveform-finalize');
    } catch (\Throwable $e) {
        DB::rollBack();

        Log::error('submitLegacyLiveformQuestions failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return view('person.entry-error');
    }
}

}