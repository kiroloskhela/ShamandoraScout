<?php

use App\Http\Controllers\CurriculaCategoryController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FamilyMembersController;
use App\Http\Controllers\GroupPersonController;
use App\Http\Controllers\GuestsController;
use App\Http\Controllers\LiveFormMaxLimitsController;
use App\Http\Controllers\MarhalaEntryQuestionsController;
use App\Http\Controllers\NewEnrolmentAdminController;
use App\Http\Controllers\PersonBlackListController;
use App\Http\Controllers\PersonDirectoryController;
use App\Http\Controllers\PersonExamMarkController;
use App\Http\Controllers\PersonSpecialCaseController;
use App\Http\Controllers\PersonTreeController;
use App\Http\Controllers\SeasonEventServantFollowupController;
use App\Http\Controllers\WaitingListController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminQetaa', 'can.permission:web.people.manage'])->group(function () {

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

    // Exam marks
    Route::get('/personexammark', [PersonExamMarkController::class, 'index'])->name('personexammark.index');
    Route::get('/personexammark/create', [PersonExamMarkController::class, 'create'])->name('personexammark.create');
    Route::post('/personexammark/insert', [PersonExamMarkController::class, 'insert'])->name('personexammark.insert');
    Route::get('/personexammark/edit/{id}', [PersonExamMarkController::class, 'edit'])->name('personexammark.edit');
    Route::post('/personexammark/updates/{id}', [PersonExamMarkController::class, 'updates'])->name('personexammark.updates');
    Route::get('/personexammark/delete/{id}', [PersonExamMarkController::class, 'deletes'])->name('personexammark.delete');
    Route::post('/personexammark/destroy/{id}', [PersonExamMarkController::class, 'destroy'])->name('personexammark.destroy');
    Route::get('/personexammark/search-persons', [PersonExamMarkController::class, 'searchPersons'])->name('personexammark.searchPersons');

    // Season Event Servant Followup
    Route::prefix('event-servant-followup')->name('eventServantFollowup.')->group(function () {
        Route::get('/selector', [SeasonEventServantFollowupController::class, 'selector'])->name('selector');
        Route::get('/get-events-with-plan', [SeasonEventServantFollowupController::class, 'getEventsWithPlan'])->name('getEventsWithPlan');
        Route::get('/event/{seasonEventID}', [SeasonEventServantFollowupController::class, 'index'])->name('index');

    });

    // Group Person (normal)
    Route::get('/group-person', [GroupPersonController::class, 'index'])->name('group-person.index');
    Route::get('/group-person/add-makhdoom', [GroupPersonController::class, 'createMakhdoom'])->name('group-person.create-makhdoom');
    Route::post('/group-person/insert', [GroupPersonController::class, 'insert'])->name('group-person.insert');
    Route::get('/group-person/edit/{id}', [GroupPersonController::class, 'edit'])->name('group-person.edit');
    Route::patch('/group-person/update/{id}', [GroupPersonController::class, 'updates'])->name('group-person.update');

    Route::get('/person/add', [PersonDirectoryController::class, 'create'])->name('person.create');
    Route::post('/person/insert', [PersonDirectoryController::class, 'insert'])->name('person.insert');

    Route::get('/person/entry-questions/insert/{id}', [PersonDirectoryController::class, 'getQuestions'])->name('person.entry-questions');
    Route::post('/person/entry-questions/submit', [PersonDirectoryController::class, 'submitQuestions'])->name('person.entry-questions-submit');

    Route::get('/person/edit/{id}', [PersonDirectoryController::class, 'edit'])->name('person.edit');
    Route::patch('/person/update/{id}', [PersonDirectoryController::class, 'updates'])->name('person.update');

    // Delete Persons
    Route::get('/person/delete/{id}', [PersonDirectoryController::class, 'deletes'])->name('person.delete');
    Route::delete('/person/destroy/{id}', [PersonDirectoryController::class, 'destroy'])->name('person.destroy');

    // Waiting List Management
    Route::get('/persons/waiting-list', [WaitingListController::class, 'indexWaitingList'])->name('person.waiting-list-index');
    Route::get('/persons/waiting-list/{id}', [WaitingListController::class, 'showWaitingList'])->name('person.waiting-list-show');
    Route::get('/persons/waiting-list/{id}/migrate', [WaitingListController::class, 'migrateWaitingList'])->name('person.waiting-list-migrate');
    Route::get('/persons/waiting-list/{id}/decline', [WaitingListController::class, 'declineWaitingList'])->name('person.waiting-list-decline');

});

// Curricula categories: any authenticated role may manage
Route::middleware(['auth'])->group(function () {
    Route::get('/CurriculaCategory', [CurriculaCategoryController::class, 'index'])->name('CurriculaCategory.index');
    Route::get('/CurriculaCategory/add', [CurriculaCategoryController::class, 'create'])->name('CurriculaCategory.create');
    Route::post('/CurriculaCategory/insert', [CurriculaCategoryController::class, 'insert'])->name('CurriculaCategory.insert');
    Route::get('/CurriculaCategory/edit/{id}', [CurriculaCategoryController::class, 'edit'])->name('CurriculaCategory.edit');
    Route::patch('/CurriculaCategory/update/{id}', [CurriculaCategoryController::class, 'updates'])->name('CurriculaCategory.update');
    Route::get('/CurriculaCategory/delete/{id}', [CurriculaCategoryController::class, 'deletes'])->name('CurriculaCategory.delete');
    Route::delete('/CurriculaCategory/destroy/{id}', [CurriculaCategoryController::class, 'destroy'])->name('CurriculaCategory.destroy');
});

// Full profile + served directory for all staff except Mkhdom.
Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminQetaa|AdminSecretary|Secretary|AdminFinance|Finance|AdminInventory|Inventory|AdminFirstAid|Khadem|Media', 'can.permission:web.people.view_served|web.people.directory'])->group(function () {
    Route::get('/person', [PersonDirectoryController::class, 'index'])->name('person.index');
    Route::get('/person/show/{id}', [PersonDirectoryController::class, 'show'])->name('person.show');
});

// Person directory Excel export (scoped to caller's groups)
Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminQetaa|AdminSecretary|Secretary|AdminFinance|Khadem', 'can.permission:web.people.directory'])->group(function () {
    Route::get('/export/served-people', [ExportController::class, 'form'])->name('export.served-people');
    Route::post('/export/served-people', [ExportController::class, 'download'])->name('export.served-people.download');
    Route::get('/export/scouts', fn () => redirect()->route('export.served-people'))->name('export.scouts.excel');
});

Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminQetaa|AdminSecretary|Secretary|AdminFinance', 'can.permission:web.enrolments.manage'])->group(function () {

    Route::get('/new-enrolments/show/qetaa/{id}', [NewEnrolmentAdminController::class, 'showNewEnrolmentsByQetaaID'])->name('person.new-enrolments-show-qetaa');
    Route::get('/new-enrolments/show/{id}', [NewEnrolmentAdminController::class, 'showNewEnrolments'])->name('person.new-enrolments-show');
    Route::get('/new-enrolments/person/approve/{id}', [NewEnrolmentAdminController::class, 'approveNewEnrolments'])->name('person.new-enrolments-approve');
    Route::get('/new-enrolments/person/approve-again/{id}', [NewEnrolmentAdminController::class, 'approveAgainNewEnrolments'])->name('person.new-enrolments-approve-again');
    Route::get('/new-enrolments/person/delete/{id}', [NewEnrolmentAdminController::class, 'deleteNewEnrolments'])->name('person.new-enrolments-delete');
    Route::delete('/new-enrolments/person/destroy/{id}', [NewEnrolmentAdminController::class, 'destroyNewEnrolments'])->name('person.new-enrolments-destroy');

    // New Enrolments (admin lists)
    Route::get('/new-enrolments', [NewEnrolmentAdminController::class, 'indexNewEnrolments'])->name('person.new-enrolments-index');

    Route::get('/new-enrolments/analytics', [NewEnrolmentAdminController::class, 'analyticsNewEnrolments'])->name('person.new-enrolments-analytics');
    Route::get('/new-enrolments/count/marahel', [NewEnrolmentAdminController::class, 'countNewEnrolmentsMarahel'])->name('person.new-enrolments-marahel-count');
    Route::get('/new-enrolments/count/qetaat', [NewEnrolmentAdminController::class, 'countNewEnrolmentsQetaat'])->name('person.new-enrolments-qetaat-count');

    Route::get('/new-enrolments/edit/{id}', [NewEnrolmentAdminController::class, 'editNewEnrolments'])->name('person.new-enrolments-edit');
    Route::put('/new-enrolments/update/{id}', [NewEnrolmentAdminController::class, 'updateNewEnrolments'])->name('person.new-enrolments-update');

    // Max Limits
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

Route::middleware(['auth', 'checkAuth:SuperAdmin|Finance|AdminFinance|Secretary|AdminSecretary', 'can.permission:web.registrations.manage'])->group(function () {

    // Guests
    Route::get('/guests', [GuestsController::class, 'index'])->name('guests.index');
    Route::get('/guests/create', [GuestsController::class, 'create'])->name('guests.create');
    Route::post('/guests/store', [GuestsController::class, 'store'])->name('guests.store');
    Route::get('/guests/show/{id}', [GuestsController::class, 'show'])->name('guests.show');
    Route::get('/guests/edit/{id}', [GuestsController::class, 'edit'])->name('guests.edit');
    Route::post('/guests/update/{id}', [GuestsController::class, 'update'])->name('guests.update');
    Route::get('/guests/delete/{id}', [GuestsController::class, 'delete'])->name('guests.delete');
    Route::post('/guests/destroy/{id}', [GuestsController::class, 'destroy'])->name('guests.destroy');

    // Family Members
    Route::get('/family-members', [FamilyMembersController::class, 'index'])->name('family-members.index');
    Route::get('/family-members/create', [FamilyMembersController::class, 'create'])->name('family-members.create');
    Route::post('/family-members/store', [FamilyMembersController::class, 'store'])->name('family-members.store');
    Route::get('/family-members/show/{id}', [FamilyMembersController::class, 'show'])->name('family-members.show');
    Route::get('/family-members/edit/{id}', [FamilyMembersController::class, 'edit'])->name('family-members.edit');
    Route::post('/family-members/update/{id}', [FamilyMembersController::class, 'update'])->name('family-members.update');
    Route::get('/family-members/delete/{id}', [FamilyMembersController::class, 'delete'])->name('family-members.delete');
    Route::post('/family-members/destroy/{id}', [FamilyMembersController::class, 'destroy'])->name('family-members.destroy');

    // Person Tree
    Route::get('/person-tree', [PersonTreeController::class, 'index'])->name('person-tree.index');

});
