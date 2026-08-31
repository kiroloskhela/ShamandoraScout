<?php

namespace App\Http\Controllers;

use App\Support\LikeSearch;
use App\Support\LookupCache;
use App\Support\SqlPaginator;
use App\Support\TableColumnFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NewEnrolmentAdminController extends Controller
{
    private function newUsersSearchColumns(): array
    {
        return [
            'nui.FirstName',
            'nui.SecondName',
            'nui.ThirdName',
            'nui.FourthName',
            'nui.QetaaName',
            'sm.SanaMarhalaName',
            'nui.RaqamQawmy',
            'nui.PersonPersonalMobileNumber',
            'CAST(nui.PersonID AS CHAR)',
        ];
    }

    private function paginateNewUsers(Request $request, string $extraWhere = '', array $extraBindings = [], string $view = 'person.new-enrolments-index')
    {
        $bindings = $extraBindings;
        $whereParts = [];

        if ($extraWhere !== '') {
            $whereParts[] = '('.$extraWhere.')';
        }

        $term = LikeSearch::fromRequest($request);
        if ($term !== null) {
            $fragment = LikeSearch::sqlOr($this->newUsersSearchColumns(), $term);
            $whereParts[] = $fragment['sql'];
            $bindings = array_merge($bindings, $fragment['bindings']);
        }

        $filters = TableColumnFilters::fromRequest($request, ['QetaaName', 'SanaMarhalaName']);
        $filterFrag = TableColumnFilters::sqlEquals($filters, [
            'QetaaName' => 'nui.QetaaName',
            'SanaMarhalaName' => 'sm.SanaMarhalaName',
        ]);
        if ($filterFrag['sql'] !== '') {
            $whereParts[] = $filterFrag['sql'];
            $bindings = array_merge($bindings, $filterFrag['bindings']);
        }

        $whereSql = $whereParts === [] ? '' : (' WHERE '.implode(' AND ', $whereParts));

        $sql = "SELECT DISTINCT nui.PersonID,
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
                                                    CASE WHEN EXISTS (
                                                        SELECT 1 FROM NewUsersPersonEntryQuestions nupq
                                                        WHERE nupq.PersonID = nui.PersonID
                                                    ) THEN 'نعم' ELSE 'لا' END AS HasAnsweredQuestions,
                                                    DATE_FORMAT(nui.CreatedAt, '%Y-%m-%d %H:%i') AS CreatedAt
                                                FROM NewUsersInformation nui
                                                LEFT JOIN SanaMarhala sm ON nui.SanaMarhalaID = sm.SanaMarhalaID
                                                {$whereSql}
                                                ORDER BY nui.CreatedAt DESC, nui.PersonID DESC";

        $persons = SqlPaginator::paginate($sql, $bindings, 25);

        return view($view, [
            'persons' => $persons,
            'filterOptions' => $this->enrolmentFilterOptions(),
            'activeServerFilters' => $filters,
        ]);
    }

    /**
     * @return array{SanaMarhalaName: list<string>, QetaaName: list<string>}
     */
    private function enrolmentFilterOptions(): array
    {
        $stages = DB::table('SanaMarhala')
            ->whereNotNull('SanaMarhalaName')
            ->where('SanaMarhalaName', '<>', '')
            ->orderBy('SanaMarhalaName')
            ->pluck('SanaMarhalaName');

        $sectors = DB::table('NewUsersInformation')
            ->whereNotNull('QetaaName')
            ->where('QetaaName', '<>', '')
            ->distinct()
            ->orderBy('QetaaName')
            ->pluck('QetaaName');

        return [
            'SanaMarhalaName' => $stages->map(fn ($v) => (string) $v)->values()->all(),
            'QetaaName' => $sectors->map(fn ($v) => (string) $v)->values()->all(),
        ];
    }

    public function indexNewEnrolmentsAndMigrations()
    {
        $this->authorize('enrolment.migrate');

        $qetaas = LookupCache::ordered('Qetaa', 'QetaaID');

        $approvedByQetaa = DB::table('NewUsersInformation')
            ->where('IsApproved', 1)
            ->select('QetaaID', DB::raw('COUNT(*) as approved_count'))
            ->groupBy('QetaaID')
            ->pluck('approved_count', 'QetaaID');

        $totalApproved = (int) $approvedByQetaa->sum();

        $qetaas = $qetaas->map(function ($qetaa) use ($approvedByQetaa) {
            $qetaa->approved_count = (int) ($approvedByQetaa[$qetaa->QetaaID] ?? 0);

            return $qetaa;
        });

        return view('person.new-enrolments-migrate-index', [
            'qetaas' => $qetaas,
            'totalApproved' => $totalApproved,
        ]);
    }

    public function indexNewEnrolments(Request $request)
    {
        $this->authorize('enrolment.viewAny');

        return $this->paginateNewUsers($request, '', [], 'person.new-enrolments-index');
    }

    public function showNewEnrolmentsByQetaaID(Request $request, $id)
    {
        return $this->paginateNewUsers($request, 'nui.QetaaID = ?', [$id], 'person.new-enrolments-index');
    }

    public function analyticsNewEnrolments()
    {
        $analytics = DB::select('SELECT NewUsersInformation.QetaaID,
                                            NewUsersInformation.QetaaName,
                                            COUNT(*) AS CountOfRequests,
                                            COUNT(IF(NewUsersInformation.IsApproved = 1, 1, NULL)) AS CountOfApprovedRequests
                                    FROM NewUsersInformation
                                    LEFT JOIN SanaMarhala ON SanaMarhala.SanaMarhalaID = NewUsersInformation.SanaMarhalaID
                                    GROUP BY NewUsersInformation.QetaaID
                                    ORDER BY NewUsersInformation.QetaaID ASC');

        // return $analytics;
        return view('person.new-enrolments-analytics', ['analytics' => $analytics]);
    }

    public function showNewEnrolments($id)
    {
        $this->authorize('enrolment.view', (int) $id);

        $person = DB::table('NewUsersInformation')->where('PersonID', $id)
            ->leftJoin('BloodType', 'BloodType.BloodTypeID', '=', 'NewUsersInformation.BloodTypeID')
            ->leftJoin('Qetaa', 'Qetaa.QetaaID', '=', 'NewUsersInformation.QetaaID')
            ->leftJoin('SanaMarhala', 'SanaMarhala.SanaMarhalaID', '=', 'NewUsersInformation.SanaMarhalaID')
            ->leftJoin('Manteqa', 'Manteqa.ManteqaID', '=', 'NewUsersInformation.ManteqaID')
            ->leftJoin('Districts', 'Districts.DistrictID', '=', 'NewUsersInformation.DistrictID')
            ->get()->first();

        $questions = DB::table('NewUsersPersonEntryQuestions')
            ->join('MarhalaEntryQuestions', 'MarhalaEntryQuestions.QuestionID', '=', 'NewUsersPersonEntryQuestions.QuestionID')
            ->select('MarhalaEntryQuestions.QuestionText', 'NewUsersPersonEntryQuestions.Answer')
            ->where('NewUsersPersonEntryQuestions.PersonID', $id)->get();

        // return $person->PersonID;
        return view('person.new-enrolments-show', ['person' => $person, 'questions' => $questions]);
    }

    public function deleteNewEnrolments($id)
    {
        $this->authorize('enrolment.delete', (int) $id);

        $person = DB::table('NewUsersInformation')->where('PersonID', '=', $id)->select('NewUsersInformation.PersonID', 'NewUsersInformation.ShamandoraCode', DB::raw("CONCAT(FirstName, ' ', SecondName, ' ', ThirdName, ' ', FourthName) as FullName"))->first();

        return view('person.new-enrolments-delete', ['person' => $person]);
    }

    public function destroyNewEnrolments($id)
    {
        $this->authorize('enrolment.delete', (int) $id);

        $person = DB::table('NewUsersInformation')->where('PersonID', '=', $id)->select('NewUsersInformation.PersonID', 'NewUsersInformation.QetaaID')->first();

        DB::beginTransaction();

        DB::table('NewUsersInformation')->where('PersonID', $id)->delete();
        DB::table('NewUsersPersonEntryQuestions')->where('PersonID', $id)->delete();

        DB::commit();

        return redirect()->route('person.new-enrolments-index');
    }

    public function approveNewEnrolments($id)
    {
        $this->authorize('enrolment.approve', (int) $id);

        DB::table('NewUsersInformation')
            ->where('PersonID', $id)
            ->update(['IsApproved' => 1]);

        return redirect()->back()->with('success', __('Approved successfully'));
    }

    public function approveAgainNewEnrolments($id)
    {
        $this->authorize('enrolment.approve', (int) $id);

        DB::table('NewUsersInformation')
            ->where('PersonID', $id)
            ->update(['IsApproved' => 1]);

        return redirect()->route('person.new-enrolments-index')
            ->with('success', __('Re-approved successfully'));
    }

    public function countNewEnrolmentsMarahel()
    {
        $marahel = [];
        $counts = [];
        for ($i = 3; $i < 22; $i++) {
            $counts[$i] = DB::table('NewUsersInformation')->where('SanaMarhalaID', $i)->count();
        }

        return view('person.new-enrolments-marahel-count', [
            'marahel' => $marahel,
            'counts' => $counts,
        ]);
    }

    public function countNewEnrolmentsQetaat()
    {
        $counts = [];
        $qetaat = [];
        for ($i = 1; $i < 10; $i++) {
            $counts[$i] = DB::table('NewUsersInformation')->where('QetaaID', $i)->count();
            $qetaat[$i] = DB::table('Qetaa')->where('QetaaID', $i)->select('QetaaName')->get();
        }

        return view('person.new-enrolments-qetaat-count', [
            'qetaat' => $qetaat,
            'counts' => $counts,
        ]);
    }

    public function editNewEnrolments($id)
    {
        $this->authorize('enrolment.update', (int) $id);

        $person = DB::table('NewUsersInformation')
            ->where('PersonID', $id)
            ->leftJoin('BloodType', 'BloodType.BloodTypeID', '=', 'NewUsersInformation.BloodTypeID')
            ->leftJoin('Qetaa', 'Qetaa.QetaaID', '=', 'NewUsersInformation.QetaaID')
            ->leftJoin('SanaMarhala', 'SanaMarhala.SanaMarhalaID', '=', 'NewUsersInformation.SanaMarhalaID')
            ->leftJoin('Manteqa', 'Manteqa.ManteqaID', '=', 'NewUsersInformation.ManteqaID')
            ->leftJoin('Districts', 'Districts.DistrictID', '=', 'NewUsersInformation.DistrictID')
            ->first();
        $questions = DB::table('MarhalaEntryQuestions')
            ->leftJoin('NewUsersPersonEntryQuestions', function ($join) use ($id) {
                $join->on('NewUsersPersonEntryQuestions.QuestionID', '=', 'MarhalaEntryQuestions.QuestionID')
                    ->where('NewUsersPersonEntryQuestions.PersonID', '=', $id);
            })
            ->select(
                'MarhalaEntryQuestions.QuestionID',
                'MarhalaEntryQuestions.QetaaID',
                'MarhalaEntryQuestions.QuestionText',
                'MarhalaEntryQuestions.RequiredAnswerType',
                'MarhalaEntryQuestions.MCAnswer',
                'MarhalaEntryQuestions.NotToBeShown',
                'MarhalaEntryQuestions.IsRequired',
                'NewUsersPersonEntryQuestions.Answer'
            )
            ->where('MarhalaEntryQuestions.QetaaID', $person->QetaaID)
            ->where('MarhalaEntryQuestions.NotToBeShown', 0)
            ->orderBy('MarhalaEntryQuestions.QuestionID', 'asc')
            ->get();

        $blood = LookupCache::all('BloodType');
        $manateq = LookupCache::all('Manteqa');
        $districts = LookupCache::all('Districts');
        $seneen_marahel = LookupCache::all('SanaMarhala');

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
        $this->authorize('enrolment.update', (int) $id);

        $exists = DB::selectOne(
            'SELECT COUNT(*) as count FROM NewUsersInformation WHERE RaqamQawmy = ? AND PersonID != ?',
            [$request->input_raqam_qawmy, $id]
        );

        if ($exists->count > 0) {
            return view('person.person-already-exists');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'second_name' => 'required',
            'third_name' => 'required',
            'gender' => 'required',
            'birthdate_input' => 'required',
            'input_raqam_qawmy' => 'required|min_digits:14|max_digits:14',
            'personal_phone_number' => 'required|min_digits:11|max_digits:11',
            'facebook_profile_url' => \App\Support\SafeHttpUrl::rules(),
            'instagram_profile_url' => \App\Support\SafeHttpUrl::rules(),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $birthDate = $request->birthdate_input;
        $nid = $request->input_raqam_qawmy;

        if (strlen($nid) == 14) {
            $year = substr($birthDate, 2, 2);
            $month = substr($birthDate, 5, 2);
            $day = substr($birthDate, 8, 2);
            $expected = substr($nid, 1, 2).substr($nid, 3, 2).substr($nid, 5, 2);
            $actual = $year.$month.$day;

            if ($expected !== $actual) {
                return redirect()->back()
                    ->withErrors(['input_raqam_qawmy' => 'الرقم القومي لا يتطابق مع تاريخ الميلاد'])
                    ->withInput();
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
                    'IsOPersonalPhoneNumberHavingWhatsapp' => $request->is_personal_phone_has_whatsapp ? 1 : 0,
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
                    'AllergyFood' => $request->allergy_food,
                    'AllergyMedicine' => $request->allergy_medicine,
                    'MedicalDiseases' => $request->medical_diseases,
                    'MedicalMedications' => $request->medical_medications,
                    'HasEmergencyCase' => $request->has_emergency_case ? 1 : 0,
                    'EmergencyDetails' => $request->emergency_details,
                ]);

            // get qetaa after update
            $person = DB::table('NewUsersInformation')
                ->where('PersonID', $id)
                ->first();

            $questions = DB::table('MarhalaEntryQuestions')
                ->where('QetaaID', $person->QetaaID)
                ->where('NotToBeShown', 0)
                ->orderBy('QuestionID', 'asc')
                ->get();

            foreach ($questions as $question) {
                $fieldName = 'question_'.$question->QuestionID;
                $answer = $request->input($fieldName);

                DB::table('NewUsersPersonEntryQuestions')->updateOrInsert(
                    [
                        'PersonID' => $id,
                        'QuestionID' => $question->QuestionID,
                    ],
                    [
                        'Answer' => $answer,
                    ]
                );
            }

            DB::commit();

            return redirect()->route('person.new-enrolments-index')
                ->with('success', 'Updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Update New Enrolment Error', [
                'message' => $e->getMessage(),
                'person_id' => $id,
            ]);

            return back()->with('error', $e->getMessage());
        }
    }
}
