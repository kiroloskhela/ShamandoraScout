<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Illuminate\Http\Response;
use Session;
use Throwable;

class MigrateNewEnrolments extends Controller
{
    public function migrate($qetaaID)
    {
        $personsBeforeMigration = DB::select("  SELECT      NewUsersInformation.*, 
                                                                GROUP_CONCAT(CONCAT(NewUsersPersonEntryQuestions.QuestionID, ':', NewUsersPersonEntryQuestions.Answer) SEPARATOR ', ') AS AnsweredQuestions
                                                FROM        NewUsersInformation
                                                JOIN        NewUsersPersonEntryQuestions ON NewUsersInformation.PersonID = NewUsersPersonEntryQuestions.PersonID
                                                WHERE       IsApproved = 1 AND NewUsersInformation.QetaaID = ?
                                                GROUP BY    NewUsersInformation.PersonID
                                                ", [$qetaaID]);
        
         foreach($personsBeforeMigration as $person)
         {
        
        try{

                $questionsAnswersPairs = explode(', ', $person->AnsweredQuestions);

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
                
                $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
                $pass = array();
                $alphaLength = strlen($alphabet) - 1;
                for ($i = 0; $i < 8; $i++) {
                    $n = rand(0, $alphaLength);
                    $pass[] = $alphabet[$n];
                }
                $passString =  implode($pass);
            
                DB::beginTransaction();

                DB::table('PersonInformation')->insert(
                    array(
                        'PersonID'=>$thisPersonID,
                        'ShamandoraCode'=>$shamandoraCode,
                        'FirstName' => $person->FirstName,
                        'SecondName' => $person->SecondName,
                        'ThirdName'   => $person->ThirdName,
                        'FourthName' => $person->FourthName,
                        'Gender' => $person->Gender,
                        'DateOfBirth' => $person->DateOfBirth,
                        'RaqamQawmy' => $person->RaqamQawmy,
                        'ScoutJoiningYear'  => $person->ScoutJoiningYear,
                        'BloodTypeID' => $person->BloodTypeID,
                        'FacebookProfileURL' =>$person->FacebookProfileURL,
                        'InstagramProfileURL' =>$person->InstagramProfileURL,
                        'PersonalEmail' => $person->PersonalEmail,
                        'RequestPersonID' => 0,
                    )
                );


                DB::table('PersonPhoneNumbers')->insert(
                    array(
                        'PersonID'=>$thisPersonID,
                        'PersonPersonalMobileNumber' => $person->PersonPersonalMobileNumber,
                        'FatherMobileNumber' => $person->FatherMobileNumber,
                        'MotherMobileNumber'   => $person->MotherMobileNumber,
                        'HomePhoneNumber' => $person->HomePhoneNumber,
                        'IsOPersonalPhoneNumberHavingWhatsapp' => $person->IsOPersonalPhoneNumberHavingWhatsapp,
                    )
                );

                DB::table('PersonLearningInformation')->insert(
                    array(
                        'PersonID'=>$thisPersonID,
                        'SchoolName'=>$person->SchoolName,
                        'SchoolGraduationYear'=>$person->SchoolGraduationYear,
                    )
                );

                DB::table('PersonQetaa')->insert(
                    array(
                        'PersonID'=>$thisPersonID,
                        'QetaaID'=>$person->QetaaID
                    )
                );

                DB::table('PersonSanaMarhala')->insert(
                    array(
                        'PersonID'=>$thisPersonID,
                        'SanaMarhalaID'=>$person->SanaMarhalaID
                    )
                );

                DB::table('PersonSpiritualFatherInformation')->insert(
                    array(
                        'PersonID'=>$thisPersonID,
                        'SpiritualFatherName'=>$person->SpiritualFatherName,
                        'SpiritualFatherChurchName'=>$person->SpiritualFatherChurchName
                    )
                );

                DB::table('PersonSystemPassword')->insert(
                    array(
                        'PersonID'=>$thisPersonID,
                        'Password'=>$passString 
                    )
                );

                    DB::table('PersonImages')->insert(
                    array(
                        'PersonID'=>$thisPersonID,
                        'PersonSystemImagePath'=>$person->PersonalImagePath,
                        'ScoutOfficialUniformImagePath'=>$person->ScoutImagePath
                    )
                );

                DB::table('PersonalPhysicalAddress')->insert(
                    array(
                        'PersonID'=>$thisPersonID,
                        'BuildingNumber'=>$person->BuildingNumber,
                        'FloorNumber'=>$person->FloorNumber,
                        'AppartmentNumber'=>$person->AppartmentNumber,
                        'MainStreetName'=>$person->MainStreetName,
                        'SubStreetName'=>$person->SubStreetName,
                        'ManteqaID'=>$person->ManteqaID,
                        'DistrictID'=>is_null($person->DistrictID)?1:$person->DistrictID,
                        'NearestLandmark'=>$person->NearestLandmark
                    )
                );

                
                
                foreach($questionsAnswersPairs as $pair)
                    {
                        //return $pair;
                        list($questionID, $answer) = explode(':', $pair);

                        DB::table('PersonEntryQuestions')->insert(
                            array(
                                'PersonID' => $thisPersonID,
                                'QuestionID' => $questionID,
                                'Answer' => $answer
                            )
                        );

                        DB::table('NewUsersPersonEntryQuestions')->where('PersonID',$person->PersonID)->where('QuestionID',$questionID)->delete();
                    }

            DB::table('NewUsersInformation')->where('PersonID',$person->PersonID)->delete();
            
            DB::commit();
            
        }
        catch(Throwable $e)
        {
            dd($e->getMessage());
            DB::rollBack();
            return view('person.entry-error');
        }

        
    }
    
        return view('person.migrate-new-enrolments-status');    
    }
}