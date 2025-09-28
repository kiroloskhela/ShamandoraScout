<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use \Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request as HttpRequest; 
use Illuminate\Support\Facades\Log;
class PersonNewController extends Controller
{
    /**
     * API: Return filtered persons for a user as JSON
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


/**
        * Display a listing of the resource.
        *
        * @return Response
        */
  public function index(Request $request)
{
    // ✅ Get the user ID from the request
    $userId = $request->input('id');

    // ✅ Run the raw SQL with group filtering
    $rawPersons = DB::select("
SELECT  Distinct
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
WHERE pg2.PersonID = ?
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
    /**
     * API: Return all joined person data as JSON
     */
    public function apiShowProfile($id)
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

            $persons = collect($persons)->map(function ($person) {
            $person->full_name = trim("{$person->FirstName} {$person->SecondName} {$person->ThirdName} {$person->FourthName}");
             $person->IsApprovedText = is_null($person->IsApproved)
            ? 'لم يتم التقييم بعد'
            : ($person->IsApproved ? 'تمت الموافقة' : 'في انتظار الموافقة');
            return $person;
        });
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


              $persons = collect($persons)->map(function ($person) {
            $person->full_name = trim("{$person->FirstName} {$person->SecondName} {$person->ThirdName} {$person->FourthName}");
             $person->IsApprovedText = is_null($person->IsApproved)
            ? 'لم يتم التقييم بعد'
            : ($person->IsApproved ? 'تمت الموافقة' : 'في انتظار الموافقة');

            return $person;
        });
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
            $person = DB::table('NewUsersInformation')->where('PersonID','=',$id)->select('NewUsersInformation.PersonID', 'NewUsersInformation.ShamandoraCode')->first();

            return view("person.new-enrolments-delete", array('person' => $person));
        }

        public function destroyNewEnrolments($id)
        {

            $person = DB::table('NewUsersInformation')->where('PersonID','=',$id)->select('NewUsersInformation.PersonID', 'NewUsersInformation.QetaaID')->first();

            DB::beginTransaction();

            DB::table('NewUsersInformation')->where('PersonID',$id)->delete();
            DB::table('NewUsersPersonEntryQuestions')->where('PersonID', $id)->delete();

            DB::commit();

            

            return redirect()->route('person.new-enrolments-show-qetaa', $person->QetaaID);
        }

        public function approveNewEnrolments($id)
        {   
            return redirect()->route('person.new-enrolments-approve-again', $id);
        }

        public function approveAgainNewEnrolments($id)
        {
            $approvedInt = 1;
            DB::table('NewUsersInformation')->where('PersonID', $id)->update(['IsApproved' => $approvedInt]);
            $qetaa_id = DB::table('NewUsersInformation')->where('PersonID', $id)->first()->QetaaID;
            return redirect()->route('person.new-enrolments-show-qetaa', $qetaa_id);
        }





        public function createLiveForm()
        {

            $seneen_marahel = DB::table('SanaMarhala')->get();

            return view("person.person-create-liveform-1", array(
                'seneen_marahel'=>$seneen_marahel
            ));
        }

        public function insertLiveForm(Request $request)
        {
            if($request->newLeadersSchool)
            {
                if($request->sana_marhala_id==NULL||$request->gender==NULL)
                {
                    $seneen_marahel = DB::table('SanaMarhala')->get();

                    return view("person.person-create-liveform-1", array(
                        'seneen_marahel'=>$seneen_marahel
                    ));
                }

                $qetaa_name = "اعداد قادة";
                $qetaa_id = 10;
                $gender = $request->gender;

            }
            else
            {
                if($request->sana_marhala_id==NULL||$request->gender==NULL)
                {
                    $seneen_marahel = DB::table('SanaMarhala')->get();

                    return view("person.person-create-liveform-1", array(
                        'seneen_marahel'=>$seneen_marahel
                    ));
                }

                if($request->sana_marhala_id<5&&$request->sana_marhala_id>2)
                {
                    $qetaa_name = "براعم";
                    $qetaa_id = 1;
                    $gender = $request->gender;
                }
                elseif($request->sana_marhala_id<9&&$request->sana_marhala_id>4)
                {
                    if($request->gender=="Male")
                    {
                        $qetaa_name = "أشبال";
                        $qetaa_id = 2;
                        $gender = "Male";
                    }
                    elseif($request->gender=="Female")
                    {
                        $qetaa_name = "زهرات";
                        $qetaa_id = 9;
                        $gender = "Female";
                    }
                }
                elseif($request->sana_marhala_id<12&&$request->sana_marhala_id>8)
                {
                    if($request->gender=="Male")
                    {
                        $qetaa_name = "كشافة";
                        $qetaa_id = 8;
                        $gender = "Male";
                    }
                    elseif($request->gender=="Female")
                    {
                        $qetaa_name = "مرشدات";
                        $qetaa_id = 6;
                        $gender = "Female";
                    }
                }
                elseif($request->sana_marhala_id<14&&$request->sana_marhala_id>11)
                {
                    if($request->gender=="Male")
                    {
                        $qetaa_name = "متقدم";
                        $qetaa_id = 3;
                        $gender = "Male";
                    }
                    elseif($request->gender=="Female")
                    {
                        $qetaa_name = "رائدات";
                        $qetaa_id = 4;
                        $gender = "Female";
                    }
                }
                elseif($request->sana_marhala_id<21&&$request->sana_marhala_id>14)
                {
                        $qetaa_name = "جوالة";
                        $qetaa_id = 5;
                        $gender = $request->gender;
                }
                else
                {
                    $qetaa_name = "قادة";
                    $qetaa_id = 7;
                    $gender = $request->gender;
                }
            }
            $marhala_limit = 0;
            //return $request->sana_marhala_id;
            if(DB::table('MarhalaLiveFormLimit')
            ->where('MarhalaLiveFormLimit.QetaaID', $qetaa_id)
            ->where('MarhalaLiveFormLimit.SanaMarhalaID', $request->sana_marhala_id)
            ->select('MarhalaLiveFormLimit.MaxLimit')
            ->exists())
            {
            $marhala_limit = DB::table('MarhalaLiveFormLimit')
                        ->where('MarhalaLiveFormLimit.QetaaID', $qetaa_id)
                        ->where('MarhalaLiveFormLimit.SanaMarhalaID', $request->sana_marhala_id)
                        ->select('MarhalaLiveFormLimit.MaxLimit')
                        ->first()->MaxLimit;
            //return $marhala_limit;
            }
            //return $marhala_limit;
            $numberOfStudentsCurrentlySubmittedInSanaMarhala = 
                        DB::table('NewUsersInformation')
                        ->where('NewUsersInformation.QetaaID', $qetaa_id)
                        ->where('NewUsersInformation.SanaMarhalaID', $request->sana_marhala_id)
                        ->count();
            //return $numberOfStudentsCurrentlySubmittedInSanaMarhala;

            if($numberOfStudentsCurrentlySubmittedInSanaMarhala>$marhala_limit||$marhala_limit==0)
            {      
                return view('person.liveform-limit-exceeded');  
            }

            $marahel = DB::table('Marhala')->get();
            $faculties = DB::table('Faculty')->get();
            $universities = DB::table('University')->get();
            $rotab = DB::table('RotbaInformation')->get();
            $sana_marhala_name = DB::table('SanaMarhala')
                                -> where('SanaMarhala.SanaMarhalaID',$request->sana_marhala_id)
                                -> select('SanaMarhalaName')
                                -> first()
                                -> SanaMarhalaName;

            $questionTypes = DB::table('QuestionsTypes')->get();
            $blood = DB::table('BloodType')->get();
            //$betakat = DB::table('EgazetBetakatTaqaddom')->get();
            $manateq = DB::table('Manteqa')->get();
            $districts = DB::table('Districts')->get();
            

            return view("person.person-create-liveform", 
            array('marahel'=>$marahel, 
                    'rotab'=>$rotab,
                    'sana_marhala_id'=>$request->sana_marhala_id,
                    'sana_marhala_name'=>$sana_marhala_name,
                    'qetaa_id'=>$qetaa_id,
                    'qetaa_name'=>$qetaa_name,
                    'gender'=>$gender, 
                    'questionTypes'=>$questionTypes, 
                    'blood'=>$blood, 
                    'manateq'=>$manateq, 
                    'districts'=>$districts,
                    'faculties' =>$faculties,
                    'universities' =>$universities,
                ));
            //return view('person.person-create-liveform', array('sana_marhala_id' => $request->sana_marhala_id));
            //return redirect()->route('person.de7k', $marhala_limit);
            //return $request->sana_marhala_id;
        }

        public function insertNewPersonLiveForm(Request $request)
        {

            $raqamQawmyExistsObject = DB::selectOne('SELECT EXISTS(SELECT 1 FROM NewUsersInformation WHERE RaqamQawmy = :some_id) AS `exists`', ['some_id' => $request->input_raqam_qawmy]);
              $raqamQawmyExists = $raqamQawmyExistsObject->exists; // 0 or 1
              
  
              if($raqamQawmyExists)
              {
                  return view('person.person-already-exists');
              }
            
            $lastPersonID = DB::table('NewUsersInformation')->orderBy('PersonID','desc')->first();
            
            if($lastPersonID==Null)
                $thisPersonID = 1;
            else
                $thisPersonID = $lastPersonID->PersonID + 1;
            
            
            $shamandoraCode="SH-";

            $shamandoraCodeNumberOfDigits = 5;

            for ($i=0;$i<$shamandoraCodeNumberOfDigits-strlen((string)$thisPersonID);$i++)
            {
                $shamandoraCode = $shamandoraCode.'0';
            }

            $shamandoraCode = $shamandoraCode. $thisPersonID;
            
            
                
            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $pass = array(); //remember to declare $pass as an array
            $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
            for ($i = 0; $i < 8; $i++) {
                $n = rand(0, $alphaLength);
                $pass[] = $alphabet[$n];
            }
            $passString =  implode($pass); //turn the array into a string




            $hashedPasswordString = Hash::make($passString); // Hash it securely

























            $QetaaName = DB::table('Qetaa')->where('Qetaa.QetaaID', $request->qetaa_id)->first()->QetaaName;
            //return $QetaaName;
            try{

            $validatedData = $request->validate([
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
              ]);

            DB::table('NewUsersInformation')->insert(
                array(
                    'PersonID'              => $thisPersonID,
                    'ShamandoraCode'        => $shamandoraCode,
                    'FirstName'             => $request->first_name,
                    'SecondName'            => $request->second_name,
                    'ThirdName'             => $request->third_name,
                    'FourthName'            => $request->fourth_name,
                    'Gender'                => $request->gender,
                    'DateOfBirth'           => $request->birthdate_input,
                    'RaqamQawmy'            => $request->input_raqam_qawmy,
                    'ScoutJoiningYear'      => $request->joining_year_input,
                    'BloodTypeID'           => $request->blood_type_input,
                    'FacebookProfileURL'    => $request->inputFacebookLink,
                    'InstagramProfileURL'   => $request->inputInstagramLink,
                    'PersonalEmail'         => $request->email_input,
                    'BuildingNumber'        => $request->building_number,
                    'FloorNumber'           => $request->floor_number,
                    'AppartmentNumber'      => $request->appartment_number,
                    'MainStreetName'        => $request->main_street_name,
                    'SubStreetName'         => $request->sub_street_name,
                    'ManteqaID'             => $request->manteqa_id,
                    'DistrictID'            => is_null($request->district_id)?1:$request->district_id,
                    'NearestLandmark'       => $request->nearest_landmark,
                    'SanaMarhalaID'         => $request->sana_marhala_id, 
                    'SpiritualFatherName'   => $request->spiritual_father,
                    'SpiritualFatherChurchName' => $request->spiritual_father_church,
                    'Password'              => $hashedPasswordString, 
                    'PersonPersonalMobileNumber' => $request->personal_phone_number,
                    'FatherMobileNumber'    => $request->father_phone_number,
                    'MotherMobileNumber'    => $request->mother_phone_number,
                    'HomePhoneNumber'       => $request->home_phone_number,
                    'IsOPersonalPhoneNumberHavingWhatsapp' => $request->has_whatsapp,
                    'SchoolName'            => $request->person_school,
                    'SchoolGraduationYear'  => $request->school_grad_year,
                    'QetaaID'               => $request->qetaa_id,
                    'QetaaName'             => $QetaaName, 
                    'FacultyID'             => $request->person_faculty,
                    'UniversityID'          => $request->person_university,
                    'UniversityGraduationYear' => $request->university_grad_year,
                )
            );

        }
        catch(Exception $e)
        {
            //return view('person.entry-error');
            dd($e->getMessage());
            DB::rollBack();
            return view('person.entry-error-repeat-trial');
        }

            DB::commit();

            return redirect()->route('person.entry-questions-liveform', $thisPersonID);
        }

        public function getLiveformQuestions ($id)
        {
            $person = DB::table('NewUsersInformation')
                    ->where('NewUsersInformation.PersonID', $id)
                    ->first();
            

            $questions = DB::table('MarhalaEntryQuestions')->where('QetaaID', $person->QetaaID)->where('NotToBeShown', '=', 0)->get();

            return view('person.person-questions-liveform', array('questions'=>$questions, 'person'=>$person));
        }

        public function submitLiveformQuestions(Request $request)
        {
            //return $request[88];
            //$data = $request->all();
            $person = DB::table('NewUsersInformation')
                    ->where('NewUsersInformation.PersonID', $request->person_id)
                    ->first();
            
            
            $questions = DB::table('MarhalaEntryQuestions')->where('QetaaID', '=' ,$person->QetaaID)->where('NotToBeShown', '=', 0)->get();
            
            DB::beginTransaction();
        try{
            
            foreach ($questions as $question)
            {  
                
                $q = $request[$question->QuestionID];

                if($question->IsRequired&&$q==NULL)
                {
                    //return $question->QuestionID;
                    //return $question->QuestionID;
                    DB::rollBack();
                    return view('person.entry-error-repeat-trial');
                }
                DB::table('NewUsersPersonEntryQuestions')->insert(
                    array(
                        'PersonID' => $request->person_id,
                        'QuestionID' => $question->QuestionID,
                        'Answer' => $q
                    )
                );
            }
        }
        catch(Exception $e)
        {
            //dd($e->getMessage());
            DB::rollBack();
            return view('person.entry-error');
        }
        DB::commit();
            
        return view('person.liveform-finalize');

        }

        public function create()
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
            return view("person.person-create", 
            array('marahel'=>$marahel, 
                    'rotab'=>$rotab, 
                    'seneen_marahel'=>$seneen_marahel, 
                    'questionTypes'=>$questionTypes, 
                    'blood'=>$blood, 
                    'betakat'=>$betakat, 
                    'manateq'=>$manateq, 
                    'districts'=>$districts, 
                    'qetaat'=>$qetaat,
                    'faculties'=>$faculties,
                    'universities'=>$universities,
                ));
        }

        public function insert(Request  $request)
        {
            
              $raqamQawmyExistsObject = DB::selectOne('SELECT EXISTS(SELECT 1 FROM PersonInformation WHERE RaqamQawmy = :some_id) AS `exists`', ['some_id' => $request->input_raqam_qawmy]);
              $raqamQawmyExists = $raqamQawmyExistsObject->exists; // 0 or 1
              
            
              if($raqamQawmyExists)
              {
                  return view('person.person-already-exists');
              }
            
            $lastPersonID = DB::table('PersonInformation')->orderBy('PersonID','desc')->first();
            
            if($lastPersonID==Null)
                $thisPersonID = 1;
            else
                $thisPersonID = $lastPersonID->PersonID + 1;
            
            $shamandoraCode="SH-";

            $shamandoraCodeNumberOfDigits = 5;

            for ($i=0;$i<$shamandoraCodeNumberOfDigits-strlen((string)$thisPersonID);$i++)
            {
                $shamandoraCode = $shamandoraCode.'0';
            }

            $shamandoraCode = $shamandoraCode. $thisPersonID;
            //dd($shamandoraCode);
            
            //print_r($shamandoraCode);
            //return "".$thisPersonID."\n".$shamandoraCode;

            
            

              
              

            
            
        try{

            $validatedData = $request->validate([
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
                'sana_marhala_id'=>'required',
              ]);
            

            DB::beginTransaction();

            DB::table('PersonInformation')->insert(
                array(
                    'PersonID'=>$thisPersonID,
                    'ShamandoraCode'=>$shamandoraCode,
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


            DB::table('PersonPhoneNumbers')->insert(
                array(
                    'PersonID'=>$thisPersonID,
                    'PersonPersonalMobileNumber' => $request->personal_phone_number,
                    'FatherMobileNumber' => $request->father_phone_number,
                    'MotherMobileNumber'   => $request->mother_phone_number,
                    'HomePhoneNumber' => $request->home_phone_number,
                    'IsOPersonalPhoneNumberHavingWhatsapp' => $request->has_whatsapp,
                )
            );

            DB::table('PersonJob')->insert(
                array(
                    'PersonID'=>$thisPersonID,
                    'JobName'=>$request->person_job,
                    'WorkPlace'=>$request->person_job_place
                )
            );

            DB::table('PersonLearningInformation')->insert(
                array(
                    'PersonID'=>$thisPersonID,
                    'SchoolName'=>$request->school_name,
                    'SchoolGraduationYear'=>$request->school_grad_year,
                    'FacultyID'=>$request->person_faculty,
                    'UniversityID'=>$request->person_university,
                    'ActualFacultyGraduationYear'=>$request->university_grad_year
                )
            );




            //$timestamp = time();
            //$formatted = date('y-m-d h:i:s T', $timestamp);

            DB::table('PersonRotbaKashfeyya')->insert(
                array(
                    'PersonID'=>$thisPersonID,
                    'RotbaID'=>$request->rotba_kashfeyya_id
                )
            );


            DB::table('PersonQetaa')->insert(
                array(
                    'PersonID'=>$thisPersonID,
                    'QetaaID'=>$request->qetaa_id
                )
            );


            DB::table('PersonEgazetBetakatTaqaddom')->insert(
                array(
                    'PersonID'=>$thisPersonID,
                    'EgazetBetakatTaqaddomID'=>$request->betaka_id
                )
            );

            DB::table('PersonSanaMarhala')->insert(
                array(
                    'PersonID'=>$thisPersonID,
                    'SanaMarhalaID'=>$request->sana_marhala_id
                )
            );

            DB::table('PersonSpiritualFatherInformation')->insert(
                array(
                    'PersonID'=>$thisPersonID,
                    'SpiritualFatherName'=>$request->spiritual_father,
                    'SpiritualFatherChurchName'=>$request->spiritual_father_church
                )
            );



                    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
                    $pass = [];
                    $alphaLength = strlen($alphabet) - 1; // length - 1

                    for ($i = 0; $i < 8; $i++) {
                        $n = rand(0, $alphaLength);
                        $pass[] = $alphabet[$n];
                    }

                    $plainPassword = implode($pass); // The actual random password
                    $hashedPassword = Hash::make($plainPassword); // Hash it securely

                    // Insert into DB
                    DB::table('PersonSystemPassword')->insert([
                        'PersonID' => $thisPersonID,
                        'Password' => $hashedPassword,
                    ]);

                 if ($request->personal_phone_number && $request->has_whatsapp) {
                        try {
                            // Create an internal request payload for sendWithHeader
                            $payload = [
                                'full_number' => $request->personal_phone_number,
                                'message'     => "اهلا بك يا {$request->first_name} {$request->second_name} {$request->third_name} {$request->fourth_name}\nالرقم الخاص بك: {$thisPersonID}\nالرقم: {$plainPassword}\nيرجى تغيير الرقم عند أول تسجيل دخول.\nمرحبا بك في الكشافة.",
                            ];

        
                            // Build a fake POST Request object and call the controller directly
                            $fake = HttpRequest::create('/whatsapp/send', 'POST', $payload);

                            app(WhatsAppBridgeController::class)->send($fake);
                            // We ignore the returned RedirectResponse; we’ll always go back to admin list
                        } catch (\Throwable $e) {
                            Log::error('Failed to send WA new password via sendWithHeader', [
                    
                                'error'     => $e->getMessage(),
                            ]);
                        }
                    } else {
                        Log::warning('No phone found for WA new password');
                    }


            DB::table('PersonalPhysicalAddress')->insert(
                array(
                    'PersonID'=>$thisPersonID,
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

            

            return redirect()->route('person.entry-questions', $thisPersonID);


        }

        public function getQuestions ($id)
        {
            $person = DB::table('PersonInformation')
                    ->where('PersonInformation.PersonID', $id)
                    ->Join('PersonQetaa', 'PersonInformation.PersonID' , '=', 'PersonQetaa.PersonID')
                    ->Join('Qetaa', 'PersonQetaa.QetaaID', '=', 'Qetaa.QetaaID')
                    ->Join('PersonSystemPassword', 'PersonInformation.PersonID' , '=', 'PersonSystemPassword.PersonID')
                    ->select('PersonInformation.*', 'PersonSystemPassword.Password', 'PersonQetaa.QetaaID', 'Qetaa.QetaaName')
                    ->first();

            $questions = DB::table('MarhalaEntryQuestions')->where('QetaaID', $person->QetaaID)->where('NotToBeShown', '=', 0)->get();

            return view('person.person-questions', array('questions'=>$questions, 'person'=>$person));
        }

        public function submitQuestions(Request $request)
        {
            $person = DB::table('PersonInformation')
                    ->where('PersonInformation.PersonID', $request->person_id)
                    ->Join('PersonQetaa', 'PersonInformation.PersonID' , '=', 'PersonQetaa.PersonID')
                    ->Join('Qetaa', 'PersonQetaa.QetaaID', '=', 'Qetaa.QetaaID')
                    ->Join('PersonSystemPassword', 'PersonInformation.PersonID' , '=', 'PersonSystemPassword.PersonID')
                    ->select('PersonInformation.*', 'PersonSystemPassword.Password', 'PersonQetaa.QetaaID', 'Qetaa.QetaaName')
                    ->first();
            
                    $questions = DB::table('MarhalaEntryQuestions')->where('QetaaID', $person->QetaaID)->where('NotToBeShown', '=', 0)->get();
            
            


            //return $request;
            DB::beginTransaction();
        try{
            
            foreach ($questions as $question)
            {  
                
                $q = $request[$question->QuestionID];
                //return $qs;

                if($question->IsRequired&&$q==NULL)
                {
                    //return $question->QuestionID;
                    DB::rollBack();
                    return view('person.entry-error-repeat-trial');
                }
                DB::table('PersonEntryQuestions')->insert(
                    array(
                        'PersonID' => $request->person_id,
                        'QuestionID' => $question->QuestionID,
                        'Answer' => $q
                    )
                );
            }
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



}