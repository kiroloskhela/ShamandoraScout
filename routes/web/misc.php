<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceLiveController;
use App\Http\Controllers\CurriculaController;
use App\Http\Controllers\CustodyRequestController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PersonProfileController;
use App\Http\Controllers\PlaceBookingController;
use App\Http\Controllers\QetaaTreeController;
use App\Http\Controllers\TestingController;

/*
|--------------------------------------------------------------------------
| Health (public)
|--------------------------------------------------------------------------
*/
Route::get('/health', HealthController::class)->name('health');
Route::get('/up', HealthController::class);

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

    // Team Structure
    Route::get('/team/structure', [QetaaTreeController::class, 'index'])->name('qetaa.tree');
    Route::get('/team/auxiliary', [QetaaTreeController::class, 'auxiliary'])->name('qetaa.auxiliary');
    Route::get('/team/structure/search-persons', [QetaaTreeController::class, 'searchPersons'])->name('qetaa.searchPersons');
    Route::get('/team/structure/rotba-list', [QetaaTreeController::class, 'getRotbaList'])->name('qetaa.getRotbaList');
    Route::post('/team/structure/group', [QetaaTreeController::class, 'storeGroup'])->name('qetaa.storeGroup');
    Route::delete('/team/structure/group/{groupId}', [QetaaTreeController::class, 'deleteGroup'])->name('qetaa.deleteGroup');
    Route::post('/team/structure/person', [QetaaTreeController::class, 'storePerson'])->name('qetaa.storePerson');
    Route::post('/team/structure/person/rotba', [QetaaTreeController::class, 'updatePersonRotba'])->name('qetaa.updatePersonRotba');
    Route::post('/team/structure/person/remove', [QetaaTreeController::class, 'removePerson'])->name('qetaa.removePerson');

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

    // Curricula (any authenticated role may view and publish)
    Route::get('/curricula', [CurriculaController::class, 'index'])->name('curricula.index');
    Route::get('/curricula/download/{id}', [CurriculaController::class, 'download'])->name('curricula.download');
    Route::get('/curricula/add', [CurriculaController::class, 'create'])->name('curricula.create');
    Route::post('/curricula/insert', [CurriculaController::class, 'insert'])->name('curricula.insert');
    Route::get('/curricula/edit/{id}', [CurriculaController::class, 'edit'])->name('curricula.edit');
    Route::patch('/curricula/update/{id}', [CurriculaController::class, 'updates'])->name('curricula.update');
    Route::get('/curricula/delete/{id}', [CurriculaController::class, 'deletes'])->name('curricula.delete');
    Route::delete('/curricula/destroy/{id}', [CurriculaController::class, 'destroy'])->name('curricula.destroy');

    // Attendance
    Route::get('/attendance/manage', [AttendanceController::class, 'manage'])->name('attendance.manage');
    Route::post('/attendance/save/{seasonEventId}', [AttendanceController::class, 'save'])->name('attendance.save');
    Route::get('/attendance/scan', [AttendanceController::class, 'scan'])->name('attendance.scan');
    Route::post('/attendance/lookup', [AttendanceController::class, 'lookup'])->name('attendance.lookup');
    Route::post('/attendance/mark-present', [AttendanceController::class, 'markPresent'])->name('attendance.mark-present');
    Route::post('/attendance/mark-status', [AttendanceController::class, 'markStatus'])->name('attendance.mark-status');
    Route::post('/attendance/send-qr/{personId}', [AttendanceController::class, 'sendQr'])->name('attendance.send-qr');
    Route::post('/attendance/send-qr-entity/{type}/{id}', [AttendanceController::class, 'sendEntityQr'])->name('attendance.send-qr-entity');
    Route::post('/attendance/send-qr-bulk', [AttendanceController::class, 'sendQrBulk'])->name('attendance.send-qr-bulk');

    Route::middleware('checkAuth:SuperAdmin|Secretary|AdminSecretary|Finance|AdminFinance')->group(function () {
        Route::get('/attendance/live', [AttendanceLiveController::class, 'index'])->name('attendance.live');
        Route::get('/attendance/live/snapshot', [AttendanceLiveController::class, 'snapshot'])->name('attendance.live.snapshot');
    });

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
    Route::get('/games/show/{id}', [GamesController::class, 'show'])->name('games.show');
    Route::middleware(['checkAuth:SuperAdmin'])->group(function () {
        Route::get('/games/create', [GamesController::class, 'create'])->name('games.create');
        Route::post('/games/insert', [GamesController::class, 'insert'])->name('games.insert');
        Route::get('/games/edit/{id}', [GamesController::class, 'edit'])->name('games.edit');
        Route::post('/games/update/{id}', [GamesController::class, 'updates'])->name('games.updates');
        Route::get('/games/delete/{id}', [GamesController::class, 'deletes'])->name('games.delete');
        Route::post('/games/destroy/{id}', [GamesController::class, 'destroy'])->name('games.destroy');
    });
});
