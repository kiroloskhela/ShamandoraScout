<?php

use App\Http\Controllers\PersonController;
use App\Http\Controllers\FeedbackController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ForgotPasswordController;

use App\Http\Controllers\HomeController;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


        Route::get('/cards', function () {return view('cards');});
        Route::get('/charts', function () {return view('charts');});
        Route::get('/blank', function () {return view('blank');});
        Route::get('/buttons', function () {return view('buttons');});
        Route::get('/utilities-animation', function () {return view('utilities-animation');});

        Route::get('/adv', function () {
            return view('Adv');
        });

//General UI Routes
Route::middleware(['auth','checkAuth:SuperAdmin|Admin'])->group(function(){
    Route::get('/', [HomeController::class, 'index'])->name('home');
});




Route::middleware(['auth','checkAuth:SuperAdmin|Secretary'])->group(function(){
Route::get('/secretary', array('as'=> 'secretary.index', 'uses'=> 'App\Http\Controllers\SecretaryController@index'));
Route::get('/secretary/add', array('as' => 'secretary.create', 'uses' =>'App\Http\Controllers\SecretaryController@create'));
Route::post('/secretary/insert', array('as' => 'secretary.insert', 'uses' => 'App\Http\Controllers\SecretaryController@insert'));
Route::get('/secretary/edit/{id}', array('as' => 'secretary.edit', 'uses' => 'App\Http\Controllers\SecretaryController@edit'));
Route::patch('/secretary/update/{id}', array('as'=> 'secretary.update', 'uses'=> 'App\Http\Controllers\SecretaryController@updates'));
Route::get('/secretary/delete/{id}', array('as'=> 'secretary.delete', 'uses'=>'App\Http\Controllers\SecretaryController@deletes'));
Route::delete('/secretary/destroy/{id}', array('as'=> 'secretary.destroy', 'uses'=>'App\Http\Controllers\SecretaryController@destroy'));
Route::post('/secretary/upload', array('as'=> 'secretary.upload', 'uses'=>'App\Http\Controllers\SecretaryController@upload'));



});







//General Registration and Login Routes
Route::get('/login-auth', array('as'=>'login-auth', 'uses'=>'App\Http\Controllers\LoginController@show'));
Route::post('/login', array('as'=>'login', 'uses'=>'App\Http\Controllers\LoginController@login'));

// routes/web.php


Route::get('/forgot-password', function () {
    return view('forgot-password.form');   // ⬅️ مسار الفيو الجديد
})->name('forgot-password.form');

// معالجة الفورم
Route::post('/forgot-password', [ForgotPasswordController::class, 'handle'])
    ->name('forgot-password.handle');



Route::get('/register', function () {return view('register');});


//Person Tables Routes
//Route::get('/tables', function () {return view('tables');});

//Routes for Person Information
//Route::get('/createperson', function () {return view('createperson');});
//Route::get('/person', 'App\Http\Controllers\PersonController@index');
//Route::get('/insertperson','App\Http\Controllers\PersonController@insert');
//Route::get('/createperson','App\Http\Controllers\PersonController@createPersonController');
//Route::post('/submitPerson','App\Http\Controllers\PersonController@submitPersonController');


//Route Profile




//New Routes for Person Information for Live Form
Route::get('/de7k', array('as'=>'person.de7k','uses'=>'App\Http\Controllers\PersonNewController@showLiveForm'));
Route::get('/liveform', array('as'=>'person.liveform', 'uses'=>'App\Http\Controllers\PersonNewController@createLiveForm'));
Route::post('/liveform/insert', array('as'=>'person.liveform-insert', 'uses'=>'App\Http\Controllers\PersonNewController@insertLiveForm'));
Route::get('/liveform/person/add', array('as' => 'person.liveform-create', 'uses' =>'App\Http\Controllers\PersonNewController@createNewPersonLiveForm'));
Route::post('/liveform/person/insert', array('as' => 'person.liveform-insert-person', 'uses' => 'App\Http\Controllers\PersonNewController@insertNewPersonLiveForm'));
Route::get('/liveform/person/entry-questions/insert/{id}', array('as'=> 'person.liveform-entry-questions', 'uses'=>'App\Http\Controllers\PersonNewController@getQuestionsLiveForm'));
Route::post('/liveform/person/entry-questions/submit', array('as'=> 'person.entry-questions-submit-liveform', 'uses'=>'App\Http\Controllers\PersonNewController@submitQuestionsLiveForm'));

Route::get('/liveform/person/delete/{id}', array('as'=> 'person.liveform-delete', 'uses'=>'App\Http\Controllers\PersonNewController@deletesLiveForm'));
Route::delete('/liveform/person/destroy/{id}', array('as'=> 'person.liveform-destroy', 'uses'=>'App\Http\Controllers\PersonNewController@destroyLiveForm'));
Route::get('/liveform/person/entry-questions/insert/{id}', array('as'=> 'person.entry-questions-liveform', 'uses'=>'App\Http\Controllers\PersonNewController@getLiveFormQuestions'));
Route::post('/liveform/person/entry-questions/submit', array('as'=> 'person.entry-questions-submit-liveform', 'uses'=>'App\Http\Controllers\PersonNewController@submitLiveFormQuestions'));

Route::get('/liveform/apologize', function() {return view('person.liveform-limit-exceeded');});
Route::get('/liveform/finalize', function(){return view('person.liveform-finalize');});



Route::get('/new-enrolments/show/qetaa/{id}', array('as'=> 'person.new-enrolments-show-qetaa', 'uses'=>'App\Http\Controllers\PersonNewController@showNewEnrolmentsByQetaaID'));
Route::get('/new-enrolments/show/{id}', array('as'=> 'person.new-enrolments-show', 'uses'=>'App\Http\Controllers\PersonNewController@showNewEnrolments'));
Route::get('/new-enrolments/person/approve/{id}', array('as'=>'person.new-enrolments-approve', 'uses'=>'App\Http\Controllers\PersonNewController@approveNewEnrolments'));
Route::get('/new-enrolments/person/approve-again/{id}', array('as'=>'person.new-enrolments-approve-again', 'uses'=>'App\Http\Controllers\PersonNewController@approveAgainNewEnrolments'));
Route::get('/new-enrolments/person/delete/{id}', array('as'=> 'person.new-enrolments-delete', 'uses'=>'App\Http\Controllers\PersonNewController@deleteNewEnrolments'));
Route::delete('/new-enrolments/person/destroy/{id}', array('as'=> 'person.new-enrolments-destroy', 'uses'=>'App\Http\Controllers\PersonNewController@destroyNewEnrolments'));

//Attendance Routes
// Route::middleware(['auth','checkAuth:SuperAdmin|Admin|Khadem'])->group(function(){
//         Route::get('/attendance', array('as'=> 'attendance.index', 'uses'=> 'App\Http\Controllers\AttendanceController@index'));
//         Route::get('/attendance/add', array('as' => 'attendance.create', 'uses' =>'App\Http\Controllers\AttendanceController@create'));
//         Route::post('/attendance/insert', array('as' => 'attendance.insert', 'uses' => 'App\Http\Controllers\AttendanceController@insert'));
// });


Route::middleware(['auth','checkAuth:SuperAdmin'])->group(function(){
//Routes for Roles
Route::get('/role', array('as'=> 'role.index', 'uses'=> 'App\Http\Controllers\RoleController@index'));
Route::get('/role/add', array('as' => 'role.create', 'uses' =>'App\Http\Controllers\RoleController@create'));
Route::post('/role/insert', array('as' => 'role.insert', 'uses' => 'App\Http\Controllers\RoleController@insert'));
Route::get('/role/edit/{id}', array('as' => 'role.edit', 'uses' => 'App\Http\Controllers\RoleController@edit'));
Route::patch('/role/update/{id}', array('as'=> 'role.update', 'uses'=> 'App\Http\Controllers\RoleController@updates'));
Route::get('/role/delete/{id}', array('as'=> 'role.delete', 'uses'=>'App\Http\Controllers\RoleController@deletes'));
Route::delete('/role/destroy/{id}', array('as'=> 'role.destroy', 'uses'=>'App\Http\Controllers\RoleController@destroy'));

//Routes for Person Information for all system (Show ALL, Insert, Show by ID, Edit)
Route::get('/person/ShowPersons', array('as'=> 'person.ShowPersons', 'uses'=>'App\Http\Controllers\PersonNewController@ShowPersons'));


//Routes for Person Roles Assignment
Route::get('/person-role', array('as'=> 'person-role.index', 'uses'=> 'App\Http\Controllers\PersonRoleController@index'));
Route::get('/person-role/add', array('as' => 'person-role.create', 'uses' =>'App\Http\Controllers\PersonRoleController@create'));
Route::post('/person-role/insert', array('as' => 'person-role.insert', 'uses' => 'App\Http\Controllers\PersonRoleController@insert'));
Route::get('/person-role/edit/{id}', array('as' => 'person-role.edit', 'uses' => 'App\Http\Controllers\PersonRoleController@edit'));
Route::patch('/person-role/update/{id}', array('as'=> 'person-role.update', 'uses'=> 'App\Http\Controllers\PersonRoleController@updates'));
Route::get('/person-role/delete/{id}', array('as'=> 'person-role.delete', 'uses'=>'App\Http\Controllers\PersonRoleController@deletes'));
Route::delete('/person-role/destroy/{id}', array('as'=> 'person-role.destroy', 'uses'=>'App\Http\Controllers\PersonRoleController@destroy'));

//Routes for Group Person Roles Assignment
Route::get('/group-person/add-khadem', array('as' => 'group-person.create-khadem', 'uses' =>'App\Http\Controllers\GroupPersonController@createKhadem'));
Route::get('/group-person/delete/{id}', array('as'=> 'group-person.delete', 'uses'=>'App\Http\Controllers\GroupPersonController@deletes'));
Route::delete('/group-person/destroy/{id}', array('as'=> 'group-person.destroy', 'uses'=>'App\Http\Controllers\GroupPersonController@destroy'));




});

Route::middleware(['auth'])->group(function(){
        Route::get('/group-person', array('as'=> 'group-person.index', 'uses'=> 'App\Http\Controllers\GroupPersonController@index'));
        Route::get('/group-person/add-makhdoom', array('as' => 'group-person.create-makhdoom', 'uses' =>'App\Http\Controllers\GroupPersonController@createMakhdoom'));
        Route::post('/group-person/insert', array('as' => 'group-person.insert', 'uses' => 'App\Http\Controllers\GroupPersonController@insert'));
        Route::get('/group-person/edit/{id}', array('as' => 'group-person.edit', 'uses' => 'App\Http\Controllers\GroupPersonController@edit'));
        Route::patch('/group-person/update/{id}', array('as'=> 'group-person.update', 'uses'=> 'App\Http\Controllers\GroupPersonController@updates'));
});


Route::middleware(['auth','checkAuth:SuperAdmin|Admin|Khadem'])->group(function(){

//Routes for Person Information for all system (Show ALL, Insert, Show by ID, Edit)
Route::get('/person', array('as'=> 'person.index', 'uses'=>'App\Http\Controllers\PersonNewController@index'));
Route::get('/person/add', array('as' => 'person.create', 'uses' =>'App\Http\Controllers\PersonNewController@create'));
Route::post('/person/insert', array('as' => 'person.insert', 'uses' => 'App\Http\Controllers\PersonNewController@insert'));
Route::get('/person/entry-questions/insert/{id}', array('as'=> 'person.entry-questions', 'uses'=>'App\Http\Controllers\PersonNewController@getQuestions'));
Route::post('/person/entry-questions/submit', array('as'=> 'person.entry-questions-submit', 'uses'=>'App\Http\Controllers\PersonNewController@submitQuestions'));
Route::get('/person/show/{id}', array('as'=> 'person.show', 'uses'=>'App\Http\Controllers\PersonNewController@show'));
Route::get('/person/edit/{id}', array('as' => 'person.edit', 'uses' => 'App\Http\Controllers\PersonNewController@edit'));
Route::patch('/person/update/{id}', array('as'=> 'person.update', 'uses'=> 'App\Http\Controllers\PersonNewController@updates'));
});

Route::middleware(['auth','checkAuth:SuperAdmin|Admin'])->group(function(){

//Routes for New Enrolments
Route::get('/new-enrolments', array('as'=> 'person.new-enrolments-index', 'uses'=>'App\Http\Controllers\PersonNewController@indexNewEnrolments'));
Route::get('/new-enrolments/migrations', array('as'=> 'person.new-enrolments-migrate-index', 'uses'=>'App\Http\Controllers\PersonNewController@indexNewEnrolmentsAndMigrations'));
Route::get('/new-enrolments/analytics', array('as'=>'person.new-enrolments-analytics', 'uses'=>'App\Http\Controllers\PersonNewController@analyticsNewEnrolments'));
Route::get('/new-enrolments/count/marahel', array('as'=>'person.new-enrolments-marahel-count','uses'=>'App\Http\Controllers\PersonNewController@countNewEnrolmentsMarahel'));
Route::get('/new-enrolments/count/qetaat', array('as'=>'person.new-enrolments-qetaat-count','uses'=>'App\Http\Controllers\PersonNewController@countNewEnrolmentsQetaat'));

//Routes for Migrating New Enrolments to Original System
Route::get('/migrate-new-enrolments/{qetaaID}', array('as'=> 'person.migrate-new-enrolments', 'uses'=> 'App\Http\Controllers\MigrateNewEnrolments@migrate'));

//Routes for Deleting Persons from Database
Route::get('/person/delete/{id}', array('as'=> 'person.delete', 'uses'=>'App\Http\Controllers\PersonNewController@deletes'));
Route::delete('/person/destroy/{id}', array('as'=> 'person.destroy', 'uses'=>'App\Http\Controllers\PersonNewController@destroy'));


//Routes for Event
Route::get('/event', array('as' => 'event.index', 'uses' => 'App\Http\Controllers\EventController@index'));
Route::get('/event/add-recursive', array('as' => 'event.create-recursive', 'uses' =>'App\Http\Controllers\EventController@createRecursive'));
Route::post('/event/insert-recursive', array('as' => 'event.insert-recursive', 'uses' => 'App\Http\Controllers\EventController@insertRecursive'));
Route::get('/event/add', array('as' => 'event.create', 'uses' =>'App\Http\Controllers\EventController@create'));
Route::post('/event/insert', array('as' => 'event.insert', 'uses' => 'App\Http\Controllers\EventController@insert'));
Route::get('/event/edit/{id}', array('as' => 'event.edit', 'uses' => 'App\Http\Controllers\EventController@edit'));
Route::patch('/event/update/{id}', array('as'=> 'event.update', 'uses'=> 'App\Http\Controllers\EventController@updates'));
Route::get('/event/delete/{id}', array('as'=> 'event.delete', 'uses'=>'App\Http\Controllers\EventController@deletes'));
Route::delete('/event/destroy/{id}', array('as'=> 'event.destroy', 'uses'=>'App\Http\Controllers\EventController@destroy'));

//Routes for Event Types
Route::get('/event-type', array('as' => 'event-type.index', 'uses' => 'App\Http\Controllers\EventTypeController@index'));
Route::get('/event-type/add', array('as' => 'event-type.create', 'uses' =>'App\Http\Controllers\EventTypeController@create'));
Route::post('/event-type/insert', array('as' => 'event-type.insert', 'uses' => 'App\Http\Controllers\EventTypeController@insert'));
Route::get('/event-type/edit/{id}', array('as' => 'event-type.edit', 'uses' => 'App\Http\Controllers\EventTypeController@edit'));
Route::patch('/event-type/update/{id}', array('as'=> 'event-type.update', 'uses'=> 'App\Http\Controllers\EventTypeController@updates'));
Route::get('/event-type/delete/{id}', array('as'=> 'event-type.delete', 'uses'=>'App\Http\Controllers\EventTypeController@deletes'));
Route::delete('/event-type/destroy/{id}', array('as'=> 'event-type.destroy', 'uses'=>'App\Http\Controllers\EventTypeController@destroy'));


//Routes for Media
Route::get('/media', array('as' => 'media.index', 'uses' => 'App\Http\Controllers\MediaController@index'));
Route::get('/media/add', array('as' => 'media.create', 'uses' =>'App\Http\Controllers\MediaController@create'));
Route::post('/media/insert', array('as' => 'media.insert', 'uses' => 'App\Http\Controllers\MediaController@insert'));
Route::get('/media/edit/{id}', array('as' => 'media.edit', 'uses' => 'App\Http\Controllers\MediaController@edit'));
Route::patch('/media/update/{id}', array('as'=> 'media.update', 'uses'=> 'App\Http\Controllers\MediaController@update'));
Route::get('/media/delete/{id}', array('as'=> 'media.delete', 'uses'=>'App\Http\Controllers\MediaController@delete'));
Route::delete('/media/destroy/{id}', array('as'=> 'media.destroy', 'uses'=>'App\Http\Controllers\MediaController@destroy'));
Route::get('/media/events', array('as' => 'media.getEventsForSeason','uses' => 'App\Http\Controllers\MediaController@getEventsForSeason'));
Route::get('/media/pages', array('as' => 'media.pages', 'uses' => 'App\Http\Controllers\MediaController@pages'));
Route::get('/media/pages/events', array('as' => 'media.getEventsForPages', 'uses' => 'App\Http\Controllers\MediaController@getEventsForPages'));
Route::get('/media/pages/media', array('as' => 'media.getMediaForEvent', 'uses' => 'App\Http\Controllers\MediaController@getMediaForEvent'));


//Routes for Group Types
Route::get('/group-type', array('as' => 'group-type.index', 'uses' => 'App\Http\Controllers\GroupTypeController@index'));
Route::get('/group-type/add', array('as' => 'group-type.create', 'uses' =>'App\Http\Controllers\GroupTypeController@create'));
Route::post('/group-type/insert', array('as' => 'group-type.insert', 'uses' => 'App\Http\Controllers\GroupTypeController@insert'));
Route::get('/group-type/edit/{id}', array('as' => 'group-type.edit', 'uses' => 'App\Http\Controllers\GroupTypeController@edit'));
Route::patch('/group-type/update/{id}', array('as'=> 'group-type.update', 'uses'=> 'App\Http\Controllers\GroupTypeController@updates'));
Route::get('/group-type/delete/{id}', array('as'=> 'group-type.delete', 'uses'=>'App\Http\Controllers\GroupTypeController@deletes'));
Route::delete('/group-type/destroy/{id}', array('as'=> 'group-type.destroy', 'uses'=>'App\Http\Controllers\GroupTypeController@destroy'));

//Routes for Groups
Route::get('/group', array('as' => 'group.index', 'uses' => 'App\Http\Controllers\GroupController@index'));
Route::get('/group/add', array('as' => 'group.create', 'uses' =>'App\Http\Controllers\GroupController@create'));
Route::post('/group/insert', array('as' => 'group.insert', 'uses' => 'App\Http\Controllers\GroupController@insert'));
Route::get('/group/edit/{id}', array('as' => 'group.edit', 'uses' => 'App\Http\Controllers\GroupController@edit'));
Route::patch('/group/update/{id}', array('as'=> 'group.update', 'uses'=> 'App\Http\Controllers\GroupController@updates'));
Route::get('/group/delete/{id}', array('as'=> 'group.delete', 'uses'=>'App\Http\Controllers\GroupController@deletes'));
Route::delete('/group/destroy/{id}', array('as'=> 'group.destroy', 'uses'=>'App\Http\Controllers\GroupController@destroy'));


//Routes for Rotab Kashfeyya
Route::get('/rotab', array('as' => 'rotab.index', 'uses' => 'App\Http\Controllers\RotbaKashfeyaController@index'));
Route::get('/rotab/add', array('as' => 'rotab.create', 'uses' =>'App\Http\Controllers\RotbaKashfeyaController@create'));
Route::post('/rotab/insert', array('as' => 'rotab.insert', 'uses' => 'App\Http\Controllers\RotbaKashfeyaController@insert'));
Route::get('/rotab/edit/{id}', array('as' => 'rotab.edit', 'uses' => 'App\Http\Controllers\RotbaKashfeyaController@edit'));
Route::patch('/rotab/update/{id}', array('as'=> 'rotab.update', 'uses'=> 'App\Http\Controllers\RotbaKashfeyaController@updates'));
Route::get('/rotab/delete/{id}', array('as'=> 'rotab.delete', 'uses'=>'App\Http\Controllers\RotbaKashfeyaController@deletes'));
Route::delete('/rotab/destroy/{id}', array('as'=> 'rotab.destroy', 'uses'=>'App\Http\Controllers\RotbaKashfeyaController@destroy'));



//Routes for Inventory
Route::get('/inventory', array('as' => 'inventory.index', 'uses' => 'App\Http\Controllers\InventoryController@index'));
Route::get('/inventory/add', array('as' => 'inventory.create', 'uses' =>'App\Http\Controllers\InventoryController@create'));
Route::post('/inventory/insert', array('as' => 'inventory.insert', 'uses' => 'App\Http\Controllers\InventoryController@insert'));
Route::get('/inventory/edit/{id}', array('as' => 'inventory.edit', 'uses' => 'App\Http\Controllers\InventoryController@edit'));
Route::patch('/inventory/update/{id}', array('as'=> 'inventory.update', 'uses'=> 'App\Http\Controllers\InventoryController@updates'));
Route::get('/inventory/delete/{id}', array('as'=> 'inventory.delete', 'uses'=>'App\Http\Controllers\InventoryController@deletes'));
Route::delete('/inventory/destroy/{id}', array('as'=> 'inventory.destroy', 'uses'=>'App\Http\Controllers\InventoryController@destroy'));


//Routes for Inventory-issue
Route::get('/inventory-issue', array('as' => 'inventory-issue.index', 'uses' => 'App\Http\Controllers\InventoryIssueController@index'));
Route::get('/inventory-issue/getEventsForSeason', array('as' => 'inventory-issue.getEventsForSeason', 'uses' =>'App\Http\Controllers\InventoryIssueController@getEventsForSeason'));
Route::post('/inventory-issue/generate', array('as' => 'inventory-issue.generate','uses' => 'App\Http\Controllers\InventoryIssueController@generate'));

//Routes for LiveForm Max Limits
Route::get('/max-limits', array('as' => 'max-limits.index', 'uses' => 'App\Http\Controllers\LiveFormMaxLimitsController@index'));
Route::get('/max-limits/add', array('as' => 'max-limits.create', 'uses' =>'App\Http\Controllers\LiveFormMaxLimitsController@create'));
Route::post('/max-limits/insert', array('as' => 'max-limits.insert', 'uses' => 'App\Http\Controllers\LiveFormMaxLimitsController@insert'));
Route::get('/max-limits/edit/{id}/{sana_id}', array('as' => 'max-limits.edit', 'uses' => 'App\Http\Controllers\LiveFormMaxLimitsController@edit'));
Route::patch('/max-limits/update/{id}/{sana_id}', array('as'=> 'max-limits.update', 'uses'=> 'App\Http\Controllers\LiveFormMaxLimitsController@updates'));
Route::get('/max-limits/delete/{id}/{sana_id}', array('as'=> 'max-limits.delete', 'uses'=>'App\Http\Controllers\LiveFormMaxLimitsController@deletes'));
Route::delete('/max-limits/destroy/{id}/{sana_id}', array('as'=> 'max-limits.destroy', 'uses'=>'App\Http\Controllers\LiveFormMaxLimitsController@destroy'));

//Routes for Betakat Takaddom
Route::get('/betaka', array('as' => 'betaka.index', 'uses' => 'App\Http\Controllers\BetakaTakaddomController@index'));
Route::get('/betaka/add', array('as' => 'betaka.create', 'uses' =>'App\Http\Controllers\BetakaTakaddomController@create'));
Route::post('/betaka/insert', array('as' => 'betaka.insert', 'uses' => 'App\Http\Controllers\BetakaTakaddomController@insert'));
Route::get('/betaka/edit/{id}', array('as' => 'betaka.edit', 'uses' => 'App\Http\Controllers\BetakaTakaddomController@edit'));
Route::patch('/betaka/update/{id}', array('as'=> 'betaka.update', 'uses'=> 'App\Http\Controllers\BetakaTakaddomController@updates'));
Route::get('/betaka/delete/{id}', array('as'=> 'betaka.delete', 'uses'=>'App\Http\Controllers\BetakaTakaddomController@deletes'));
Route::delete('/betaka/destroy/{id}', array('as'=> 'betaka.destroy', 'uses'=>'App\Http\Controllers\BetakaTakaddomController@destroy'));


//Routes for Blood Types
Route::get('/blood', array('as'=> 'blood.index', 'uses'=> 'App\Http\Controllers\BloodTypeController@index'));
Route::get('/blood/add', array('as' => 'blood.create', 'uses' =>'App\Http\Controllers\BloodTypeController@create'));
Route::post('/blood/insert', array('as' => 'blood.insert', 'uses' => 'App\Http\Controllers\BloodTypeController@insert'));
Route::get('/blood/edit/{id}', array('as' => 'blood.edit', 'uses' => 'App\Http\Controllers\BloodTypeController@edit'));
Route::patch('/blood/update/{id}', array('as'=> 'blood.update', 'uses'=> 'App\Http\Controllers\BloodTypeController@updates'));
Route::get('/blood/delete/{id}', array('as'=> 'blood.delete', 'uses'=>'App\Http\Controllers\BloodTypeController@deletes'));
Route::delete('/blood/destroy/{id}', array('as'=> 'blood.destroy', 'uses'=>'App\Http\Controllers\BloodTypeController@destroy'));

//Routes for Manateq
Route::get('/manteqa', array('as'=> 'manteqa.index', 'uses'=> 'App\Http\Controllers\ManteqaController@index'));
Route::get('/manteqa/add', array('as' => 'manteqa.create', 'uses' =>'App\Http\Controllers\ManteqaController@create'));
Route::post('/manteqa/insert', array('as' => 'manteqa.insert', 'uses' => 'App\Http\Controllers\ManteqaController@insert'));
Route::get('/manteqa/edit/{id}', array('as' => 'manteqa.edit', 'uses' => 'App\Http\Controllers\ManteqaController@edit'));
Route::patch('/manteqa/update/{id}', array('as'=> 'manteqa.update', 'uses'=> 'App\Http\Controllers\ManteqaController@updates'));
Route::get('/manteqa/delete/{id}', array('as'=> 'manteqa.delete', 'uses'=>'App\Http\Controllers\ManteqaController@deletes'));
Route::delete('/manteqa/destroy/{id}', array('as'=> 'manteqa.destroy', 'uses'=>'App\Http\Controllers\ManteqaController@destroy'));

//Routes for Districts
Route::get('/district', array('as'=> 'district.index', 'uses'=> 'App\Http\Controllers\DistrictController@index'));
Route::get('/district/add', array('as' => 'district.create', 'uses' =>'App\Http\Controllers\DistrictController@create'));
Route::post('/district/insert', array('as' => 'district.insert', 'uses' => 'App\Http\Controllers\DistrictController@insert'));
Route::get('/district/edit/{id}', array('as' => 'district.edit', 'uses' => 'App\Http\Controllers\DistrictController@edit'));
Route::patch('/district/update/{id}', array('as'=> 'district.update', 'uses'=> 'App\Http\Controllers\DistrictController@updates'));
Route::get('/district/delete/{id}', array('as'=> 'district.delete', 'uses'=>'App\Http\Controllers\DistrictController@deletes'));
Route::delete('/district/destroy/{id}', array('as'=> 'district.destroy', 'uses'=>'App\Http\Controllers\DistrictController@destroy'));

//Routes for Qetaat
Route::get('/qetaa', array('as'=> 'qetaa.index', 'uses'=> 'App\Http\Controllers\QetaaController@index'));
Route::get('/qetaa/add', array('as' => 'qetaa.create', 'uses' =>'App\Http\Controllers\QetaaController@create'));
Route::post('/qetaa/insert', array('as' => 'qetaa.insert', 'uses' => 'App\Http\Controllers\QetaaController@insert'));
Route::get('/qetaa/edit/{id}', array('as' => 'qetaa.edit', 'uses' => 'App\Http\Controllers\QetaaController@edit'));
Route::patch('/qetaa/update/{id}', array('as'=> 'qetaa.update', 'uses'=> 'App\Http\Controllers\QetaaController@updates'));
Route::get('/qetaa/delete/{id}', array('as'=> 'qetaa.delete', 'uses'=>'App\Http\Controllers\QetaaController@deletes'));
Route::delete('/qetaa/destroy/{id}', array('as'=> 'qetaa.destroy', 'uses'=>'App\Http\Controllers\QetaaController@destroy'));

//Routes for Faculty
Route::get('/faculty', array('as'=> 'faculty.index', 'uses'=> 'App\Http\Controllers\FacultyController@index'));
Route::get('/faculty/add', array('as' => 'faculty.create', 'uses' =>'App\Http\Controllers\FacultyController@create'));
Route::post('/faculty/insert', array('as' => 'faculty.insert', 'uses' => 'App\Http\Controllers\FacultyController@insert'));
Route::get('/faculty/edit/{id}', array('as' => 'faculty.edit', 'uses' => 'App\Http\Controllers\FacultyController@edit'));
Route::patch('/faculty/update/{id}', array('as'=> 'faculty.update', 'uses'=> 'App\Http\Controllers\FacultyController@updates'));
Route::get('/faculty/delete/{id}', array('as'=> 'faculty.delete', 'uses'=>'App\Http\Controllers\FacultyController@deletes'));
Route::delete('/faculty/destroy/{id}', array('as'=> 'faculty.destroy', 'uses'=>'App\Http\Controllers\FacultyController@destroy'));



//Route for Seasons
Route::get('/season', array('as'=> 'season.index', 'uses'=> 'App\Http\Controllers\SeasonController@index'));
Route::get('/season/add', array('as' => 'season.create', 'uses' =>'App\Http\Controllers\SeasonController@create'));
Route::post('/season/insert', array('as' => 'season.insert', 'uses' => 'App\Http\Controllers\SeasonController@insert'));
Route::get('/season/edit/{id}', array('as' => 'season.edit', 'uses' => 'App\Http\Controllers\SeasonController@edit'));
Route::patch('/season/update/{id}', array('as'=> 'season.update', 'uses'=> 'App\Http\Controllers\SeasonController@updates'));
Route::get('/season/delete/{id}', array('as'=> 'season.delete', 'uses'=>'App\Http\Controllers\SeasonController@deletes'));
Route::delete('/season/destroy/{id}', array('as'=> 'season.destroy', 'uses'=>'App\Http\Controllers\SeasonController@destroy'));

//Route for SeasonsEvents
Route::get('/season-event', array('as'=> 'season-event.index', 'uses'=> 'App\Http\Controllers\SeasonEventController@index'));
Route::get('/season-event/add', array('as' => 'season-event.create', 'uses' =>'App\Http\Controllers\SeasonEventController@create'));
Route::post('/season-event/insert', array('as' => 'season-event.insert', 'uses' => 'App\Http\Controllers\SeasonEventController@insert'));
Route::get('/season-event/edit/{id}', array('as' => 'season-event.edit', 'uses' => 'App\Http\Controllers\SeasonEventController@edit'));
Route::patch('/season-event/update/{id}', array('as'=> 'season-event.update', 'uses'=> 'App\Http\Controllers\SeasonEventController@update'));
Route::get('/season-event/delete/{id}', array('as'=> 'season-event.delete', 'uses'=>'App\Http\Controllers\SeasonEventController@deletes'));
Route::delete('/season-event/destroy/{id}', array('as'=> 'season-event.destroy', 'uses'=>'App\Http\Controllers\SeasonEventController@destroy'));

//Routes for University
Route::get('/university', array('as'=> 'university.index', 'uses'=> 'App\Http\Controllers\UniversityController@index'));
Route::get('/university/add', array('as' => 'university.create', 'uses' =>'App\Http\Controllers\UniversityController@create'));
Route::post('/university/insert', array('as' => 'university.insert', 'uses' => 'App\Http\Controllers\UniversityController@insert'));
Route::get('/university/edit/{id}', array('as' => 'university.edit', 'uses' => 'App\Http\Controllers\UniversityController@edit'));
Route::patch('/university/update/{id}', array('as'=> 'university.update', 'uses'=> 'App\Http\Controllers\UniversityController@updates'));
Route::get('/university/delete/{id}', array('as'=> 'university.delete', 'uses'=>'App\Http\Controllers\UniversityController@deletes'));
Route::delete('/university/destroy/{id}', array('as'=> 'university.destroy', 'uses'=>'App\Http\Controllers\UniversityController@destroy'));

//Routes for Marhala Deraseyya
Route::get('/marhala', array('as'=> 'marhala.index', 'uses'=> 'App\Http\Controllers\MarhalaDeraseyyaController@index'));
Route::get('/marhala/add', array('as' => 'marhala.create', 'uses' =>'App\Http\Controllers\MarhalaDeraseyyaController@create'));
Route::post('/marhala/insert', array('as' => 'marhala.insert', 'uses' => 'App\Http\Controllers\MarhalaDeraseyyaController@insert'));
Route::get('/marhala/edit/{id}', array('as' => 'marhala.edit', 'uses' => 'App\Http\Controllers\MarhalaDeraseyyaController@edit'));
Route::patch('/marhala/update/{id}', array('as'=> 'marhala.update', 'uses'=> 'App\Http\Controllers\MarhalaDeraseyyaController@updates'));
Route::get('/marhala/delete/{id}', array('as'=> 'marhala.delete', 'uses'=>'App\Http\Controllers\MarhalaDeraseyyaController@deletes'));
Route::delete('/marhala/destroy/{id}', array('as'=> 'marhala.destroy', 'uses'=>'App\Http\Controllers\MarhalaDeraseyyaController@destroy'));

//Routes for Sana Marhala
Route::get('/sana-marhala', array('as'=> 'sana-marhala.index', 'uses'=> 'App\Http\Controllers\SanaMarhalaDeraseyyaController@index'));
Route::get('/sana-marhala/add', array('as' => 'sana-marhala.create', 'uses' =>'App\Http\Controllers\SanaMarhalaDeraseyyaController@create'));
Route::post('/sana-marhala/insert', array('as' => 'sana-marhala.insert', 'uses' => 'App\Http\Controllers\SanaMarhalaDeraseyyaController@insert'));
Route::get('/sana-marhala/edit/{id}', array('as' => 'sana-marhala.edit', 'uses' => 'App\Http\Controllers\SanaMarhalaDeraseyyaController@edit'));
Route::patch('/sana-marhala/update/{id}', array('as'=> 'sana-marhala.update', 'uses'=> 'App\Http\Controllers\SanaMarhalaDeraseyyaController@updates'));
Route::get('/sana-marhala/delete/{id}', array('as'=> 'sana-marhala.delete', 'uses'=>'App\Http\Controllers\SanaMarhalaDeraseyyaController@deletes'));
Route::delete('/sana-marhala/destroy/{id}', array('as'=> 'sana-marhala.destroy', 'uses'=>'App\Http\Controllers\SanaMarhalaDeraseyyaController@destroy'));

//Routes for Entry Questions
Route::get('/entry-questions', array('as'=> 'entry-questions.index', 'uses'=>'App\Http\Controllers\MarhalaEntryQuestionsController@index'));
Route::get('/entry-questions/add', array('as' => 'entry-questions.create', 'uses' =>'App\Http\Controllers\MarhalaEntryQuestionsController@create'));
Route::post('/entry-questions/insert', array('as' => 'entry-questions.insert', 'uses' => 'App\Http\Controllers\MarhalaEntryQuestionsController@insert'));
Route::get('/entry-questions/edit/{id}', array('as' => 'entry-questions.edit', 'uses' => 'App\Http\Controllers\MarhalaEntryQuestionsController@edit'));
Route::patch('/entry-questions/update/{id}', array('as'=> 'entry-questions.update', 'uses'=> 'App\Http\Controllers\MarhalaEntryQuestionsController@updates'));
Route::get('/entry-questions/delete/{id}', array('as'=> 'entry-questions.delete', 'uses'=>'App\Http\Controllers\MarhalaEntryQuestionsController@deletes'));
Route::delete('/entry-questions/destroy/{id}', array('as'=> 'entry-questions.destroy', 'uses'=>'App\Http\Controllers\MarhalaEntryQuestionsController@destroy'));

//Routes for Entry Questions
Route::get('/liveform-maxlimits', array('as'=> 'liveform-maxlimits.index', 'uses'=>'App\Http\Controllers\LiveFormMaxLimitsController@index'));
Route::get('/liveform-maxlimits/add', array('as' => 'liveform-maxlimits.create', 'uses' =>'App\Http\Controllers\LiveFormMaxLimitsController@create'));
Route::post('/liveform-maxlimits/insert', array('as' => 'liveform-maxlimits.insert', 'uses' => 'App\Http\Controllers\LiveFormMaxLimitsController@insert'));
Route::get('/liveform-maxlimits/edit/{id}', array('as' => 'liveform-maxlimits.edit', 'uses' => 'App\Http\Controllers\LiveFormMaxLimitsController@edit'));
Route::patch('/liveform-maxlimits/update/{id}', array('as'=> 'liveform-maxlimits.update', 'uses'=> 'App\Http\Controllers\LiveFormMaxLimitsController@updates'));
Route::get('/liveform-maxlimits/delete/{id}', array('as'=> 'liveform-maxlimits.delete', 'uses'=>'App\Http\Controllers\LiveFormMaxLimitsController@deletes'));
Route::delete('/liveform-maxlimits/destroy/{id}', array('as'=> 'liveform-maxlimits.destroy', 'uses'=>'App\Http\Controllers\LiveFormMaxLimitsController@destroy'));
});

Route::view('/feedback', 'feedback.index'); // Shows the form
Route::post('/feedback', [FeedbackController::class, 'create'])->name('feedback.create'); // Handles submission


Route::group(['middleware' => ['auth']], function() {
    Route::post('/logout', 'App\Http\Controllers\LogoutController@perform')->name('logout');
    Route::get('/change-password', function () {return view('change-password');});
});

// SuperAdmin-only password management
Route::middleware(['auth', 'checkAuth:SuperAdmin'])->group(function() {
    Route::get('/admin/passwords', 'App\Http\Controllers\AdminPasswordController@index')->name('admin.passwords'); // List users
    Route::get('/admin/passwords/{id}/edit', 'App\Http\Controllers\AdminPasswordController@edit')->name('admin.passwords.edit'); // Edit form
    Route::post('/admin/passwords/{id}/update', 'App\Http\Controllers\AdminPasswordController@update')->name('admin.passwords.update'); // Update password
    
// Whatsapp Integration

    Route::post('/whatsapp/send', 'App\\Http\\Controllers\\WhatsAppBridgeController@send')->name('whatsapp.send');
    Route::post('/whatsapp/sendWithHeader', 'App\\Http\\Controllers\\WhatsAppBridgeController@sendWithHeader')->name('whatsapp.sendWithHeader');
});

// Profile routes for every person
Route::middleware(['auth'])->group(function() {
    Route::get('/profile', 'App\\Http\\Controllers\\PersonProfileController@show')->name('profile.show');
    Route::get('/profile/edit', 'App\\Http\\Controllers\\PersonProfileController@edit')->name('profile.edit');
    Route::post('/profile/update', 'App\\Http\\Controllers\\PersonProfileController@update')->name('profile.update');
    Route::post('/profile/updatePassword', 'App\\Http\\Controllers\\PersonProfileController@updatePassword') ->name('profile.updatePassword');


});

// Google Drive Integration Routes
Route::get('/auth/google', [GoogleDriveController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleDriveController::class, 'handleGoogleCallback']);
Route::get('/drive/upload', [GoogleDriveController::class, 'uploadTestFile']);


//Attendance Routes
Route::get('/attendance/manage', [AttendanceController::class,'manage'])->name('attendance.manage');
Route::post('/attendance/save/{seasonEventId}', [AttendanceController::class,'save'])->name('attendance.save');



Route::get('/testing', [TestingController::class, 'index'])->name('testing.index');
Route::post('/testing', [TestingController::class, 'upload'])->name('testing.upload');