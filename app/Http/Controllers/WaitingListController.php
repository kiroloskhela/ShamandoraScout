<?php

namespace App\Http\Controllers;

use App\Domain\Enrolment\WaitingListService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class WaitingListController extends Controller
{

public function indexWaitingList()
{
    $persons = DB::table('NewUsersInformationWaitinglist as nui')
        ->leftJoin('SanaMarhala as sm', 'sm.SanaMarhalaID', '=', 'nui.SanaMarhalaID')
        ->leftJoin('NewUsersPersonEntryQuestionsWaitinglist as nupq', 'nupq.PersonID', '=', 'nui.PersonID')
        ->select(
            'nui.PersonID',
            'nui.ShamandoraCode',
            'nui.FirstName',
            'nui.SecondName',
            'nui.ThirdName',
            'nui.FourthName',
            DB::raw("CONCAT_WS(' ', nui.FirstName, nui.SecondName, nui.ThirdName, nui.FourthName) AS FullName"),
            'nui.QetaaName',
            'nui.QetaaID',
            'sm.SanaMarhalaName',
            'nui.RaqamQawmy',
            'nui.PersonPersonalMobileNumber',
            DB::raw("IF(nupq.PersonID IS NOT NULL, 'نعم', 'لا') AS HasAnsweredQuestions"),
            DB::raw("DATE_FORMAT(nui.CreatedAt, '%Y-%m-%d %H:%i') AS CreatedAt")
        )
        ->distinct()
        ->orderByDesc('nui.CreatedAt')
        ->orderByDesc('nui.PersonID')
        ->get();
 
    return view('person.waiting-list-index', ['persons' => $persons]);
}
 
/**
 * Show a single waiting-list person's full profile.
 */
public function showWaitingList($id)
{
    $person = DB::table('NewUsersInformationWaitinglist')
        ->where('PersonID', $id)
        ->leftJoin('BloodType', 'BloodType.BloodTypeID', '=', 'NewUsersInformationWaitinglist.BloodTypeID')
        ->leftJoin('SanaMarhala', 'SanaMarhala.SanaMarhalaID', '=', 'NewUsersInformationWaitinglist.SanaMarhalaID')
        ->leftJoin('Manteqa', 'Manteqa.ManteqaID', '=', 'NewUsersInformationWaitinglist.ManteqaID')
        ->leftJoin('Districts', 'Districts.DistrictID', '=', 'NewUsersInformationWaitinglist.DistrictID')
        ->leftJoin('Faculty', 'Faculty.FacultyID', '=', 'NewUsersInformationWaitinglist.FacultyID')
        ->leftJoin('University', 'University.UniversityID', '=', 'NewUsersInformationWaitinglist.UniversityID')
        ->select(
            'NewUsersInformationWaitinglist.*',
            'BloodType.BloodTypeName',
            'SanaMarhala.SanaMarhalaName',
            'Manteqa.ManteqaName',
            'Districts.DistrictName',
            'Faculty.FacultyName',
            'University.UniversityName'
        )
        ->first();
 
    if (!$person) {
        abort(404);
    }
 
    $questions = DB::table('NewUsersPersonEntryQuestionsWaitinglist as nupq')
        ->join('MarhalaEntryQuestions as meq', 'meq.QuestionID', '=', 'nupq.QuestionID')
        ->select('meq.QuestionText', 'nupq.Answer')
        ->where('nupq.PersonID', $id)
        ->get();
 
    return view('person.waiting-list-show', [
        'person'    => $person,
        'questions' => $questions,
    ]);
}


/**
 * Migrate a person from the waiting list into the main enrolment tables.
 */
public function migrateWaitingList($id)
{
    try {
        app(WaitingListService::class)->migrate((int) $id);

        return redirect()->route('person.waiting-list-index')
            ->with('success', 'تم نقل الشخص إلى قائمة التسجيل بنجاح');
    } catch (RuntimeException $e) {
        return redirect()->route('person.waiting-list-index')
            ->with('error', $e->getMessage());
    } catch (Throwable $e) {
        Log::error('migrateWaitingList failed', ['message' => $e->getMessage(), 'person_id' => $id]);
        return redirect()->route('person.waiting-list-index')
            ->with('error', 'حدث خطأ أثناء النقل: ' . $e->getMessage());
    }
}

/**
 * Decline (delete) a person from the waiting list.
 */
public function declineWaitingList($id)
{
    try {
        app(WaitingListService::class)->decline((int) $id);

        return redirect()->route('person.waiting-list-index')
            ->with('success', 'تم رفض الطلب وحذفه من قائمة الانتظار');
    } catch (RuntimeException $e) {
        return redirect()->route('person.waiting-list-index')
            ->with('error', $e->getMessage());
    } catch (Throwable $e) {
        Log::error('declineWaitingList failed', ['message' => $e->getMessage(), 'person_id' => $id]);
        return redirect()->route('person.waiting-list-index')
            ->with('error', 'حدث خطأ أثناء الحذف: ' . $e->getMessage());
    }
}
}
