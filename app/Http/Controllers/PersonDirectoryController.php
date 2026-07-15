<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Domain\Person\PersonSearchService;
use App\Support\LikeSearch;
use App\Support\ShamandoraCode;
use Throwable;

class PersonDirectoryController extends Controller
{

       public function index(Request $request, PersonSearchService $personSearch)
            {
                $userId = Auth::id();
                Log::info("Fetching persons for user ID: " . $userId);

                $term = LikeSearch::fromRequest($request);
                $persons = $personSearch->paginateScopedToPerson((int) $userId, $term);

                return view("person.person-index", ['persons' => $persons, 'q' => $term ?? '']);
                        }


public function ShowPersons(Request $request, PersonSearchService $personSearch)
{
    $term = LikeSearch::fromRequest($request);
    $persons = $personSearch->paginateAllPersons($term);

    return view("person.person-showAllPersons", ['persons' => $persons, 'q' => $term ?? '']);
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

public function updates(Request $request, $id, \App\Domain\Person\PersonProfileService $profiles)
{
    if (!$profiles->exists((int) $id)) {
        return view('person.entry-error');
    }

    if ($request->filled('input_raqam_qawmy') && $profiles->raqamQawmyTaken((string) $request->input_raqam_qawmy, (int) $id)) {
        return view('person.person-already-exists');
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
        $profiles->updateProfile(
            (int) $id,
            $request->all(),
            $request->file('personal_image'),
            $request->file('scout_image'),
            $request->input('questions'),
            $request->exists('questions'),
        );

        return redirect()->route('person.edit', $id)->with('status', 'تم تعديل البيانات بنجاح');
    } catch (\Exception $e) {
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









public function searchPerson(Request $request, PersonSearchService $personSearch)
{
    $term = LikeSearch::fromRequest($request);

    return response()->json($personSearch->typeaheadByNameOrIdentity($term));
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

    /**
     * Admin direct person create (PersonInformation) — not new-enrolment liveform.
     */
    public function create()
    {
        return view('person.person-create', [
            'marahel' => DB::table('Marhala')->get(),
            'rotab' => DB::table('RotbaInformation')->get(),
            'seneen_marahel' => DB::table('SanaMarhala')->get(),
            'questionTypes' => DB::table('QuestionsTypes')->get(),
            'blood' => DB::table('BloodType')->get(),
            'betakat' => DB::table('EgazetBetakatTaqaddom')->get(),
            'manateq' => DB::table('Manteqa')->get(),
            'districts' => DB::table('Districts')->get(),
            'qetaat' => DB::table('Qetaa')->get(),
            'faculties' => DB::table('Faculty')->get(),
            'universities' => DB::table('University')->get(),
        ]);
    }

    public function insert(Request $request)
    {
        $exists = DB::table('PersonInformation')
            ->where('RaqamQawmy', $request->input_raqam_qawmy)
            ->exists();

        if ($exists) {
            return view('person.person-already-exists');
        }

        $validated = $request->validate([
            'first_name' => 'required',
            'second_name' => 'required',
            'third_name' => 'required',
            'fourth_name' => 'nullable',
            'gender' => 'required',
            'birthdate_input' => 'required',
            'joining_year_input' => 'required',
            'input_raqam_qawmy' => 'required|digits:14',
            'blood_type_input' => 'required',
            'personal_phone_number' => 'required|digits:11',
            'building_number' => 'required',
            'floor_number' => 'required',
            'appartment_number' => 'required',
            'sub_street_name' => 'required',
            'manteqa_id' => 'required',
            'district_id' => 'required',
            'sana_marhala_id' => 'required',
            'qetaa_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $thisPersonID = (int) DB::table('PersonInformation')->insertGetId([
                'ShamandoraCode' => bin2hex(random_bytes(5)),
                'FirstName' => $request->first_name,
                'SecondName' => $request->second_name,
                'ThirdName' => $request->third_name,
                'FourthName' => $request->fourth_name,
                'Gender' => $request->gender,
                'DateOfBirth' => $request->birthdate_input,
                'RaqamQawmy' => $request->input_raqam_qawmy,
                'ScoutJoiningYear' => $request->joining_year_input,
                'BloodTypeID' => $request->blood_type_input,
                'FacebookProfileURL' => $request->inputFacebookLink,
                'InstagramProfileURL' => $request->inputInstagramLink,
                'PersonalEmail' => $request->email_input,
                'RequestPersonID' => $request->RequestPersonID ?? Auth::id(),
            ], 'PersonID');

            DB::table('PersonInformation')->where('PersonID', $thisPersonID)->update([
                'ShamandoraCode' => ShamandoraCode::forPersonId($thisPersonID),
            ]);

            DB::table('PersonPhoneNumbers')->insert([
                'PersonID' => $thisPersonID,
                'PersonPersonalMobileNumber' => $request->personal_phone_number,
                'FatherMobileNumber' => $request->father_phone_number,
                'MotherMobileNumber' => $request->mother_phone_number,
                'HomePhoneNumber' => $request->home_phone_number,
                'IsOPersonalPhoneNumberHavingWhatsapp' => $request->has_whatsapp,
            ]);

            DB::table('PersonJob')->insert([
                'PersonID' => $thisPersonID,
                'JobName' => $request->person_job,
                'WorkPlace' => $request->person_job_place,
            ]);

            DB::table('PersonLearningInformation')->insert([
                'PersonID' => $thisPersonID,
                'SchoolName' => $request->school_name ?? $request->person_school,
                'SchoolGraduationYear' => $request->school_grad_year,
                'FacultyID' => $request->person_faculty,
                'UniversityID' => $request->person_university,
                'ActualFacultyGraduationYear' => $request->university_grad_year,
            ]);

            if ($request->filled('rotba_kashfeyya_id')) {
                DB::table('PersonRotbaKashfeyya')->insert([
                    'PersonID' => $thisPersonID,
                    'RotbaID' => $request->rotba_kashfeyya_id,
                ]);
            }

            DB::table('PersonQetaa')->insert([
                'PersonID' => $thisPersonID,
                'QetaaID' => $request->qetaa_id,
            ]);

            if ($request->filled('betaka_id')) {
                DB::table('PersonEgazetBetakatTaqaddom')->insert([
                    'PersonID' => $thisPersonID,
                    'EgazetBetakatTaqaddomID' => $request->betaka_id,
                ]);
            }

            DB::table('PersonSanaMarhala')->insert([
                'PersonID' => $thisPersonID,
                'SanaMarhalaID' => $request->sana_marhala_id,
            ]);

            DB::table('PersonSpiritualFatherInformation')->insert([
                'PersonID' => $thisPersonID,
                'SpiritualFatherName' => $request->spiritual_father,
                'SpiritualFatherChurchName' => $request->spiritual_father_church,
            ]);

            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $pass = '';
            for ($i = 0; $i < 8; $i++) {
                $pass .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            DB::table('PersonSystemPassword')->insert([
                'PersonID' => $thisPersonID,
                'Password' => Hash::make($pass),
            ]);

            DB::table('PersonalPhysicalAddress')->insert([
                'PersonID' => $thisPersonID,
                'BuildingNumber' => $request->building_number,
                'FloorNumber' => $request->floor_number,
                'AppartmentNumber' => $request->appartment_number,
                'MainStreetName' => $request->main_street_name,
                'SubStreetName' => $request->sub_street_name,
                'ManteqaID' => $request->manteqa_id,
                'DistrictID' => $request->district_id ?: 1,
                'NearestLandmark' => $request->nearest_landmark,
            ]);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('person.insert failed', ['message' => $e->getMessage()]);

            return view('person.entry-error');
        }

        return redirect()
            ->route('person.entry-questions', $thisPersonID)
            ->with('status', 'تم إنشاء الشخص. كلمة المرور المؤقتة: '.$pass);
    }

    public function getQuestions($id)
    {
        $person = DB::table('PersonInformation')
            ->where('PersonInformation.PersonID', $id)
            ->join('PersonQetaa', 'PersonInformation.PersonID', '=', 'PersonQetaa.PersonID')
            ->join('Qetaa', 'PersonQetaa.QetaaID', '=', 'Qetaa.QetaaID')
            ->leftJoin('PersonSystemPassword', 'PersonInformation.PersonID', '=', 'PersonSystemPassword.PersonID')
            ->select(
                'PersonInformation.*',
                'PersonSystemPassword.Password',
                'PersonQetaa.QetaaID',
                'Qetaa.QetaaName'
            )
            ->first();

        if (! $person) {
            return view('person.entry-error');
        }

        $questions = DB::table('MarhalaEntryQuestions')
            ->where('QetaaID', $person->QetaaID)
            ->where('NotToBeShown', '=', 0)
            ->get();

        return view('person.person-questions', [
            'questions' => $questions,
            'person' => $person,
        ]);
    }

    public function submitQuestions(Request $request)
    {
        $person = DB::table('PersonInformation')
            ->where('PersonInformation.PersonID', $request->person_id)
            ->join('PersonQetaa', 'PersonInformation.PersonID', '=', 'PersonQetaa.PersonID')
            ->join('Qetaa', 'PersonQetaa.QetaaID', '=', 'Qetaa.QetaaID')
            ->select('PersonInformation.PersonID', 'PersonQetaa.QetaaID')
            ->first();

        if (! $person) {
            return view('person.entry-error');
        }

        $questions = DB::table('MarhalaEntryQuestions')
            ->where('QetaaID', $person->QetaaID)
            ->where('NotToBeShown', '=', 0)
            ->get();

        try {
            DB::beginTransaction();

            foreach ($questions as $question) {
                $answer = $request->input((string) $question->QuestionID);
                if ($question->IsRequired && ($answer === null || $answer === '')) {
                    DB::rollBack();

                    return view('person.entry-error-repeat-trial');
                }
                DB::table('PersonEntryQuestions')->insert([
                    'PersonID' => $request->person_id,
                    'QuestionID' => $question->QuestionID,
                    'Answer' => $answer,
                ]);
            }

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('person.submitQuestions failed', ['message' => $e->getMessage()]);

            return view('person.entry-error');
        }

        return redirect()->route('person.index');
    }

}
