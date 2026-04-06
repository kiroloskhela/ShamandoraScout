<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;

use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\AdminPasswordController;

use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\PersonNewController;
use App\Http\Controllers\PersonProfileController;

use App\Http\Controllers\RoleController;
use App\Http\Controllers\PersonRoleController;
use App\Http\Controllers\GroupPersonController;

use App\Http\Controllers\EventController;
use App\Http\Controllers\EventTypeController;

use App\Http\Controllers\MediaController;

use App\Http\Controllers\GroupTypeController;
use App\Http\Controllers\GroupController;

use App\Http\Controllers\RotbaKashfeyaController;

use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryIssueController;

use App\Http\Controllers\LiveFormMaxLimitsController;
use App\Http\Controllers\BetakaTakaddomController;
use App\Http\Controllers\BloodTypeController;
use App\Http\Controllers\CurriculaCategoryController;
use App\Http\Controllers\CurriculaController;

use App\Http\Controllers\ManteqaController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\QetaaController;
use App\Http\Controllers\FacultyController;

use App\Http\Controllers\SeasonController;
use App\Http\Controllers\SeasonEventController;

use App\Http\Controllers\UniversityController;

use App\Http\Controllers\CustodyRequestController;
use App\Http\Controllers\AdminCustodyRequestController;

use App\Http\Controllers\MarhalaDeraseyyaController;
use App\Http\Controllers\SanaMarhalaDeraseyyaController;
use App\Http\Controllers\MarhalaEntryQuestionsController;

use App\Http\Controllers\FeedbackController;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TestingController;

use App\Http\Controllers\LocationController;
use App\Http\Controllers\PlaceController;

use App\Http\Controllers\WhatsAppBridgeController;


use App\Http\Controllers\AdminPlaceBookingController;
use App\Http\Controllers\PlaceBookingController;



use App\Http\Controllers\PersonSeasonEventFinanceController;
use App\Http\Controllers\SeasonEventFinanceController;
use App\Http\Controllers\BookingController;

use App\Http\Controllers\PersonBlackListController;
use App\Http\Controllers\PersonSpecialCaseController;
use App\Http\Controllers\SeasonEventBookingFinanceController;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\SeasonEventWaitingListController;  

/*
|--------------------------------------------------------------------------
| Public / UI Pages
|--------------------------------------------------------------------------

/*
|--------------------------------------------------------------------------
| Auth (Login / Register / Forgot Password)
|--------------------------------------------------------------------------
*/
Route::get('/login-auth', [LoginController::class, 'show'])->name('login-auth');
Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::view('/register', 'register');

Route::get('/forgot-password', function () {
    return view('forgot-password.form');
})->name('forgot-password.form');

Route::post('/forgot-password', [ForgotPasswordController::class, 'handle'])
    ->name('forgot-password.handle');



/*
|--------------------------------------------------------------------------
| Live Form (Public)
|--------------------------------------------------------------------------
*/

Route::get('/liveform', [PersonNewController::class, 'createLiveForm'])->name('person.liveform-create');
Route::post('/liveform/step1', [PersonNewController::class, 'insertLiveForm'])->name('person.liveform-insert');

Route::get('/liveform/step2', [PersonNewController::class, 'showLiveFormStep2'])->name('person.liveform-step2');
Route::post('/liveform/step2', [PersonNewController::class, 'saveLiveFormStep2'])->name('person.liveform-step2-save');

Route::get('/liveform/questions', [PersonNewController::class, 'getLiveformQuestions'])->name('person.entry-questions-liveform');
Route::post('/liveform/questions', [PersonNewController::class, 'submitLiveformQuestions'])->name('person.entry-questions-submit-liveform');

Route::get('/liveform/person/delete/{id}', [PersonNewController::class, 'deletesLiveForm'])->name('person.liveform-delete');
Route::delete('/liveform/person/destroy/{id}', [PersonNewController::class, 'destroyLiveForm'])->name('person.liveform-destroy');
//Route::post('/liveform/person/entry-questions/submit', array('as'=> 'person.entry-questions-submit-liveform', 'uses'=>'App\Http\Controllers\PersonNewController@submitLiveFormQuestions'));
//Route::get('/liveform/person/entry-questions/insert/{id}', array('as'=> 'person.entry-questions-liveform', 'uses'=>'App\Http\Controllers\PersonNewController@getLiveFormQuestions'));
Route::get('/liveform/person/delete/{id}', [PersonNewController::class, 'deletesLiveForm'])->name('person.liveform-delete');
Route::delete('/liveform/person/destroy/{id}', [PersonNewController::class, 'destroyLiveForm'])->name('person.liveform-destroy');

// !! IMPORTANT !! THIS ROUTES SHOULD BE DELETED AFTER ALL CONFLICTS RESOLVED AND LIVEFORM QUESTIONS MIGRATED TO NEW SYSTEM, IT'S ONLY FOR RESUMING LEGACY LIVEFORM QUESTIONS IN CASE OF ANY ISSUES
Route::get('/liveform/resume/{id}', [PersonNewController::class, 'resumeLegacyLiveformQuestions'])
    ->name('person.liveform-resume-questions');

Route::post('/liveform/resume/{id}', [PersonNewController::class, 'submitLegacyLiveformQuestions'])
    ->name('person.liveform-resume-questions-submit');



Route::get('/liveform/apologize', fn() => view('person.liveform-limit-exceeded'));
Route::get('/liveform/finalize', fn() => view('person.liveform-finalize'));
Route::get('/person/delete/{id}', array('as'=> 'person.delete', 'uses'=>'App\Http\Controllers\PersonNewController@deletes'));
Route::delete('/person/destroy/{id}', array('as'=> 'person.destroy', 'uses'=>'App\Http\Controllers\PersonNewController@destroy'));




/*
|--------------------------------------------------------------------------
| New Enrolments (Public)
|--------------------------------------------------------------------------
*/



/*
|--------------------------------------------------------------------------
| Testing
|--------------------------------------------------------------------------
*/
// Route::get('/testing', [TestingController::class, 'index'])->name('testing.index');
// Route::post('/testing', [TestingController::class, 'upload'])->name('testing.upload');





/*
|--------------------------------------------------------------------------
| Feedback
|--------------------------------------------------------------------------
*/
Route::view('/feedback', 'feedback.index');
Route::post('/feedback', [FeedbackController::class, 'create'])->name('feedback.create');



/*
|--------------------------------------------------------------------------
| SuperAdmin Only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'checkAuth:SuperAdmin'])->group(function () {

    // Roles
    Route::get('/role', [RoleController::class, 'index'])->name('role.index');
    Route::get('/role/add', [RoleController::class, 'create'])->name('role.create');
    Route::post('/role/insert', [RoleController::class, 'insert'])->name('role.insert');
    Route::get('/role/edit/{id}', [RoleController::class, 'edit'])->name('role.edit');
    Route::patch('/role/update/{id}', [RoleController::class, 'updates'])->name('role.update');
    Route::get('/role/delete/{id}', [RoleController::class, 'deletes'])->name('role.delete');
    Route::delete('/role/destroy/{id}', [RoleController::class, 'destroy'])->name('role.destroy');

    // Person Roles Assignment
    Route::get('/person-role', [PersonRoleController::class, 'index'])->name('person-role.index');
    Route::get('/person-role/add', [PersonRoleController::class, 'create'])->name('person-role.create');
    Route::post('/person-role/insert', [PersonRoleController::class, 'insert'])->name('person-role.insert');
    Route::get('/person-role/edit/{id}', [PersonRoleController::class, 'edit'])->name('person-role.edit');
    Route::patch('/person-role/update/{id}', [PersonRoleController::class, 'updates'])->name('person-role.update');
    Route::get('/person-role/delete/{id}', [PersonRoleController::class, 'deletes'])->name('person-role.delete');
    Route::delete('/person-role/destroy/{id}', [PersonRoleController::class, 'destroy'])->name('person-role.destroy');

    // Group Person (add khadem)
    Route::get('/group-person/add-khadem', [GroupPersonController::class, 'createKhadem'])->name('group-person.create-khadem');
    Route::get('/group-person/delete/{id}', [GroupPersonController::class, 'deletes'])->name('group-person.delete');
    Route::delete('/group-person/destroy/{id}', [GroupPersonController::class, 'destroy'])->name('group-person.destroy');

    // SuperAdmin Password Management
    Route::get('/admin/passwords', [AdminPasswordController::class, 'index'])->name('admin.passwords');
    Route::get('/admin/passwords/{id}/edit', [AdminPasswordController::class, 'edit'])->name('admin.passwords.edit');
    Route::post('/admin/passwords/{id}/update', [AdminPasswordController::class, 'update'])->name('admin.passwords.update');

    // Whatsapp
    Route::post('/whatsapp/send', [WhatsAppBridgeController::class, 'send'])->name('whatsapp.send');
    Route::post('/whatsapp/sendWithHeader', [WhatsAppBridgeController::class, 'sendWithHeader'])->name('whatsapp.sendWithHeader');

    // Show ALL persons
    Route::get('/person/ShowPersons', [PersonNewController::class, 'ShowPersons'])->name('person.ShowPersons');


    // Group Person (normal)
    Route::get('/group-person', [GroupPersonController::class, 'index'])->name('group-person.index');
    Route::get('/group-person/add-makhdoom', [GroupPersonController::class, 'createMakhdoom'])->name('group-person.create-makhdoom');
    Route::post('/group-person/insert', [GroupPersonController::class, 'insert'])->name('group-person.insert');
    Route::get('/group-person/edit/{id}', [GroupPersonController::class, 'edit'])->name('group-person.edit');
    Route::patch('/group-person/update/{id}', [GroupPersonController::class, 'updates'])->name('group-person.update');

    Route::get('/person', [PersonNewController::class, 'index'])->name('person.index');
    Route::get('/person/add', [PersonNewController::class, 'create'])->name('person.create');
    Route::post('/person/insert', [PersonNewController::class, 'insert'])->name('person.insert');

    Route::get('/person/entry-questions/insert/{id}', [PersonNewController::class, 'getQuestions'])->name('person.entry-questions');
    Route::post('/person/entry-questions/submit', [PersonNewController::class, 'submitQuestions'])->name('person.entry-questions-submit');

    Route::get('/person/show/{id}', [PersonNewController::class, 'show'])->name('person.show');
    Route::get('/person/edit/{id}', [PersonNewController::class, 'edit'])->name('person.edit');
    Route::patch('/person/update/{id}', [PersonNewController::class, 'updates'])->name('person.update');



    // Delete Persons
    Route::get('/person/delete/{id}', [PersonNewController::class, 'deletes'])->name('person.delete');
    Route::delete('/person/destroy/{id}', [PersonNewController::class, 'destroy'])->name('person.destroy');

    // Events
    Route::get('/event', [EventController::class, 'index'])->name('event.index');
    Route::get('/event/add-recursive', [EventController::class, 'createRecursive'])->name('event.create-recursive');
    Route::post('/event/insert-recursive', [EventController::class, 'insertRecursive'])->name('event.insert-recursive');
    Route::get('/event/add', [EventController::class, 'create'])->name('event.create');
    Route::post('/event/insert', [EventController::class, 'insert'])->name('event.insert');
    Route::get('/event/edit/{id}', [EventController::class, 'edit'])->name('event.edit');
    Route::patch('/event/update/{id}', [EventController::class, 'updates'])->name('event.update');
    Route::get('/event/delete/{id}', [EventController::class, 'deletes'])->name('event.delete');
    Route::delete('/event/destroy/{id}', [EventController::class, 'destroy'])->name('event.destroy');

    // Event Types
    Route::get('/event-type', [EventTypeController::class, 'index'])->name('event-type.index');
    Route::get('/event-type/add', [EventTypeController::class, 'create'])->name('event-type.create');
    Route::post('/event-type/insert', [EventTypeController::class, 'insert'])->name('event-type.insert');
    Route::get('/event-type/edit/{id}', [EventTypeController::class, 'edit'])->name('event-type.edit');
    Route::patch('/event-type/update/{id}', [EventTypeController::class, 'updates'])->name('event-type.update');
    Route::get('/event-type/delete/{id}', [EventTypeController::class, 'deletes'])->name('event-type.delete');
    Route::delete('/event-type/destroy/{id}', [EventTypeController::class, 'destroy'])->name('event-type.destroy');

    // Media
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::get('/media/add', [MediaController::class, 'create'])->name('media.create');
    Route::post('/media/insert', [MediaController::class, 'insert'])->name('media.insert');
    Route::get('/media/edit/{id}', [MediaController::class, 'edit'])->name('media.edit');
    Route::patch('/media/update/{id}', [MediaController::class, 'update'])->name('media.update');
    Route::get('/media/delete/{id}', [MediaController::class, 'delete'])->name('media.delete');
    Route::delete('/media/destroy/{id}', [MediaController::class, 'destroy'])->name('media.destroy');


    // Group Type
    Route::get('/group-type', [GroupTypeController::class, 'index'])->name('group-type.index');
    Route::get('/group-type/add', [GroupTypeController::class, 'create'])->name('group-type.create');
    Route::post('/group-type/insert', [GroupTypeController::class, 'insert'])->name('group-type.insert');
    Route::get('/group-type/edit/{id}', [GroupTypeController::class, 'edit'])->name('group-type.edit');
    Route::patch('/group-type/update/{id}', [GroupTypeController::class, 'updates'])->name('group-type.update');
    Route::get('/group-type/delete/{id}', [GroupTypeController::class, 'deletes'])->name('group-type.delete');
    Route::delete('/group-type/destroy/{id}', [GroupTypeController::class, 'destroy'])->name('group-type.destroy');

    // Group
    Route::get('/group', [GroupController::class, 'index'])->name('group.index');
    Route::get('/group/add', [GroupController::class, 'create'])->name('group.create');
    Route::post('/group/insert', [GroupController::class, 'insert'])->name('group.insert');
    Route::get('/group/edit/{id}', [GroupController::class, 'edit'])->name('group.edit');
    Route::patch('/group/update/{id}', [GroupController::class, 'updates'])->name('group.update');
    Route::get('/group/delete/{id}', [GroupController::class, 'deletes'])->name('group.delete');
    Route::delete('/group/destroy/{id}', [GroupController::class, 'destroy'])->name('group.destroy');

    // Rotab
    Route::get('/rotab', [RotbaKashfeyaController::class, 'index'])->name('rotab.index');
    Route::get('/rotab/add', [RotbaKashfeyaController::class, 'create'])->name('rotab.create');
    Route::post('/rotab/insert', [RotbaKashfeyaController::class, 'insert'])->name('rotab.insert');
    Route::get('/rotab/edit/{id}', [RotbaKashfeyaController::class, 'edit'])->name('rotab.edit');
    Route::patch('/rotab/update/{id}', [RotbaKashfeyaController::class, 'updates'])->name('rotab.update');
    Route::get('/rotab/delete/{id}', [RotbaKashfeyaController::class, 'deletes'])->name('rotab.delete');
    Route::delete('/rotab/destroy/{id}', [RotbaKashfeyaController::class, 'destroy'])->name('rotab.destroy');

  

    // Betaka
    Route::get('/betaka', [BetakaTakaddomController::class, 'index'])->name('betaka.index');
    Route::get('/betaka/add', [BetakaTakaddomController::class, 'create'])->name('betaka.create');
    Route::post('/betaka/insert', [BetakaTakaddomController::class, 'insert'])->name('betaka.insert');
    Route::get('/betaka/edit/{id}', [BetakaTakaddomController::class, 'edit'])->name('betaka.edit');
    Route::patch('/betaka/update/{id}', [BetakaTakaddomController::class, 'updates'])->name('betaka.update');
    Route::get('/betaka/delete/{id}', [BetakaTakaddomController::class, 'deletes'])->name('betaka.delete');
    Route::delete('/betaka/destroy/{id}', [BetakaTakaddomController::class, 'destroy'])->name('betaka.destroy');

    // Blood
    Route::get('/blood', [BloodTypeController::class, 'index'])->name('blood.index');
    Route::get('/blood/add', [BloodTypeController::class, 'create'])->name('blood.create');
    Route::post('/blood/insert', [BloodTypeController::class, 'insert'])->name('blood.insert');
    Route::get('/blood/edit/{id}', [BloodTypeController::class, 'edit'])->name('blood.edit');
    Route::patch('/blood/update/{id}', [BloodTypeController::class, 'updates'])->name('blood.update');
    Route::get('/blood/delete/{id}', [BloodTypeController::class, 'deletes'])->name('blood.delete');
    Route::delete('/blood/destroy/{id}', [BloodTypeController::class, 'destroy'])->name('blood.destroy');

    // CurriculaCategory
    Route::get('/CurriculaCategory', [CurriculaCategoryController::class, 'index'])->name('CurriculaCategory.index');
    Route::get('/CurriculaCategory/add', [CurriculaCategoryController::class, 'create'])->name('CurriculaCategory.create');
    Route::post('/CurriculaCategory/insert', [CurriculaCategoryController::class, 'insert'])->name('CurriculaCategory.insert');
    Route::get('/CurriculaCategory/edit/{id}', [CurriculaCategoryController::class, 'edit'])->name('CurriculaCategory.edit');
    Route::patch('/CurriculaCategory/update/{id}', [CurriculaCategoryController::class, 'updates'])->name('CurriculaCategory.update');
    Route::get('/CurriculaCategory/delete/{id}', [CurriculaCategoryController::class, 'deletes'])->name('CurriculaCategory.delete');
    Route::delete('/CurriculaCategory/destroy/{id}', [CurriculaCategoryController::class, 'destroy'])->name('CurriculaCategory.destroy');



    // Manteqa
    Route::get('/manteqa', [ManteqaController::class, 'index'])->name('manteqa.index');
    Route::get('/manteqa/add', [ManteqaController::class, 'create'])->name('manteqa.create');
    Route::post('/manteqa/insert', [ManteqaController::class, 'insert'])->name('manteqa.insert');
    Route::get('/manteqa/edit/{id}', [ManteqaController::class, 'edit'])->name('manteqa.edit');
    Route::patch('/manteqa/update/{id}', [ManteqaController::class, 'updates'])->name('manteqa.update');
    Route::get('/manteqa/delete/{id}', [ManteqaController::class, 'deletes'])->name('manteqa.delete');
    Route::delete('/manteqa/destroy/{id}', [ManteqaController::class, 'destroy'])->name('manteqa.destroy');

    // District
    Route::get('/district', [DistrictController::class, 'index'])->name('district.index');
    Route::get('/district/add', [DistrictController::class, 'create'])->name('district.create');
    Route::post('/district/insert', [DistrictController::class, 'insert'])->name('district.insert');
    Route::get('/district/edit/{id}', [DistrictController::class, 'edit'])->name('district.edit');
    Route::patch('/district/update/{id}', [DistrictController::class, 'updates'])->name('district.update');
    Route::get('/district/delete/{id}', [DistrictController::class, 'deletes'])->name('district.delete');
    Route::delete('/district/destroy/{id}', [DistrictController::class, 'destroy'])->name('district.destroy');

    // Qetaa
    Route::get('/qetaa', [QetaaController::class, 'index'])->name('qetaa.index');
    Route::get('/qetaa/add', [QetaaController::class, 'create'])->name('qetaa.create');
    Route::post('/qetaa/insert', [QetaaController::class, 'insert'])->name('qetaa.insert');
    Route::get('/qetaa/edit/{id}', [QetaaController::class, 'edit'])->name('qetaa.edit');
    Route::patch('/qetaa/update/{id}', [QetaaController::class, 'updates'])->name('qetaa.update');
    Route::get('/qetaa/delete/{id}', [QetaaController::class, 'deletes'])->name('qetaa.delete');
    Route::delete('/qetaa/destroy/{id}', [QetaaController::class, 'destroy'])->name('qetaa.destroy');

    // Faculty
    Route::get('/faculty', [FacultyController::class, 'index'])->name('faculty.index');
    Route::get('/faculty/add', [FacultyController::class, 'create'])->name('faculty.create');
    Route::post('/faculty/insert', [FacultyController::class, 'insert'])->name('faculty.insert');
    Route::get('/faculty/edit/{id}', [FacultyController::class, 'edit'])->name('faculty.edit');
    Route::patch('/faculty/update/{id}', [FacultyController::class, 'updates'])->name('faculty.update');
    Route::get('/faculty/delete/{id}', [FacultyController::class, 'deletes'])->name('faculty.delete');
    Route::delete('/faculty/destroy/{id}', [FacultyController::class, 'destroy'])->name('faculty.destroy');

    // Season
    Route::get('/season', [SeasonController::class, 'index'])->name('season.index');
    Route::get('/season/add', [SeasonController::class, 'create'])->name('season.create');
    Route::post('/season/insert', [SeasonController::class, 'insert'])->name('season.insert');
    Route::get('/season/edit/{id}', [SeasonController::class, 'edit'])->name('season.edit');
    Route::patch('/season/update/{id}', [SeasonController::class, 'updates'])->name('season.update');
    Route::get('/season/delete/{id}', [SeasonController::class, 'deletes'])->name('season.delete');
    Route::delete('/season/destroy/{id}', [SeasonController::class, 'destroy'])->name('season.destroy');

    // Season Event
    Route::get('/season-event', [SeasonEventController::class, 'index'])->name('season-event.index');
    Route::get('/season-event/add', [SeasonEventController::class, 'create'])->name('season-event.create');
    Route::post('/season-event/insert', [SeasonEventController::class, 'insert'])->name('season-event.insert');
    Route::get('/season-event/edit/{id}', [SeasonEventController::class, 'edit'])->name('season-event.edit');
    Route::patch('/season-event/update/{id}', [SeasonEventController::class, 'update'])->name('season-event.update');
    Route::get('/season-event/delete/{id}', [SeasonEventController::class, 'deletes'])->name('season-event.delete');
    Route::delete('/season-event/destroy/{id}', [SeasonEventController::class, 'destroy'])->name('season-event.destroy');

    // University
    Route::get('/university', [UniversityController::class, 'index'])->name('university.index');
    Route::get('/university/add', [UniversityController::class, 'create'])->name('university.create');
    Route::post('/university/insert', [UniversityController::class, 'insert'])->name('university.insert');
    Route::get('/university/edit/{id}', [UniversityController::class, 'edit'])->name('university.edit');
    Route::patch('/university/update/{id}', [UniversityController::class, 'updates'])->name('university.update');
    Route::get('/university/delete/{id}', [UniversityController::class, 'deletes'])->name('university.delete');
    Route::delete('/university/destroy/{id}', [UniversityController::class, 'destroy'])->name('university.destroy');


    // Marhala
    Route::get('/marhala', [MarhalaDeraseyyaController::class, 'index'])->name('marhala.index');
    Route::get('/marhala/add', [MarhalaDeraseyyaController::class, 'create'])->name('marhala.create');
    Route::post('/marhala/insert', [MarhalaDeraseyyaController::class, 'insert'])->name('marhala.insert');
    Route::get('/marhala/edit/{id}', [MarhalaDeraseyyaController::class, 'edit'])->name('marhala.edit');
    Route::patch('/marhala/update/{id}', [MarhalaDeraseyyaController::class, 'updates'])->name('marhala.update');
    Route::get('/marhala/delete/{id}', [MarhalaDeraseyyaController::class, 'deletes'])->name('marhala.delete');
    Route::delete('/marhala/destroy/{id}', [MarhalaDeraseyyaController::class, 'destroy'])->name('marhala.destroy');

    // Sana Marhala
    Route::get('/sana-marhala', [SanaMarhalaDeraseyyaController::class, 'index'])->name('sana-marhala.index');
    Route::get('/sana-marhala/add', [SanaMarhalaDeraseyyaController::class, 'create'])->name('sana-marhala.create');
    Route::post('/sana-marhala/insert', [SanaMarhalaDeraseyyaController::class, 'insert'])->name('sana-marhala.insert');
    Route::get('/sana-marhala/edit/{id}', [SanaMarhalaDeraseyyaController::class, 'edit'])->name('sana-marhala.edit');
    Route::patch('/sana-marhala/update/{id}', [SanaMarhalaDeraseyyaController::class, 'updates'])->name('sana-marhala.update');
    Route::get('/sana-marhala/delete/{id}', [SanaMarhalaDeraseyyaController::class, 'deletes'])->name('sana-marhala.delete');
    Route::delete('/sana-marhala/destroy/{id}', [SanaMarhalaDeraseyyaController::class, 'destroy'])->name('sana-marhala.destroy');

 
    // Liveform MaxLimits (duplicate in your original file, keep one set)
    Route::get('/liveform-maxlimits', [LiveFormMaxLimitsController::class, 'index'])->name('liveform-maxlimits.index');
    Route::get('/liveform-maxlimits/add', [LiveFormMaxLimitsController::class, 'create'])->name('liveform-maxlimits.create');
    Route::post('/liveform-maxlimits/insert', [LiveFormMaxLimitsController::class, 'insert'])->name('liveform-maxlimits.insert');
    Route::get('/liveform-maxlimits/edit/{id}', [LiveFormMaxLimitsController::class, 'edit'])->name('liveform-maxlimits.edit');
    Route::patch('/liveform-maxlimits/update/{id}', [LiveFormMaxLimitsController::class, 'updates'])->name('liveform-maxlimits.update');
    Route::get('/liveform-maxlimits/delete/{id}', [LiveFormMaxLimitsController::class, 'deletes'])->name('liveform-maxlimits.delete');
    Route::delete('/liveform-maxlimits/destroy/{id}', [LiveFormMaxLimitsController::class, 'destroy'])->name('liveform-maxlimits.destroy');

    // Locations
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('/locations/create', [LocationController::class, 'create'])->name('locations.create');
    Route::post('/locations/insert', [LocationController::class, 'insert'])->name('locations.insert');
    Route::get('/locations/edit/{id}', [LocationController::class, 'edit'])->name('locations.edit');
    Route::patch('/locations/updates/{id}', [LocationController::class, 'updates'])->name('locations.updates');
    Route::get('/locations/deletes/{id}', [LocationController::class, 'deletes'])->name('locations.deletes');
    Route::delete('/locations/destroy/{id}', [LocationController::class, 'destroy'])->name('locations.destroy');

    // PlaceTypes
    Route::get('/place',                  [PlaceController::class, 'index'])->name('place.index');
    Route::get('/place/add',              [PlaceController::class, 'create'])->name('place.create');
    Route::post('/place/insert',          [PlaceController::class, 'insert'])->name('place.insert');
    Route::get('/place/edit/{id}',        [PlaceController::class, 'edit'])->name('place.edit');
    Route::patch('/place/update/{id}',    [PlaceController::class, 'updates'])->name('place.update');
    Route::get('/place/delete/{id}',      [PlaceController::class, 'deletes'])->name('place.delete');
    Route::delete('/place/destroy/{id}',  [PlaceController::class, 'destroy'])->name('place.destroy');

    // Person Blacklist
    Route::get('/personblacklist', [PersonBlackListController::class, 'index'])->name('personblacklist.index');
    Route::get('/personblacklist/create', [PersonBlackListController::class, 'create'])->name('personblacklist.create');
    Route::post('/personblacklist/insert', [PersonBlackListController::class, 'insert'])->name('personblacklist.insert');
    Route::get('/personblacklist/edit/{id}', [PersonBlackListController::class, 'edit'])->name('personblacklist.edit');
    Route::post('/personblacklist/updates/{id}', [PersonBlackListController::class, 'updates'])->name('personblacklist.updates');
    Route::get('/personblacklist/delete/{id}', [PersonBlackListController::class, 'deletes'])->name('personblacklist.delete');
    Route::post('/personblacklist/destroy/{id}', [PersonBlackListController::class, 'destroy'])->name('personblacklist.destroy');
    Route::get('/personblacklist/search-persons', [PersonBlackListController::class, 'searchPersons'])->name('personblacklist.searchPersons');

    // Person Special Cases
    Route::get('/personspecialcase', [PersonSpecialCaseController::class, 'index'])->name('personspecialcase.index');
    Route::get('/personspecialcase/create', [PersonSpecialCaseController::class, 'create'])->name('personspecialcase.create');
    Route::post('/personspecialcase/insert', [PersonSpecialCaseController::class, 'insert'])->name('personspecialcase.insert');
    Route::get('/personspecialcase/edit/{id}', [PersonSpecialCaseController::class, 'edit'])->name('personspecialcase.edit');
    Route::post('/personspecialcase/updates/{id}', [PersonSpecialCaseController::class, 'updates'])->name('personspecialcase.updates');
    Route::get('/personspecialcase/delete/{id}', [PersonSpecialCaseController::class, 'deletes'])->name('personspecialcase.delete');
    Route::post('/personspecialcase/destroy/{id}', [PersonSpecialCaseController::class, 'destroy'])->name('personspecialcase.destroy');
    Route::get('/personspecialcase/search-persons', [PersonSpecialCaseController::class, 'searchPersons'])->name('personspecialcase.searchPersons');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/create', [NotificationController::class, 'create']);
    Route::post('/notifications/send', [NotificationController::class, 'send'])->name('notifications.send');


    // Migrate New Enrolments
    Route::get('/migrate-new-enrolments/{qetaaID}', array('as'=> 'person.migrate-new-enrolments', 'uses'=> 'App\Http\Controllers\MigrateNewEnrolments@migrate'));
        Route::get('/new-enrolments/migrations', [PersonNewController::class, 'indexNewEnrolmentsAndMigrations'])->name('person.new-enrolments-migrate-index');
});






Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminQetaa|AdminSecretary|Secretary|AdminFinance'])->group(function () {

Route::get('/new-enrolments/show/qetaa/{id}', [PersonNewController::class, 'showNewEnrolmentsByQetaaID'])->name('person.new-enrolments-show-qetaa');
Route::get('/new-enrolments/show/{id}', [PersonNewController::class, 'showNewEnrolments'])->name('person.new-enrolments-show');
Route::get('/new-enrolments/person/approve/{id}', [PersonNewController::class, 'approveNewEnrolments'])->name('person.new-enrolments-approve');
Route::get('/new-enrolments/person/approve-again/{id}', [PersonNewController::class, 'approveAgainNewEnrolments'])->name('person.new-enrolments-approve-again');
Route::get('/new-enrolments/person/delete/{id}', [PersonNewController::class, 'deleteNewEnrolments'])->name('person.new-enrolments-delete');
Route::delete('/new-enrolments/person/destroy/{id}', [PersonNewController::class, 'destroyNewEnrolments'])->name('person.new-enrolments-destroy');




    // New Enrolments (admin lists)
    Route::get('/new-enrolments', [PersonNewController::class, 'indexNewEnrolments'])->name('person.new-enrolments-index');

    Route::get('/new-enrolments/analytics', [PersonNewController::class, 'analyticsNewEnrolments'])->name('person.new-enrolments-analytics');
    Route::get('/new-enrolments/count/marahel', [PersonNewController::class, 'countNewEnrolmentsMarahel'])->name('person.new-enrolments-marahel-count');
    Route::get('/new-enrolments/count/qetaat', [PersonNewController::class, 'countNewEnrolmentsQetaat'])->name('person.new-enrolments-qetaat-count');

    Route::get('/new-enrolments/edit/{id}', [PersonNewController::class, 'editNewEnrolments'])->name('person.new-enrolments-edit');
    Route::put('/new-enrolments/update/{id}', [PersonNewController::class, 'updateNewEnrolments']) ->name('person.new-enrolments-update');


      // Max Limits (duplicate existing routes in your file; keep only one set in your real file)
    Route::get('/max-limits', [LiveFormMaxLimitsController::class, 'index'])->name('max-limits.index');
    Route::get('/max-limits/add', [LiveFormMaxLimitsController::class, 'create'])->name('max-limits.create');
    Route::post('/max-limits/insert', [LiveFormMaxLimitsController::class, 'insert'])->name('max-limits.insert');
    Route::get('/max-limits/edit/{id}/{sana_id}', [LiveFormMaxLimitsController::class, 'edit'])->name('max-limits.edit');
    Route::patch('/max-limits/update/{id}/{sana_id}', [LiveFormMaxLimitsController::class, 'updates'])->name('max-limits.update');
    Route::get('/max-limits/delete/{id}/{sana_id}', [LiveFormMaxLimitsController::class, 'deletes'])->name('max-limits.delete');
    Route::delete('/max-limits/destroy/{id}/{sana_id}', [LiveFormMaxLimitsController::class, 'destroy'])->name('max-limits.destroy');


       // Entry Questions
    Route::get('/entry-questions', [MarhalaEntryQuestionsController::class, 'index'])->name('entry-questions.index');
    Route::get('/entry-questions/add', [MarhalaEntryQuestionsController::class, 'create'])->name('entry-questions.create');
    Route::post('/entry-questions/insert', [MarhalaEntryQuestionsController::class, 'insert'])->name('entry-questions.insert');
    Route::get('/entry-questions/edit/{id}', [MarhalaEntryQuestionsController::class, 'edit'])->name('entry-questions.edit');
    Route::patch('/entry-questions/update/{id}', [MarhalaEntryQuestionsController::class, 'updates'])->name('entry-questions.update');
    Route::get('/entry-questions/delete/{id}', [MarhalaEntryQuestionsController::class, 'deletes'])->name('entry-questions.delete');
    Route::delete('/entry-questions/destroy/{id}', [MarhalaEntryQuestionsController::class, 'destroy'])->name('entry-questions.destroy');

});


/*
|--------------------------------------------------------------------------
| Secretary (SuperAdmin|Finance)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'checkAuth:SuperAdmin|Finance|AdminFinance'])->group(function () {


// Route::group(['prefix' => 'finance'], function () {

//     /* =========================
//        MAIN LIST
//        ========================= */
//     Route::get('/', 
//         [PersonSeasonEventFinanceController::class, 'index']
//     )->name('finance.index');

//     /* =========================
//        CREATE / BOOKING
//        ========================= */
//     Route::get('/create', 
//         [PersonSeasonEventFinanceController::class, 'create']
//     )->name('finance.create');

//     Route::post('/insert', 
//         [PersonSeasonEventFinanceController::class, 'insert']
//     )->name('finance.insert');

//     /* =========================
//        SHOW DETAILS + PAYMENTS
//        ========================= */
//     Route::get('/show/{id}', 
//         [PersonSeasonEventFinanceController::class, 'show']
//     )->name('finance.show');

//     /* =========================
//        ADD PAYMENT
//        ========================= */
//     Route::post('/payment/{id}', 
//         [PersonSeasonEventFinanceController::class, 'addPayment']
//     )->name('finance.payment');

//     /* =========================
//        CANCEL (ADMIN DECIDES REFUND)
//        ========================= */
//     Route::get('/cancel/{id}', 
//         [PersonSeasonEventFinanceController::class, 'cancelForm']
//     )->name('finance.cancel.form');

//     Route::post('/cancel/{id}', 
//         [PersonSeasonEventFinanceController::class, 'cancel']
//     )->name('finance.cancel');

// });




// Route::prefix('season-event-finance')->group(function () {

//     Route::get('/', [SeasonEventFinanceController::class, 'index'])
//         ->name('seasonEventFinance.index');

//     Route::get('/create', [SeasonEventFinanceController::class, 'create'])
//         ->name('seasonEventFinance.create');

//     Route::post('/insert', [SeasonEventFinanceController::class, 'insert'])
//         ->name('seasonEventFinance.insert');

//     Route::get('/edit/{id}', [SeasonEventFinanceController::class, 'edit'])
//         ->name('seasonEventFinance.edit');

//     Route::post('/update/{id}', [SeasonEventFinanceController::class, 'update'])
//         ->name('seasonEventFinance.update');

//     Route::get('/delete/{id}', [SeasonEventFinanceController::class, 'delete'])
//         ->name('seasonEventFinance.delete');

//     Route::post('/destroy/{id}', [SeasonEventFinanceController::class, 'destroy'])
//         ->name('seasonEventFinance.destroy');

//     Route::get('/get-events-for-season',
//         [SeasonEventFinanceController::class, 'getEventsForSeason']
//     )->name('seasonEventFinance.getEventsForSeason');

// });



// Route::prefix('booking')->group(function () {

//     // Step 1: choose event (only finance-enabled)
//     Route::get('/create', [BookingController::class, 'create'])
//         ->name('booking.create');

//     // Step 2: event selected -> search persons page
//     Route::get('/event/{seasonEventID}', [BookingController::class, 'choosePerson'])
//         ->name('booking.choosePerson');

//     // AJAX spotlight search
//     Route::get('/search-person', [BookingController::class, 'searchPerson'])
//         ->name('booking.searchPerson');

//     // Step 3: person selected -> booking details page
//     Route::get('/event/{seasonEventID}/person/{personID}', [BookingController::class, 'details'])
//         ->name('booking.details');

//     // Save booking + optional payment (then invoice)
//     Route::post('/store', [BookingController::class, 'store'])
//         ->name('booking.store');

//     // Print invoice
//     Route::get('/invoice/{personSeasonEventID}', [BookingController::class, 'invoice'])
//         ->name('booking.invoice');

// Route::prefix('transactions')->group(function () {

//     Route::get('/edit/{id}', [BookingController::class, 'editTransaction'])->name('transactions.edit');
//     Route::post('/update/{id}', [BookingController::class, 'updateTransaction'])->name('transactions.update');

//     Route::get('/delete/{id}', [BookingController::class, 'deleteTransaction'])->name('transactions.delete');
//     Route::post('/destroy/{id}', [BookingController::class, 'destroyTransaction'])->name('transactions.destroy');

//     // ✅ Refund
//     Route::get('/refund/{id}', [BookingController::class, 'refundForm'])->name('transactions.refund.form');
//     Route::post('/refund/{id}', [BookingController::class, 'refundStore'])->name('transactions.refund.store');

//     // ✅ Print invoice for a specific payment transaction
//     Route::get('/invoice/{id}', [BookingController::class, 'invoiceByTransaction'])->name('transactions.invoice');
// });

 
// });






Route::prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [SeasonEventFinanceController::class, 'index'])->name('index');
    Route::get('/create', [SeasonEventFinanceController::class, 'create'])->name('create');
    Route::get('/get-events-for-season', [SeasonEventFinanceController::class, 'getEventsForSeason'])->name('getEventsForSeason');
    Route::post('/store', [SeasonEventFinanceController::class, 'store'])->name('store');
    Route::get('/edit/{id}', [SeasonEventFinanceController::class, 'edit'])->name('edit');
    Route::post('/update/{id}', [SeasonEventFinanceController::class, 'update'])->name('update');
    Route::get('/delete/{id}', [SeasonEventFinanceController::class, 'delete'])->name('delete');
    Route::post('/destroy/{id}', [SeasonEventFinanceController::class, 'destroy'])->name('destroy');
});



Route::prefix('event-booking-finance')->name('eventBookingFinance.')->group(function () {
    Route::get('/selector', [SeasonEventBookingFinanceController::class, 'selector'])->name('selector');
    Route::get('/get-events-with-plan', [SeasonEventBookingFinanceController::class, 'getEventsWithPlan'])->name('getEventsWithPlan');

    Route::get('/event/{seasonEventID}', [SeasonEventBookingFinanceController::class, 'index'])->name('index');

    Route::get('/event/{seasonEventID}/create', [SeasonEventBookingFinanceController::class, 'create'])->name('create');
    Route::get('/event/{seasonEventID}/search-eligible-persons', [SeasonEventBookingFinanceController::class, 'searchEligiblePersons'])->name('searchEligiblePersons');
    Route::post('/event/{seasonEventID}/store', [SeasonEventBookingFinanceController::class, 'store'])->name('store');

    Route::get('/booking/{bookingID}/installment/create', [SeasonEventBookingFinanceController::class, 'createInstallment'])->name('createInstallment');
    Route::post('/booking/{bookingID}/installment/store', [SeasonEventBookingFinanceController::class, 'storeInstallment'])->name('storeInstallment');

    Route::get('/payment/{paymentID}/edit-last', [SeasonEventBookingFinanceController::class, 'editLastPayment'])->name('editLastPayment');
    Route::post('/payment/{paymentID}/update-last', [SeasonEventBookingFinanceController::class, 'updateLastPayment'])->name('updateLastPayment');

    Route::get('/booking/{bookingID}/refund', [SeasonEventBookingFinanceController::class, 'refundPage'])->name('refundPage');
    Route::post('/booking/{bookingID}/refund', [SeasonEventBookingFinanceController::class, 'refundStore'])->name('refundStore');

    Route::get('/booking/{bookingID}/partial-refund', [SeasonEventBookingFinanceController::class, 'partialRefundPage'])
            ->name('partialRefundPage');

    Route::post('/booking/{bookingID}/partial-refund', [SeasonEventBookingFinanceController::class, 'partialRefundStore'])
        ->name('partialRefundStore');
    Route::get('/receipt/{paymentID}/print', [SeasonEventBookingFinanceController::class, 'printReceipt'])->name('printReceipt');

    Route::get('/event/{seasonEventID}/export/today', [SeasonEventBookingFinanceController::class, 'exportToday'])
        ->name('exportToday');

    Route::get('/event/{seasonEventID}/export/all', [SeasonEventBookingFinanceController::class, 'exportAll'])
        ->name('exportAll');


    Route::get('/booking/{bookingID}/show', [SeasonEventBookingFinanceController::class, 'show'])
    ->name('show');

    Route::post('/booking/{bookingID}/update-shirt-size', [SeasonEventBookingFinanceController::class, 'updateShirtSize'])
        ->name('updateShirtSize');
    });




Route::prefix('event-waiting-list')->name('eventWaitingList.')->group(function () {
    Route::get('/selector', [SeasonEventWaitingListController::class, 'selector'])->name('selector');
    Route::get('/events', [SeasonEventWaitingListController::class, 'getEvents'])->name('events');

    Route::get('/{seasonEventID}', [SeasonEventWaitingListController::class, 'index'])->name('index');
    Route::get('/{seasonEventID}/search-eligible-persons', [SeasonEventWaitingListController::class, 'searchEligiblePersons'])->name('searchEligiblePersons');
    Route::post('/{seasonEventID}/store', [SeasonEventWaitingListController::class, 'store'])->name('store');

    Route::get('/delete/{waitingListID}', [SeasonEventWaitingListController::class, 'deletePage'])->name('deletePage');
    Route::delete('/delete/{waitingListID}', [SeasonEventWaitingListController::class, 'destroy'])->name('destroy');
});

});






/*
|--------------------------------------------------------------------------
| Secretary (SuperAdmin|Secretary)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminSecretary|Secretary'])->group(function () {
    Route::get('/secretary',                [SecretaryController::class, 'index'])->name('secretary.index');
    Route::get('/secretary/add',            [SecretaryController::class, 'create'])->name('secretary.create');
    Route::post('/secretary/insert',        [SecretaryController::class, 'insert'])->name('secretary.insert');

    Route::get('/secretary/edit/{id}',      [SecretaryController::class, 'edit'])->name('secretary.edit');
    Route::patch('/secretary/update/{id}',  [SecretaryController::class, 'updates'])->name('secretary.update');

    Route::get('/secretary/download/{id}',  [SecretaryController::class, 'download'])->name('secretary.download');

    Route::get('/secretary/delete/{id}',    [SecretaryController::class, 'deletes'])->name('secretary.delete');
    Route::delete('/secretary/destroy/{id}',[SecretaryController::class, 'destroy'])->name('secretary.destroy');

    Route::post('/secretary/upload',        [SecretaryController::class, 'upload'])->name('secretary.upload');

    Route::get('/admin/place-bookings', [AdminPlaceBookingController::class, 'index'])->name('admin.place_bookings.index');
    Route::get('/admin/place-bookings/{id}', [AdminPlaceBookingController::class, 'show'])->name('admin.place_bookings.show');

    Route::post('/admin/place-bookings/{id}/approve', [AdminPlaceBookingController::class, 'approve'])->name('admin.place_bookings.approve');
    Route::post('/admin/place-bookings/{id}/reject',  [AdminPlaceBookingController::class, 'reject'])->name('admin.place_bookings.reject');
    Route::post('/admin/place-bookings/{id}/approve-edit', [AdminPlaceBookingController::class, 'approveWithEdit'])->name('admin.place_bookings.approve_edit');

Route::get('/person', [PersonNewController::class, 'index'])->name('person.index');
    
});



Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminInventory|Inventory'])->group(function () {


    // Admin Custody
    Route::get('/admin/custody-requests', [AdminCustodyRequestController::class, 'index'])->name('admin.custody_requests.index');
    Route::get('/admin/custody-requests/{id}', [AdminCustodyRequestController::class, 'show'])->name('admin.custody_requests.show');
    Route::post('/admin/custody-requests/{id}/approve', [AdminCustodyRequestController::class, 'approve'])->name('admin.custody_requests.approve');
    Route::post('/admin/custody-requests/{id}/reject', [AdminCustodyRequestController::class, 'reject'])->name('admin.custody_requests.reject');
    

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/add', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory/insert', [InventoryController::class, 'insert'])->name('inventory.insert');
    Route::get('/inventory/edit/{id}', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::patch('/inventory/update/{id}', [InventoryController::class, 'updates'])->name('inventory.update');
    Route::get('/inventory/delete/{id}', [InventoryController::class, 'deletes'])->name('inventory.delete');
    Route::delete('/inventory/destroy/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    // Inventory Issue
    Route::get('/inventory-issue', [InventoryIssueController::class, 'index'])->name('inventory-issue.index');
    Route::get('/inventory-issue/getEventsForSeason', [InventoryIssueController::class, 'getEventsForSeason'])->name('inventory-issue.getEventsForSeason');
    Route::post('/inventory-issue/generate', [InventoryIssueController::class, 'generate'])->name('inventory-issue.generate');




});




/*
|--------------------------------------------------------------------------
| Authenticated (Any User)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Home
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // Logout
    Route::post('/logout', [LogoutController::class, 'perform'])->name('logout');

    // Profile
    Route::get('/profile', [PersonProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [PersonProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [PersonProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [PersonProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Custody Requests
    Route::get('/custody-requests/create', [CustodyRequestController::class, 'create'])->name('custody_requests.create');
    Route::post('/custody-requests', [CustodyRequestController::class, 'store'])->name('custody_requests.store');
    Route::get('/custody-requests/my', [CustodyRequestController::class, 'my'])->name('custody_requests.my');
    Route::get('/custody-requests/{id}', [CustodyRequestController::class, 'show'])->name('custody_requests.show');
    Route::get('/custody-requests/{id}/edit', [CustodyRequestController::class, 'edit'])->name('custody_requests.edit');
    Route::patch('/custody-requests/{id}', [CustodyRequestController::class, 'update'])->name('custody_requests.update');
    Route::delete('/custody-requests/{id}', [CustodyRequestController::class, 'destroy'])->name('custody_requests.destroy');

    // Media
    Route::get('/media/events', [MediaController::class, 'getEventsForSeason'])->name('media.getEventsForSeason');
    Route::get('/media/pages', [MediaController::class, 'pages'])->name('media.pages');
    Route::get('/media/pages/events', [MediaController::class, 'getEventsForPages'])->name('media.getEventsForPages');
    Route::get('/media/pages/media', [MediaController::class, 'getMediaForEvent'])->name('media.getMediaForEvent');

    // Curricula
    Route::get('/curricula', [CurriculaController::class, 'index'])->name('curricula.index');
    Route::get('/curricula/add', [CurriculaController::class, 'create'])->name('curricula.create');
    Route::post('/curricula/insert', [CurriculaController::class, 'insert'])->name('curricula.insert');
    Route::get('/curricula/edit/{id}', [CurriculaController::class, 'edit'])->name('curricula.edit');
    Route::patch('/curricula/update/{id}', [CurriculaController::class, 'updates'])->name('curricula.update');
    Route::get('/curricula/delete/{id}', [CurriculaController::class, 'deletes'])->name('curricula.delete');
    Route::delete('/curricula/destroy/{id}', [CurriculaController::class, 'destroy'])->name('curricula.destroy');
    Route::get('/curricula/download/{id}', [CurriculaController::class, 'download'])->name('curricula.download');

    // Attendance
    Route::get('/attendance/manage', [AttendanceController::class, 'manage'])->name('attendance.manage');
    Route::post('/attendance/save/{seasonEventId}', [AttendanceController::class, 'save'])->name('attendance.save');

    // Place Bookings
    Route::get('/place-bookings/create', [PlaceBookingController::class, 'create'])->name('place_bookings.create');
    Route::post('/place-bookings', [PlaceBookingController::class, 'store'])->name('place_bookings.store');
    Route::get('/place-bookings/my', [PlaceBookingController::class, 'my'])->name('place_bookings.my');
    Route::get('/place-bookings/{id}', [PlaceBookingController::class, 'show'])->name('place_bookings.show');
    Route::get('/place-bookings/{id}/edit', [PlaceBookingController::class, 'edit'])->name('place_bookings.edit');
    Route::patch('/place-bookings/{id}', [PlaceBookingController::class, 'update'])->name('place_bookings.update');
    Route::delete('/place-bookings/{id}', [PlaceBookingController::class, 'destroy'])->name('place_bookings.destroy');

    // AJAX dropdown
    Route::get('/ajax/places/{locationId}', [PlaceBookingController::class, 'placesByLocation'])
        ->name('ajax.places_by_location');


    Route::get('/games', [GamesController::class, 'index'])->name('games.index');
    Route::get('/games/create', [GamesController::class, 'create'])->name('games.create');
    Route::post('/games/insert', [GamesController::class, 'insert'])->name('games.insert');
    Route::get('/games/edit/{id}', [GamesController::class, 'edit'])->name('games.edit');
    Route::post('/games/update/{id}', [GamesController::class, 'updates'])->name('games.updates');
    Route::get('/games/delete/{id}', [GamesController::class, 'deletes'])->name('games.delete');
    Route::post('/games/destroy/{id}', [GamesController::class, 'destroy'])->name('games.destroy');
    Route::get('/games/show/{id}', [GamesController::class, 'show'])->name('games.show');
   
    Route::get('/person', [PersonNewController::class, 'index'])->name('person.index');

    Route::get('/new-enrolments/migrations', [PersonNewController::class, 'indexNewEnrolmentsAndMigrations'])->name('person.new-enrolments-migrate-index');
    Route::get('/new-enrolments/analytics', [PersonNewController::class, 'analyticsNewEnrolments'])->name('person.new-enrolments-analytics');
});