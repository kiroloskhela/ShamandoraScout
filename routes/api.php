<?php






use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\LoginApiController;
use App\Http\Controllers\API\TokenApiController;
use App\Http\Controllers\API\PersonApiController;
use App\Http\Controllers\API\AttendanceApiController;
use App\Http\Controllers\API\CurriculaApiController;
use App\Http\Controllers\API\MediaApiController;
use App\Http\Controllers\API\CustodyApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


    Route::post('/login',   [LoginApiController::class, 'apiLogin'])->middleware('throttle:5,1');
    Route::post('/refresh', [TokenApiController::class, 'refresh'])->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'token.expiry'])->group(function () {
    Route::post('/logout', [LoginApiController::class, 'apiLogout']);

    // Persons
    Route::get('/show-persons', [PersonApiController::class, 'ShowPersons']);
    Route::get('/person/{id}',  [PersonApiController::class, 'ShowProfile']);
    Route::get('/calendar/{id}', [PersonApiController::class, 'ShowCalendar']);

    // Attendance
    Route::get('/attendance/events',                  [AttendanceApiController::class, 'events']);
    Route::get('/attendance/persons/{seasonEventId}', [AttendanceApiController::class, 'personsBySeasonEventId']);
    Route::get('/attendance/persons',                 [AttendanceApiController::class, 'persons']); 
    Route::post('/attendance/save',                   [AttendanceApiController::class, 'save']);

    // Curricula
    Route::get('/curricula', [CurriculaApiController::class, 'index']);
    Route::get('/curricula/meta', [CurriculaApiController::class, 'meta']);
    Route::get('/curricula/{id}', [CurriculaApiController::class, 'show']);
    Route::get('/curricula/{id}/download', [CurriculaApiController::class, 'download']);
    

    // Media
    Route::get('/media/seasons', [MediaApiController::class, 'seasons']);
    Route::get('/media/events', [MediaApiController::class, 'events']); // ?season_id=
    Route::get('/media/links', [MediaApiController::class, 'links']);   // ?season_event_id=
    Route::get('/media/links/{seasonEventId}', [MediaApiController::class, 'linksBySeasonEventId']);

    // Custody


    Route::get('/custody/meta', [CustodyApiController::class, 'meta']);

    Route::get('/custody/requests', [CustodyApiController::class, 'index']);
    Route::post('/custody/requests', [CustodyApiController::class, 'store']);
    Route::get('/custody/requests/{id}', [CustodyApiController::class, 'show']);
    Route::put('/custody/requests/{id}', [CustodyApiController::class, 'update']);
    Route::delete('/custody/requests/{id}', [CustodyApiController::class, 'destroy']);


});