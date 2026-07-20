<?php

namespace App\Http\Controllers;

use App\Domain\Person\PersonProfileService;
use App\Domain\Person\PersonSeasonActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class PersonProfileController extends Controller
{
    public function show(Request $request, PersonSeasonActivityService $seasonActivity)
    {
        $user = Auth::user();
        $personId = (int) $user->PersonID;

        $person = $this->loadPerson($personId);
        $seasons = $seasonActivity->seasons();
        $selectedSeasonId = $seasonActivity->resolveSeasonId(
            $request->integer('season') ?: null
        );
        $seasonData = $seasonActivity->forPerson($personId, $selectedSeasonId);
        $custodyCount = DB::table('CustodyRequests')->where('PersonID', $personId)->count();
        $bookingCount = DB::table('PlaceBookings')->where('PersonID', $personId)->count();

        return view('profile', [
            'user' => $user,
            'person' => $person,
            'seasons' => $seasons,
            'selectedSeasonId' => $selectedSeasonId,
            'seasonActivity' => $seasonData,
            'attendance' => $seasonData['attendance'],
            'custodyCount' => $custodyCount,
            'bookingCount' => $bookingCount,
        ]);
    }

    public function edit()
    {
        $user = Auth::user();
        $personId = (int) $user->PersonID;

        return view('profile-edit', [
            'user' => $user,
            'person' => $this->loadPerson($personId),
            'blood' => DB::table('BloodType')->orderBy('BloodTypeName')->get(),
            'rotab' => DB::table('RotbaInformation')->orderBy('RotbaName')->get(),
            'betakat' => DB::table('EgazetBetakatTaqaddom')->orderBy('EgazetBetakatTaqaddomName')->get(),
            'seneen_marahel' => DB::table('SanaMarhala')->orderBy('SanaMarhalaName')->get(),
            'manateq' => DB::table('Manteqa')->orderBy('ManteqaName')->get(),
            'districts' => DB::table('Districts')->orderBy('DistrictName')->get(),
            'faculties' => DB::table('Faculty')->orderBy('FacultyName')->get(),
            'universities' => DB::table('University')->orderBy('UniversityName')->get(),
        ]);
    }

    public function update(Request $request, PersonProfileService $profiles)
    {
        $user = Auth::user();
        $personId = (int) $user->PersonID;

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'second_name' => 'required|string|max:255',
            'third_name' => 'nullable|string|max:255',
            'fourth_name' => 'nullable|string|max:255',
            'gender' => 'nullable|in:Male,Female',
            'birthdate_input' => 'nullable|date',
            'joining_year_input' => 'nullable|integer|min:1950|max:2100',
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Explicitly strip identity fields — ShamandoraCode & RaqamQawmy stay locked.
        $data = collect($request->all())
            ->except(['input_raqam_qawmy', 'RaqamQawmy', 'ShamandoraCode', 'PersonID'])
            ->all();

        $profiles->updateProfile(
            $personId,
            $data,
            $request->file('personal_image'),
            $request->file('scout_image'),
        );

        return Redirect::route('profile.show')->with('success', 'تم تحديث الملف الشخصي بنجاح.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        DB::table('PersonSystemPassword')->updateOrInsert(
            ['PersonID' => $user->PersonID],
            ['Password' => Hash::make($request->input('password'))]
        );

        return Redirect::route('profile.show')->with('success', 'تم تحديث كلمة المرور بنجاح.');
    }

    private function loadPerson(int $personId): ?object
    {
        return DB::table('PersonInformation')
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
            ->where('PersonInformation.PersonID', $personId)
            ->select(
                'PersonInformation.*',
                'PersonImages.PersonSystemImagePath as PersonalImagePath',
                'PersonImages.ScoutOfficialUniformImagePath as ScoutImagePath',
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
                'Districts.DistrictName'
            )
            ->first();
    }
}
