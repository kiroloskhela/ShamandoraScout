<?php

use App\Http\Controllers\AdminPasswordController;
use App\Http\Controllers\AppVersionSettingsController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CurriculumPlanController;
use App\Http\Controllers\BetakaTakaddomController;
use App\Http\Controllers\BloodTypeController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventTypeController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupPersonController;
use App\Http\Controllers\GroupTypeController;
use App\Http\Controllers\LiveFormSettingsController;
use App\Http\Controllers\ManteqaController;
use App\Http\Controllers\MarhalaDeraseyyaController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MigrateNewEnrolments;
use App\Http\Controllers\NewEnrolmentAdminController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PersonDirectoryController;
use App\Http\Controllers\PersonRoleController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\QetaaController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\RotbaKashfeyaController;
use App\Http\Controllers\SanaMarhalaDeraseyyaController;
use App\Http\Controllers\SeasonController;
use App\Http\Controllers\SeasonEventController;
use App\Http\Controllers\SeasonPersonRollController;
use App\Http\Controllers\UniversityController;
use App\Http\Controllers\WhatsAppBridgeController;
use App\Http\Controllers\WhatsAppCampaignController;
use App\Http\Controllers\WhatsAppStatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SuperAdmin Only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'checkAuth:SuperAdmin', 'can.permission:web.system.manage'])->group(function () {

    // Roles
    Route::get('/role', [RoleController::class, 'index'])->name('role.index');
    Route::get('/role/add', [RoleController::class, 'create'])->name('role.create');
    Route::post('/role/insert', [RoleController::class, 'insert'])->name('role.insert');
    Route::get('/role/edit/{id}', [RoleController::class, 'edit'])->name('role.edit');
    Route::patch('/role/update/{id}', [RoleController::class, 'updates'])->name('role.update');
    Route::get('/role/delete/{id}', [RoleController::class, 'deletes'])->name('role.delete');
    Route::delete('/role/destroy/{id}', [RoleController::class, 'destroy'])->name('role.destroy');

    // SuperAdmin-only even when the matrix is enforced (never grantable).
    Route::middleware('superadmin.only')->group(function () {
        Route::get('/person-role', [PersonRoleController::class, 'index'])->name('person-role.index');
        Route::get('/person-role/add', [PersonRoleController::class, 'create'])->name('person-role.create');
        Route::post('/person-role/insert', [PersonRoleController::class, 'insert'])->name('person-role.insert');
        Route::get('/person-role/edit/{id}', [PersonRoleController::class, 'edit'])->name('person-role.edit');
        Route::patch('/person-role/update/{id}', [PersonRoleController::class, 'updates'])->name('person-role.update');
        Route::get('/person-role/delete/{id}', [PersonRoleController::class, 'deletes'])->name('person-role.delete');
        Route::delete('/person-role/destroy/{id}', [PersonRoleController::class, 'destroy'])->name('person-role.destroy');

        Route::get('/admin/role-access', [RolePermissionController::class, 'edit'])->name('role-permissions.edit');
        Route::post('/admin/role-access', [RolePermissionController::class, 'update'])->name('role-permissions.update');
        Route::get('/admin/passwords', [AdminPasswordController::class, 'index'])->name('admin.passwords');
        Route::get('/admin/passwords/{id}/edit', [AdminPasswordController::class, 'edit'])->name('admin.passwords.edit');
        Route::post('/admin/passwords/{id}/update', [AdminPasswordController::class, 'update'])->name('admin.passwords.update');
    });

    // Group Person (add khadem)
    Route::get('/group-person/add-khadem', [GroupPersonController::class, 'createKhadem'])->name('group-person.create-khadem');
    Route::get('/group-person/delete/{id}', [GroupPersonController::class, 'deletes'])->name('group-person.delete');
    Route::delete('/group-person/destroy/{id}', [GroupPersonController::class, 'destroy'])->name('group-person.destroy');

    // System audit logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Curriculum plans (منهج) per Qetaa
    Route::get('/curriculum-plans', [CurriculumPlanController::class, 'index'])->name('curriculum-plan.index');
    Route::get('/curriculum-plans/add', [CurriculumPlanController::class, 'create'])->name('curriculum-plan.create');
    Route::post('/curriculum-plans/insert', [CurriculumPlanController::class, 'insert'])->name('curriculum-plan.insert');
    Route::get('/curriculum-plans/edit/{id}', [CurriculumPlanController::class, 'edit'])->name('curriculum-plan.edit');
    Route::patch('/curriculum-plans/update/{id}', [CurriculumPlanController::class, 'update'])->name('curriculum-plan.update');
    Route::get('/curriculum-plans/delete/{id}', [CurriculumPlanController::class, 'delete'])->name('curriculum-plan.delete');
    Route::delete('/curriculum-plans/destroy/{id}', [CurriculumPlanController::class, 'destroy'])->name('curriculum-plan.destroy');
    Route::post('/curriculum-plans/activate/{id}', [CurriculumPlanController::class, 'activate'])->name('curriculum-plan.activate');
    Route::post('/curriculum-plans/deactivate/{id}', [CurriculumPlanController::class, 'deactivate'])->name('curriculum-plan.deactivate');

    // Liveform open/close
    Route::get('/liveform-settings', [LiveFormSettingsController::class, 'edit'])->name('liveform-settings.edit');
    Route::put('/liveform-settings', [LiveFormSettingsController::class, 'update'])->name('liveform-settings.update');

    // Mobile app version settings (iOS / Android)
    Route::get('/app-version-settings', [AppVersionSettingsController::class, 'edit'])->name('app-version-settings.edit');
    Route::put('/app-version-settings', [AppVersionSettingsController::class, 'update'])->name('app-version-settings.update');

    // Whatsapp
    Route::get('/whatsapp/status', [WhatsAppStatusController::class, 'index'])->name('whatsapp.status');
    Route::post('/whatsapp/reconnect', [WhatsAppStatusController::class, 'reconnect'])->name('whatsapp.reconnect');
    Route::post('/whatsapp/send', [WhatsAppBridgeController::class, 'send'])->name('whatsapp.send');
    Route::post('/whatsapp/sendWithHeader', [WhatsAppBridgeController::class, 'sendWithHeader'])->name('whatsapp.sendWithHeader');

    // WhatsApp campaigns (bulk messaging)
    Route::get('/whatsapp/campaigns', [WhatsAppCampaignController::class, 'index'])->name('whatsapp.campaigns.index');
    Route::get('/whatsapp/campaigns/create', [WhatsAppCampaignController::class, 'create'])->name('whatsapp.campaigns.create');
    Route::get('/whatsapp/campaigns/create-csv', [WhatsAppCampaignController::class, 'createCsv'])->name('whatsapp.campaigns.create-csv');
    Route::get('/whatsapp/campaigns/csv-template', [WhatsAppCampaignController::class, 'downloadCsvTemplate'])->name('whatsapp.campaigns.csv-template');
    Route::post('/whatsapp/campaigns/csv', [WhatsAppCampaignController::class, 'storeCsv'])->name('whatsapp.campaigns.store-csv');
    Route::post('/whatsapp/campaigns', [WhatsAppCampaignController::class, 'store'])->name('whatsapp.campaigns.store');
    Route::get('/whatsapp/campaigns/contacts/search', [WhatsAppCampaignController::class, 'searchContacts'])->name('whatsapp.campaigns.contacts.search');
    Route::post('/whatsapp/campaigns/preview', [WhatsAppCampaignController::class, 'preview'])->name('whatsapp.campaigns.preview');
    Route::get('/whatsapp/campaigns/{campaign}', [WhatsAppCampaignController::class, 'show'])->name('whatsapp.campaigns.show');
    Route::get('/whatsapp/campaigns/{campaign}/edit', [WhatsAppCampaignController::class, 'edit'])->name('whatsapp.campaigns.edit');
    Route::put('/whatsapp/campaigns/{campaign}', [WhatsAppCampaignController::class, 'update'])->name('whatsapp.campaigns.update');
    Route::post('/whatsapp/campaigns/{campaign}/confirm', [WhatsAppCampaignController::class, 'confirm'])->name('whatsapp.campaigns.confirm');
    Route::post('/whatsapp/campaigns/{campaign}/pause', [WhatsAppCampaignController::class, 'pause'])->name('whatsapp.campaigns.pause');
    Route::post('/whatsapp/campaigns/{campaign}/resume', [WhatsAppCampaignController::class, 'resume'])->name('whatsapp.campaigns.resume');
    Route::post('/whatsapp/campaigns/{campaign}/cancel', [WhatsAppCampaignController::class, 'cancel'])->name('whatsapp.campaigns.cancel');

    // Show ALL persons
    Route::get('/person/ShowPersons', [PersonDirectoryController::class, 'ShowPersons'])->name('person.ShowPersons');

    // Events (shared CRUD lives in Secretary group; delete stays SuperAdmin-only)
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

    Route::get('/person/change-qetaa', [PersonDirectoryController::class, 'showChangeQetaa'])
        ->name('person.changeQetaa');

    // AJAX search (called by the search box)
    Route::get('/person/search', [PersonDirectoryController::class, 'searchPerson'])
        ->name('person.search');

    // POST — save the actual change
    Route::post('/person/{id}/change-qetaa', [PersonDirectoryController::class, 'changePersonQetaa'])
        ->name('person.changePersonQetaa');

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
    Route::post('/season/activate/{id}', [SeasonController::class, 'activate'])->name('season.activate');
    Route::get('/season/delete/{id}', [SeasonController::class, 'deletes'])->name('season.delete');
    Route::delete('/season/destroy/{id}', [SeasonController::class, 'destroy'])->name('season.destroy');

    // Season person roll (academic + youth qetaa) with rollback
    Route::get('/season/person-roll', [SeasonPersonRollController::class, 'preview'])->name('season-person-roll.preview');
    Route::post('/season/person-roll/apply', [SeasonPersonRollController::class, 'apply'])->name('season-person-roll.apply');
    Route::get('/season/person-roll/history', [SeasonPersonRollController::class, 'history'])->name('season-person-roll.history');
    Route::post('/season/person-roll/{batchId}/rollback', [SeasonPersonRollController::class, 'rollback'])->name('season-person-roll.rollback');

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

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/create', [NotificationController::class, 'create']);
    Route::post('/notifications/send', [NotificationController::class, 'send'])->name('notifications.send');

    // Migrate New Enrolments
    Route::post('/migrate-new-enrolments/all', [MigrateNewEnrolments::class, 'migrateAll'])->name('person.migrate-new-enrolments-all');
    Route::post('/migrate-new-enrolments/{qetaaID}', [MigrateNewEnrolments::class, 'migrate'])->name('person.migrate-new-enrolments');
    Route::get('/new-enrolments/migrations', [NewEnrolmentAdminController::class, 'indexNewEnrolmentsAndMigrations'])->name('person.new-enrolments-migrate-index');

});
