<?php

namespace App\Http\Controllers;

use App\Domain\Enrolment\LiveFormCapacityService;
use App\Domain\Enrolment\LiveFormDuplicateCheck;
use App\Domain\Enrolment\LiveFormFieldNormalizer;
use App\Domain\Enrolment\LiveFormQetaaResolver;
use App\Domain\Enrolment\LiveFormSubmitService;
use App\Domain\Enrolment\LiveFormTempFileService;
use App\Domain\Enrolment\LiveFormWizardService;
use App\Support\LookupCache;
use App\Support\SafeHttpUrl;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LiveFormEnrolmentController extends Controller
{
    public function __construct(
        private readonly LiveFormCapacityService $capacity,
        private readonly LiveFormSubmitService $submissions,
        private readonly LiveFormDuplicateCheck $duplicates,
        private readonly LiveFormQetaaResolver $qetaaResolver,
        private readonly LiveFormFieldNormalizer $fields,
        private readonly LiveFormTempFileService $tempFiles,
        private readonly LiveFormWizardService $wizard,
    ) {}

    public function createLiveForm()
    {
        try {
            $seneen_marahel = LookupCache::all('SanaMarhala');

            return view('person.person-create-liveform-1', [
                'seneen_marahel' => $seneen_marahel,
            ]);
        } catch (\Throwable $e) {
            return $this->formUnavailable($e);
        }
    }

    public function insertLiveForm(Request $request)
    {
        $request->validate([
            'sana_marhala_id' => 'required',
            'gender' => 'required|in:Male,Female',
            'newLeadersSchool' => 'nullable',
        ], [
            'sana_marhala_id.required' => __('Choose the academic year and stage.'),
            'gender.required' => __('Choose the applicant gender.'),
            'gender.in' => __('Choose male or female.'),
        ]);

        try {
        $sana_marhala_name = DB::table('SanaMarhala')
            ->where('SanaMarhalaID', $request->sana_marhala_id)
            ->value('SanaMarhalaName');

        $qetaat = $this->qetaaResolver->resolve(
            (int) $request->sana_marhala_id,
            $request->gender,
            (bool) $request->newLeadersSchool
        );

        if (empty($qetaat)) {
            return back()->withErrors([
                'sana_marhala_id' => __('No scout sector is available for this stage. Choose another stage, or contact the group.'),
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
                ->value('MaxLimit');

            $marhala_limit = $marhala_limit !== null ? (int) $marhala_limit : 0;

            $numberOfStudentsCurrentlySubmittedInSanaMarhala = DB::table('NewUsersInformation')
                ->where('QetaaID', $qetaa_id)
                ->where('SanaMarhalaID', $request->sana_marhala_id)
                ->count();

            // Advisory only — final capacity decision stays locked in LiveFormCapacityService at submit.
            $is_full = ($marhala_limit <= 0)
                || ($numberOfStudentsCurrentlySubmittedInSanaMarhala >= $marhala_limit);

            // Keep full qetaat visible (honest is_full) so step 2 can explain waiting-list risk.
            $available_qetaat[] = [
                'QetaaID' => $qetaa_id,
                'QetaaName' => $qetaa_name,
                'gender' => $gender,
                'current_count' => $numberOfStudentsCurrentlySubmittedInSanaMarhala,
                'max_limit' => $marhala_limit,
                'is_full' => $is_full,
            ];
        }
        // !! Note: We are allowing proceeding with empty available_qetaat to show a message in step 2 about no available sectors, instead of blocking here, because some stages might not have limits and we want to allow them to proceed to step 2 to show the available sectors without limits.
        // if (empty($available_qetaat)) {
        //     return view('person.liveform-limit-exceeded', [
        //         'qetaa_name' => null,
        //         'sana_marhala_name' => $sana_marhala_name,
        //         'current_count' => null,
        //         'max_limit' => null,
        //     ]);
        // }

        session([
            'liveform.step1' => [
                'sana_marhala_id' => (int) $request->sana_marhala_id,
                'sana_marhala_name' => $sana_marhala_name,
                'gender' => $request->gender,
                'newLeadersSchool' => (bool) $request->newLeadersSchool,
                'available_qetaat' => $available_qetaat,
                'qetaa_id' => count($available_qetaat) === 1 ? $available_qetaat[0]['QetaaID'] : null,
                'qetaa_name' => count($available_qetaat) === 1 ? $available_qetaat[0]['QetaaName'] : null,
            ],
        ]);

        return redirect()->route('person.liveform-step2');
        } catch (\Throwable $e) {
            return $this->backToStart($e);
        }
    }

    public function showLiveFormStep2()
    {
        $step1 = session('liveform.step1');

        if (! $step1) {
            return $this->sessionExpired();
        }

        try {
            return view('person.person-create-liveform', array_merge(
                $this->wizard->step2Lookups(),
                [
                    'sana_marhala_id' => $step1['sana_marhala_id'],
                    'sana_marhala_name' => $step1['sana_marhala_name'],
                    'qetaa_id' => $step1['qetaa_id'],
                    'qetaa_name' => $step1['qetaa_name'],
                    'available_qetaat' => $step1['available_qetaat'] ?? [],
                    'gender' => $step1['gender'],
                ]
            ));
        } catch (\Throwable $e) {
            return $this->formUnavailable($e);
        }
    }

    public function saveLiveFormStep2(Request $request)
    {
        $step1 = session('liveform.step1');

        if (! $step1) {
            return $this->sessionExpired();
        }

        $this->normalizeLiveFormInput($request);

        $rules = [
            'first_name' => 'required|string|max:255',
            'second_name' => 'required|string|max:255',
            'third_name' => 'required|string|max:255',
            'fourth_name' => 'nullable|string|max:255',
            'birthdate_input' => 'required|date|before_or_equal:today',
            'joining_year_input' => 'required',
            'input_raqam_qawmy' => 'required|digits:14',
            'blood_type_input' => 'required',
            'personal_phone_number' => 'required|digits:11',
            'father_phone_number' => 'nullable|digits:11',
            'mother_phone_number' => 'nullable|digits:11',
            'home_phone_number' => ['nullable', 'regex:/^\d{5,11}$/'],
            'building_number' => 'required',
            'floor_number' => 'required',
            'appartment_number' => 'required',
            'main_street_name' => 'nullable|string|max:255',
            'sub_street_name' => 'required|string|max:255',
            'nearest_landmark' => 'nullable|string|max:255',
            'manteqa_id' => 'required',
            'district_id' => 'required',
            'inputFacebookLink' => SafeHttpUrl::rules(500),
            'inputInstagramLink' => SafeHttpUrl::rules(500),
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
            'folar_id' => Schema::hasTable('Folar')
                ? 'nullable|integer|exists:Folar,FolarID'
                : 'nullable|integer',
        ];

        $validator = Validator::make($request->all(), $rules, $this->step2Messages());

        $hasEmergency = $request->boolean('has_emergency_case');

        $validator->sometimes('emergency_details', 'required|string|max:255', function () use ($hasEmergency) {
            return $hasEmergency === true;
        });

        $validator->after(function ($validator) use ($request) {
            foreach (['first_name', 'second_name', 'third_name', 'fourth_name'] as $nameField) {
                $name = trim((string) $request->input($nameField));
                if ($name !== '' && preg_match('/^[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\s]+$/u', $name) !== 1) {
                    $validator->errors()->add($nameField, __('Please enter Arabic letters only'));
                }
            }

            $birthDate = (string) $request->birthdate_input;
            $nid = (string) $request->input_raqam_qawmy;

            if (strlen($nid) === 14 && strlen($birthDate) >= 10) {
                $fullYear = (int) substr($birthDate, 0, 4);
                $year = substr($birthDate, 2, 2);
                $month = substr($birthDate, 5, 2);
                $day = substr($birthDate, 8, 2);

                $expected = substr($nid, 1, 2).substr($nid, 3, 2).substr($nid, 5, 2);
                $actual = $year.$month.$day;
                $expectedCentury = match (true) {
                    $fullYear >= 1900 && $fullYear <= 1999 => '2',
                    $fullYear >= 2000 && $fullYear <= 2099 => '3',
                    default => null,
                };

                if ($expectedCentury === null || $nid[0] !== $expectedCentury || $expected !== $actual) {
                    $validator->errors()->add('input_raqam_qawmy', __('The national ID does not match the date of birth. Check both.'));
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        $data = $this->fields->normalizeArabicFields($data, [
            'first_name',
            'second_name',
            'third_name',
            'fourth_name',
        ]);

        $selectedQetaa = collect($step1['available_qetaat'] ?? [])
            ->firstWhere('QetaaID', (int) $request->qetaa_id);

        if (! $selectedQetaa) {
            return redirect()->back()
                ->withErrors(['qetaa_id' => __('Choose a scout sector from the list.')])
                ->withInput();
        }

        $step1['qetaa_id'] = $selectedQetaa['QetaaID'];
        $step1['qetaa_name'] = $selectedQetaa['QetaaName'];

        session(['liveform.step1' => $step1]);

        try {
            $raqam = (string) $request->input('input_raqam_qawmy');

            if ($this->duplicates->exists($raqam)) {
                return redirect()->back()
                    ->withErrors(['input_raqam_qawmy' => __('This national ID is already registered. Check the digits, or contact the group if this person is already enrolled.')])
                    ->withInput();
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
                'allergy_food' => $this->fields->cleanList($request->allergy_food),
                'allergy_medicine' => $this->fields->cleanList($request->allergy_medicine),
                'medical_diseases' => $this->fields->cleanList($request->medical_diseases),
                'medical_medications' => $this->fields->cleanList($request->medical_medications),
                'has_emergency_case' => $hasEmergency && $this->fields->cleanList($request->emergency_details) !== null ? 1 : 0,
                'emergency_details' => $this->fields->cleanList($request->emergency_details),
                'folar_id' => $data['folar_id'] ?? null,

                'profile_image' => $profileImagePath,
                'scout_uniform_image' => $scoutImagePath,
            ],
        ]);

        return redirect()->route('person.entry-questions-liveform');
        } catch (\Throwable $e) {
            return $this->backToStep2($e);
        }
    }

    public function getLiveformQuestions()
    {
        $step1 = session('liveform.step1');
        $step2 = session('liveform.step2');

        if (! $step1 || ! $step2) {
            return $this->sessionExpired();
        }

        try {
            $questions = DB::table('MarhalaEntryQuestions')
                ->where('QetaaID', $step1['qetaa_id'])
                ->where('NotToBeShown', 0)
                ->orderBy('QuestionID')
                ->get();
        } catch (\Throwable $e) {
            return $this->formUnavailable($e);
        }

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
        ]);
    }

    public function submitLiveformQuestions(Request $request)
    {
        $step1 = session('liveform.step1');
        $step2 = session('liveform.step2');

        if (! $step1 || ! $step2) {
            return $this->sessionExpired();
        }

        try {
            $questions = DB::table('MarhalaEntryQuestions')
                ->where('QetaaID', $step1['qetaa_id'])
                ->where('NotToBeShown', 0)
                ->get();
        } catch (\Throwable $e) {
            return $this->formUnavailable($e);
        }

        $missing = [];

        foreach ($questions as $question) {
            $answer = $request->input($question->QuestionID);

            if ($question->IsRequired && ($answer === null || $answer === '')) {
                $missing[(string) $question->QuestionID] = __('Answer this question: :question', [
                    'question' => $question->QuestionText,
                ]);
            }
        }

        if ($missing !== []) {
            return redirect()
                ->route('person.entry-questions-liveform')
                ->withErrors($missing)
                ->withInput();
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | CHECK DUPLICATE
            |--------------------------------------------------------------------------
            */

            if ($this->duplicates->exists((string) $step2['input_raqam_qawmy'], lockForUpdate: true)) {
                DB::rollBack();

                return $this->nationalIdTaken($step2);
            }

            /*
            |--------------------------------------------------------------------------
            | PASSWORD
            |--------------------------------------------------------------------------
            */

            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

            $pass = [];

            $alphaLength = strlen($alphabet) - 1;

            for ($i = 0; $i < 8; $i++) {
                $pass[] = $alphabet[rand(0, $alphaLength)];
            }

            $passString = implode($pass);

            /*
            |--------------------------------------------------------------------------
            | IMAGES
            |--------------------------------------------------------------------------
            */

            $personalImagePath = $this->tempFiles->finalizeTempFile(
                $step2['profile_image'] ?? null
            );

            $scoutImagePath = $this->tempFiles->finalizeTempFile(
                $step2['scout_uniform_image'] ?? null
            );

            /*
            |--------------------------------------------------------------------------
            | DATA ARRAY
            |--------------------------------------------------------------------------
            */

            $personData = [
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
                'DistrictID' => is_null($step2['district_id'])
                    ? 1
                    : $step2['district_id'],
                'NearestLandmark' => $step2['nearest_landmark'],
                'SanaMarhalaID' => $step1['sana_marhala_id'],
                'SpiritualFatherName' => $step2['spiritual_father'],
                'SpiritualFatherChurchName' => $step2['spiritual_father_church'],
                'Password' => Hash::make($passString),
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
                'AllergyFood' => $this->fields->cleanList($step2['allergy_food'] ?? null),
                'AllergyMedicine' => $this->fields->cleanList($step2['allergy_medicine'] ?? null),
                'MedicalDiseases' => $this->fields->cleanList($step2['medical_diseases'] ?? null),
                'MedicalMedications' => $this->fields->cleanList($step2['medical_medications'] ?? null),
                'HasEmergencyCase' => ! empty($step2['has_emergency_case'])
                    && $this->fields->cleanList($step2['emergency_details'] ?? null) !== null ? 1 : 0,
                'EmergencyDetails' => ! empty($step2['has_emergency_case'])
                    ? $this->fields->cleanList($step2['emergency_details'] ?? null)
                    : null,
                'FolarID' => ! empty($step2['folar_id']) ? (int) $step2['folar_id'] : null,
            ];

            $answers = [];
            foreach ($questions as $question) {
                $answers[$question->QuestionID] = $request->input($question->QuestionID);
            }

            $result = $this->submissions->persistSubmission(
                $personData,
                (int) $step1['qetaa_id'],
                (int) $step1['sana_marhala_id'],
                $questions,
                $answers,
            );

            DB::commit();

            session()->forget('liveform');

            if ($result['is_waiting_list']) {
                return view('person.liveform-waiting-list');
            }

            return view('person.liveform-finalize');

        } catch (QueryException $e) {
            DB::rollBack();

            if ($this->duplicates->isUniqueViolation($e)) {
                return $this->nationalIdTaken($step2);
            }

            return $this->backToQuestions($e);
        } catch (\Throwable $e) {

            DB::rollBack();

            return $this->backToQuestions($e);
        }
    }

    /**
     * @param  array<string, mixed>  $step2
     */
    private function nationalIdTaken(array $step2)
    {
        return redirect()
            ->route('person.liveform-step2')
            ->withInput($step2)
            ->withErrors([
                'input_raqam_qawmy' => __('This national ID is already registered. Check the digits, or contact the group if this person is already enrolled.'),
            ]);
    }

    private function backToQuestions(\Throwable $e)
    {
        Log::error('submitLiveformQuestions failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return redirect()
            ->route('person.entry-questions-liveform')
            ->withInput(request()->except('_token'))
            ->withErrors([
                'form' => __('Something went wrong while saving. Your answers are still here. Please try again.'),
            ]);
    }

    private function backToStart(\Throwable $e)
    {
        Log::error('liveform step failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return redirect()
            ->route('person.liveform-create')
            ->withInput(request()->except('_token'))
            ->withErrors([
                'form' => __('Something went wrong while saving. Your answers are still here. Please try again.'),
            ]);
    }

    private function backToStep2(\Throwable $e)
    {
        Log::error('liveform step failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return redirect()
            ->route('person.liveform-step2')
            ->withInput(request()->except(['_token', 'profile_image', 'scout_uniform_image']))
            ->withErrors([
                'form' => __('Something went wrong while saving. Your answers are still here. Please choose the photos again and try again.'),
            ]);
    }

    private function sessionExpired()
    {
        return redirect()
            ->route('person.liveform-create')
            ->with('status', __('The page was open too long. Continue from here. Your saved answers will come back.'));
    }

    private function formUnavailable(\Throwable $e)
    {
        Log::error('liveform page failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->view('person.liveform-unavailable', [], 503);
    }

    private function normalizeLiveFormInput(Request $request): void
    {
        $merged = [];

        foreach ([
            'input_raqam_qawmy',
            'personal_phone_number',
            'father_phone_number',
            'mother_phone_number',
            'home_phone_number',
        ] as $key) {
            if (! $request->exists($key)) {
                continue;
            }

            $merged[$key] = $this->westernDigits($request->input($key));
        }

        foreach (['inputFacebookLink', 'inputInstagramLink'] as $key) {
            $raw = trim((string) $request->input($key, ''));
            if ($raw !== '' && preg_match('#^[a-z][a-z0-9+.-]*://#i', $raw) !== 1) {
                $merged[$key] = 'https://'.ltrim($raw, '/');
            }
        }

        if ($merged !== []) {
            $request->merge($merged);
        }
    }

    private function westernDigits(mixed $value): ?string
    {
        $value = strtr((string) $value, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        return $digits === '' ? null : $digits;
    }

    /**
     * @return array<string, string>
     */
    private function step2Messages(): array
    {
        $long = __('This value is too long. Shorten it and try again.');
        $photo = __('Image must be JPG/PNG/WebP and under 5 MB');
        $mobile = __('The mobile number must be 11 digits.');

        return [
            'first_name.required' => __('Enter the first name in Arabic.'),
            'second_name.required' => __('Enter the second name in Arabic.'),
            'third_name.required' => __('Enter the third name in Arabic.'),
            'first_name.max' => $long,
            'second_name.max' => $long,
            'third_name.max' => $long,
            'fourth_name.max' => $long,
            'birthdate_input.required' => __('Enter the date of birth.'),
            'birthdate_input.date' => __('The date of birth is not a valid date.'),
            'birthdate_input.before_or_equal' => __('The date of birth cannot be in the future.'),
            'joining_year_input.required' => __('Choose the joining year.'),
            'input_raqam_qawmy.required' => __('Enter the national ID.'),
            'input_raqam_qawmy.digits' => __('The national ID must be 14 digits.'),
            'blood_type_input.required' => __('Choose the blood type. Choose Unspecified if you are not sure.'),
            'personal_phone_number.required' => __('Enter the applicant mobile number.'),
            'personal_phone_number.digits' => $mobile,
            'father_phone_number.digits' => $mobile,
            'mother_phone_number.digits' => $mobile,
            'home_phone_number.regex' => __('Enter the landline as digits only, from 5 to 11 digits.'),
            'building_number.required' => __('Enter the building number.'),
            'floor_number.required' => __('Enter the floor number.'),
            'appartment_number.required' => __('Enter the apartment number.'),
            'sub_street_name.required' => __('Enter the street name.'),
            'manteqa_id.required' => __('Choose the area.'),
            'district_id.required' => __('Choose the district.'),
            'email_input.email' => __('Invalid email address'),
            'qetaa_id.required' => __('Choose a scout sector from the list.'),
            'emergency_details.required' => __('Write the emergency details, because you answered yes.'),
            'profile_image.image' => $photo,
            'profile_image.mimes' => $photo,
            'profile_image.max' => $photo,
            'scout_uniform_image.image' => $photo,
            'scout_uniform_image.mimes' => $photo,
            'scout_uniform_image.max' => $photo,
            'folar_id.exists' => __('Choose a scarf from the list, or leave it empty.'),
            'folar_id.integer' => __('Choose a scarf from the list, or leave it empty.'),
        ];
    }
}
