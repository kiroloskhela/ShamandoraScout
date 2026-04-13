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

    $sana_marhala_name = DB::table('SanaMarhala')
        ->where('SanaMarhalaID', $request->sana_marhala_id)
        ->value('SanaMarhalaName');

    $qetaat = $this->resolveLiveFormQetaa(
        (int) $request->sana_marhala_id,
        $request->gender,
        (bool) $request->newLeadersSchool
    );

    if (empty($qetaat)) {
        return back()->withErrors([
            'qetaa_id' => 'لا يوجد قطاع كشفي متاح لهذه المرحلة'
        ])->withInput();
    }

    $available_qetaat = [];

    foreach ($qetaat as $qetaa) {
        $qetaa_id = $qetaa[0];
        $qetaa_name = $qetaa[1];
        $gender = $qetaa[2];

        $marhala_limit = DB::table('MarhalaLiveFormLimit')
            ->where('QetaaID', $qetaa_id)
            ->where('SanaMarhalaID', $request->sana_marhala_id)
            ->value('MaxLimit') ?? 0;

        $numberOfStudentsCurrentlySubmittedInSanaMarhala = DB::table('NewUsersInformation')
            ->where('QetaaID', $qetaa_id)
            ->where('SanaMarhalaID', $request->sana_marhala_id)
            ->count();

        // أضف فقط القطاعات التي ما زال فيها أماكن
      $available_qetaat[] = [
    'QetaaID' => $qetaa_id,
    'QetaaName' => $qetaa_name,
    'gender' => $gender,
    'current_count' => $numberOfStudentsCurrentlySubmittedInSanaMarhala,
    'max_limit' => $marhala_limit,
    'is_full' => ($marhala_limit == 0 || $numberOfStudentsCurrentlySubmittedInSanaMarhala >= $marhala_limit),
];
    }

    if (empty($available_qetaat)) {
        return view('person.liveform-limit-exceeded', [
            'qetaa_name' => null,
            'sana_marhala_name' => $sana_marhala_name,
            'current_count' => null,
            'max_limit' => null,
        ]);
    }

    session([
        'liveform.step1' => [
            'sana_marhala_id' => (int) $request->sana_marhala_id,
            'sana_marhala_name' => $sana_marhala_name,
            'gender' => $request->gender,
            'newLeadersSchool' => (bool) $request->newLeadersSchool,
            'available_qetaat' => $available_qetaat,

            // لو قطاع واحد فقط، اختاره تلقائيًا
            'qetaa_id' => count($available_qetaat) === 1 ? $available_qetaat[0]['QetaaID'] : null,
            'qetaa_name' => count($available_qetaat) === 1 ? $available_qetaat[0]['QetaaName'] : null,
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
                        'available_qetaat' => $step1['available_qetaat'] ?? [],
            'gender' => $step1['gender'],
        ]
    ));
}
private function normalizeArabicName(?string $value): ?string
{
    if ($value === null) {
        return null;
    }

    $value = trim($value);

    if ($value === '') {
        return $value;
    }

    $search = [
        'أ', 'إ', 'آ', 'ٱ',
        'ى', 'ئ', 'ي',
        'ؤ',
        'ة',
        'چ',
    ];

    $replace = [
        'ا', 'ا', 'ا', 'ا',
        'ي', 'ي', 'ي',
        'و',
        'ه',
        'ج',
    ];

    $value = str_replace($search, $replace, $value);

    $value = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}\x{0640}]/u', '', $value);
    $value = preg_replace('/[\x{200E}\x{200F}\x{061C}\x{202A}-\x{202E}]/u', '', $value);
    $value = preg_replace('/[^\p{Arabic}\s]/u', '', $value);
    $value = preg_replace('/\s+/u', ' ', $value);

    return trim($value);
}

private function normalizeArabicFields(array $data, array $fields): array
{
    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $data[$field] = $this->normalizeArabicName($data[$field]);
        }
    }

    return $data;
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
        'qetaa_id' => 'required',
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

      $data = $validator->validated();


    $data = $this->normalizeArabicFields($data, [
    'first_name',
    'second_name',
    'third_name',
    'fourth_name',
]);

    $selectedQetaa = collect($step1['available_qetaat'] ?? [])
    ->firstWhere('QetaaID', (int) $request->qetaa_id);

if (!$selectedQetaa) {
    return redirect()->back()
        ->withErrors(['qetaa_id' => 'برجاء اختيار قطاع صحيح'])
        ->withInput();
}

$step1['qetaa_id'] = $selectedQetaa['QetaaID'];
$step1['qetaa_name'] = $selectedQetaa['QetaaName'];

session(['liveform.step1' => $step1]);

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
             'first_name' => $data['first_name'],
        'second_name' => $data['second_name'],
        'third_name' => $data['third_name'],
        'fourth_name' => $data['fourth_name'] ?? null,
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
        ->orderBy('QuestionID')
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
    if ($newLeadersSchool && $sanaMarhalaId > 14) {
        return [
            [10, 'اعداد قادة', $gender]
        ];
    }

    if ($sanaMarhalaId < 5 && $sanaMarhalaId > 2) {
        return [
            [1, 'براعم', $gender]
        ];
    }

    if ($sanaMarhalaId < 9 && $sanaMarhalaId > 4) {
        return [
            [$gender === 'Male' ? 2 : 9, $gender === 'Male' ? 'أشبال' : 'زهرات', $gender]
        ];
    }

    if ($sanaMarhalaId < 12 && $sanaMarhalaId > 8) {
        return [
            [$gender === 'Male' ? 8 : 6, $gender === 'Male' ? 'كشافة' : 'مرشدات', $gender]
        ];
    }

    if ($sanaMarhalaId <= 14 && $sanaMarhalaId > 11) {
        return [
            [$gender === 'Male' ? 3 : 4, $gender === 'Male' ? 'متقدم' : 'رائدات', $gender]
        ];
    }

    if ($sanaMarhalaId <= 21 && $sanaMarhalaId > 14) {
        return [
            [5, 'جوالة', $gender],
            [7, 'قادة', $gender],
        ];
    }

    return [
        [7, 'قادة', $gender]
    ];
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

$questions = DB::table('MarhalaEntryQuestions')
    ->leftJoin('PersonEntryQuestions', function ($join) use ($id) {
        $join->on('PersonEntryQuestions.QuestionID', '=', 'MarhalaEntryQuestions.QuestionID')
             ->where('PersonEntryQuestions.PersonID', '=', $id);
    })
    ->select(
        'MarhalaEntryQuestions.QuestionID',
        'MarhalaEntryQuestions.QuestionText',
        'MarhalaEntryQuestions.RequiredAnswerType',
        'MarhalaEntryQuestions.MCAnswer',
        'PersonEntryQuestions.Answer'
    )
    ->where('MarhalaEntryQuestions.NotToBeShown', 0)
    ->where('MarhalaEntryQuestions.QetaaID', $person->QetaaID)
    ->orderBy('MarhalaEntryQuestions.QuestionID', 'asc')
    ->get();

    return view('person.person-edit', [
        'marahel' => $marahel,
        'rotab' => $rotab,
        'seneen_marahel' => $seneen_marahel,
        'questionTypes' => $questionTypes,
        'blood' => $blood,
        'betakat' => $betakat,
        'manateq' => $manateq,
        'districts' => $districts,
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

    // Only check duplicate national ID if user actually sent one
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
        'first_name' => 'sometimes|nullable|string|max:255',
        'second_name' => 'sometimes|nullable|string|max:255',
        'third_name' => 'sometimes|nullable|string|max:255',
        'fourth_name' => 'sometimes|nullable|string|max:255',
        'gender' => 'sometimes|nullable|in:Male,Female',
        'birthdate_input' => 'sometimes|nullable|date',
        'joining_year_input' => 'sometimes|nullable|integer',
        'input_raqam_qawmy' => 'sometimes|nullable|digits:14',
        'blood_type_input' => 'sometimes|nullable|integer',
        'email_input' => 'sometimes|nullable|email|max:255',
        'inputFacebookLink' => 'sometimes|nullable|url|max:1000',
        'inputInstagramLink' => 'sometimes|nullable|url|max:1000',

        'personal_phone_number' => 'sometimes|nullable|digits_between:11,11',
        'father_phone_number' => 'sometimes|nullable|digits_between:11,11',
        'mother_phone_number' => 'sometimes|nullable|digits_between:11,11',
        'home_phone_number' => 'sometimes|nullable|string|max:50',
        'has_whatsapp' => 'sometimes|nullable|in:0,1',

        'building_number' => 'sometimes|nullable|string|max:255',
        'floor_number' => 'sometimes|nullable|string|max:255',
        'appartment_number' => 'sometimes|nullable|string|max:255',
        'main_street_name' => 'sometimes|nullable|string|max:255',
        'sub_street_name' => 'sometimes|nullable|string|max:255',
        'nearest_landmark' => 'sometimes|nullable|string|max:1000',
        'manteqa_id' => 'sometimes|nullable|integer',
        'district_id' => 'sometimes|nullable|integer',

        'sana_marhala_id' => 'sometimes|nullable|integer',
        'person_job' => 'sometimes|nullable|string|max:255',
        'person_job_place' => 'sometimes|nullable|string|max:255',
        'school_name' => 'sometimes|nullable|string|max:255',
        'school_grad_year' => 'sometimes|nullable|string|max:50',
        'person_faculty' => 'sometimes|nullable|integer',
        'person_university' => 'sometimes|nullable|integer',
        'university_grad_year' => 'sometimes|nullable|string|max:50',
        'spiritual_father' => 'sometimes|nullable|string|max:255',
        'spiritual_father_church' => 'sometimes|nullable|string|max:255',

        'rotba_kashfeyya_id' => 'sometimes|nullable|integer',
        'betaka_id' => 'sometimes|nullable|integer',

        'personal_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:6144',
        'scout_image' => 'sometimes|nullable|image|mimes:jpg,jpeg,png,webp|max:6144',

        'questions' => 'sometimes|array',
        'questions.*' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return view('person.entry-error-repeat-trial');
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

        // PersonInformation
        $personInformationData = [];

        if ($request->exists('first_name')) $personInformationData['FirstName'] = $request->first_name;
        if ($request->exists('second_name')) $personInformationData['SecondName'] = $request->second_name;
        if ($request->exists('third_name')) $personInformationData['ThirdName'] = $request->third_name;
        if ($request->exists('fourth_name')) $personInformationData['FourthName'] = $request->fourth_name;
        if ($request->exists('gender')) $personInformationData['Gender'] = $request->gender;
        if ($request->exists('birthdate_input')) $personInformationData['DateOfBirth'] = $request->birthdate_input;
        if ($request->exists('input_raqam_qawmy')) $personInformationData['RaqamQawmy'] = $request->input_raqam_qawmy;
        if ($request->exists('joining_year_input')) $personInformationData['ScoutJoiningYear'] = $request->joining_year_input;
        if ($request->exists('blood_type_input')) $personInformationData['BloodTypeID'] = $request->blood_type_input;
        if ($request->exists('inputFacebookLink')) $personInformationData['FacebookProfileURL'] = $request->inputFacebookLink;
        if ($request->exists('inputInstagramLink')) $personInformationData['InstagramProfileURL'] = $request->inputInstagramLink;
        if ($request->exists('email_input')) $personInformationData['PersonalEmail'] = $request->email_input;
        if ($request->exists('RequestPersonID')) $personInformationData['RequestPersonID'] = $request->RequestPersonID;

        if (!empty($personInformationData)) {
            DB::table('PersonInformation')->where('PersonID', $id)->update($personInformationData);
        }

        // PersonPhoneNumbers
        $phoneData = [];
        if ($request->exists('personal_phone_number')) $phoneData['PersonPersonalMobileNumber'] = $request->personal_phone_number;
        if ($request->exists('father_phone_number')) $phoneData['FatherMobileNumber'] = $request->father_phone_number;
        if ($request->exists('mother_phone_number')) $phoneData['MotherMobileNumber'] = $request->mother_phone_number;
        if ($request->exists('home_phone_number')) $phoneData['HomePhoneNumber'] = $request->home_phone_number;
        if ($request->exists('has_whatsapp')) $phoneData['IsOPersonalPhoneNumberHavingWhatsapp'] = $request->has_whatsapp;

        if (!empty($phoneData)) {
            $exists = DB::table('PersonPhoneNumbers')->where('PersonID', $id)->exists();
            if ($exists) {
                DB::table('PersonPhoneNumbers')->where('PersonID', $id)->update($phoneData);
            } else {
                $phoneData['PersonID'] = $id;
                DB::table('PersonPhoneNumbers')->insert($phoneData);
            }
        }

        // PersonJob
        $jobData = [];
        if ($request->exists('person_job')) $jobData['JobName'] = $request->person_job;
        if ($request->exists('person_job_place')) $jobData['WorkPlace'] = $request->person_job_place;

        if (!empty($jobData)) {
            $exists = DB::table('PersonJob')->where('PersonID', $id)->exists();
            if ($exists) {
                DB::table('PersonJob')->where('PersonID', $id)->update($jobData);
            } else {
                $jobData['PersonID'] = $id;
                DB::table('PersonJob')->insert($jobData);
            }
        }

        // PersonLearningInformation
        $learningData = [];
        if ($request->exists('school_name')) $learningData['SchoolName'] = $request->school_name;
        if ($request->exists('school_grad_year')) $learningData['SchoolGraduationYear'] = $request->school_grad_year;
        if ($request->exists('person_faculty')) $learningData['FacultyID'] = $request->person_faculty;
        if ($request->exists('person_university')) $learningData['UniversityID'] = $request->person_university;
        if ($request->exists('university_grad_year')) $learningData['ActualFacultyGraduationYear'] = $request->university_grad_year;

        if (!empty($learningData)) {
            $exists = DB::table('PersonLearningInformation')->where('PersonID', $id)->exists();
            if ($exists) {
                DB::table('PersonLearningInformation')->where('PersonID', $id)->update($learningData);
            } else {
                $learningData['PersonID'] = $id;
                DB::table('PersonLearningInformation')->insert($learningData);
            }
        }

        // PersonRotbaKashfeyya
        if ($request->exists('rotba_kashfeyya_id')) {
            $exists = DB::table('PersonRotbaKashfeyya')->where('PersonID', $id)->exists();
            if ($exists) {
                DB::table('PersonRotbaKashfeyya')->where('PersonID', $id)->update([
                    'RotbaID' => $request->rotba_kashfeyya_id
                ]);
            } else {
                DB::table('PersonRotbaKashfeyya')->insert([
                    'PersonID' => $id,
                    'RotbaID' => $request->rotba_kashfeyya_id
                ]);
            }
        }

        // PersonEgazetBetakatTaqaddom
        if ($request->exists('betaka_id')) {
            $exists = DB::table('PersonEgazetBetakatTaqaddom')->where('PersonID', $id)->exists();
            if ($exists) {
                DB::table('PersonEgazetBetakatTaqaddom')->where('PersonID', $id)->update([
                    'EgazetBetakatTaqaddomID' => $request->betaka_id
                ]);
            } else {
                DB::table('PersonEgazetBetakatTaqaddom')->insert([
                    'PersonID' => $id,
                    'EgazetBetakatTaqaddomID' => $request->betaka_id
                ]);
            }
        }

        // PersonSanaMarhala
        if ($request->exists('sana_marhala_id')) {
            $exists = DB::table('PersonSanaMarhala')->where('PersonID', $id)->exists();
            if ($exists) {
                DB::table('PersonSanaMarhala')->where('PersonID', $id)->update([
                    'SanaMarhalaID' => $request->sana_marhala_id
                ]);
            } else {
                DB::table('PersonSanaMarhala')->insert([
                    'PersonID' => $id,
                    'SanaMarhalaID' => $request->sana_marhala_id
                ]);
            }
        }

        // PersonSpiritualFatherInformation
        $spiritualData = [];
        if ($request->exists('spiritual_father')) $spiritualData['SpiritualFatherName'] = $request->spiritual_father;
        if ($request->exists('spiritual_father_church')) $spiritualData['SpiritualFatherChurchName'] = $request->spiritual_father_church;

        if (!empty($spiritualData)) {
            $exists = DB::table('PersonSpiritualFatherInformation')->where('PersonID', $id)->exists();
            if ($exists) {
                DB::table('PersonSpiritualFatherInformation')->where('PersonID', $id)->update($spiritualData);
            } else {
                $spiritualData['PersonID'] = $id;
                DB::table('PersonSpiritualFatherInformation')->insert($spiritualData);
            }
        }

        // PersonalPhysicalAddress
        $addressData = [];
        if ($request->exists('building_number')) $addressData['BuildingNumber'] = $request->building_number;
        if ($request->exists('floor_number')) $addressData['FloorNumber'] = $request->floor_number;
        if ($request->exists('appartment_number')) $addressData['AppartmentNumber'] = $request->appartment_number;
        if ($request->exists('main_street_name')) $addressData['MainStreetName'] = $request->main_street_name;
        if ($request->exists('sub_street_name')) $addressData['SubStreetName'] = $request->sub_street_name;
        if ($request->exists('manteqa_id')) $addressData['ManteqaID'] = $request->manteqa_id;
        if ($request->exists('district_id')) $addressData['DistrictID'] = $request->district_id;
        if ($request->exists('nearest_landmark')) $addressData['NearestLandmark'] = $request->nearest_landmark;

        if (!empty($addressData)) {
            $exists = DB::table('PersonalPhysicalAddress')->where('PersonID', $id)->exists();
            if ($exists) {
                DB::table('PersonalPhysicalAddress')->where('PersonID', $id)->update($addressData);
            } else {
                $addressData['PersonID'] = $id;
                DB::table('PersonalPhysicalAddress')->insert($addressData);
            }
        }

        // PersonImages
        if ($request->hasFile('personal_image') || $request->hasFile('scout_image')) {
            $imageData = [
                'PersonSystemImagePath' => $personSystemImagePath,
                'ScoutOfficialUniformImagePath' => $scoutOfficialUniformImagePath,
            ];

            $exists = DB::table('PersonImages')->where('PersonID', $id)->exists();
            if ($exists) {
                DB::table('PersonImages')->where('PersonID', $id)->update($imageData);
            } else {
                $imageData['PersonID'] = $id;
                DB::table('PersonImages')->insert($imageData);
            }
        }

        // Questions
        if ($request->has('questions')) {
            foreach ($request->questions as $questionId => $answer) {
                $exists = DB::table('PersonEntryQuestions')
                    ->where('PersonID', $id)
                    ->where('QuestionID', $questionId)
                    ->exists();

                if ($exists) {
                    DB::table('PersonEntryQuestions')
                        ->where('PersonID', $id)
                        ->where('QuestionID', $questionId)
                        ->update([
                            'Answer' => $answer
                        ]);
                } else {
                    DB::table('PersonEntryQuestions')->insert([
                        'PersonID' => $id,
                        'QuestionID' => $questionId,
                        'Answer' => $answer
                    ]);
                }
            }
        }

        DB::commit();
        return redirect()->route('person.index');

    } catch (Exception $e) {
        DB::rollBack();
        dd($e->getMessage());
        return view('person.entry-error');
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