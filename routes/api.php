<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\LoginApiController;
use App\Http\Controllers\API\TokenApiController;
use App\Http\Controllers\API\PersonApiController;
use App\Http\Controllers\API\AttendanceApiController;
use App\Http\Controllers\API\CurriculaApiController;
use App\Http\Controllers\API\MediaApiController;
use App\Http\Controllers\API\CustodyApiController;
use App\Http\Controllers\API\PlaceBookingApiController;
use App\Http\Controllers\API\PersonSpecialCaseApiController;
use App\Http\Controllers\API\GamesApiController;
use App\Http\Controllers\API\Test;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [LoginApiController::class, 'apiLogin'])->middleware('throttle:5,1');
Route::post('/refresh', [TokenApiController::class, 'refresh'])->middleware('throttle:10,1');
Route::post('/test', [Test::class, 'store']);


// Protected routes
Route::middleware(['auth:sanctum', 'token.expiry'])->group(function () {

    // Auth
    Route::post('/logout', [LoginApiController::class, 'apiLogout']);

    // Persons
    Route::get('/show-persons', [PersonApiController::class, 'ShowPersons']);
    Route::get('/person/{id}', [PersonApiController::class, 'ShowProfile']);
    Route::get('/calendar/{id}', [PersonApiController::class, 'ShowCalendar']);

    // Attendance
    Route::get('/attendance/events', [AttendanceApiController::class, 'events']);
    Route::get('/attendance/persons', [AttendanceApiController::class, 'persons']);
    Route::get('/attendance/persons/{seasonEventId}', [AttendanceApiController::class, 'personsBySeasonEventId']);
    Route::post('/attendance/save', [AttendanceApiController::class, 'save']);

    // Curricula
    Route::get('/curricula', [CurriculaApiController::class, 'index']);
    Route::get('/curricula/meta', [CurriculaApiController::class, 'meta']);
    Route::get('/curricula/{id}', [CurriculaApiController::class, 'show']);
    Route::get('/curricula/{id}/download', [CurriculaApiController::class, 'download']);

    // Media
    Route::get('/media/seasons', [MediaApiController::class, 'seasons']);
    Route::get('/media/events', [MediaApiController::class, 'events']);
    Route::get('/media/links', [MediaApiController::class, 'links']);
    Route::get('/media/links/{seasonEventId}', [MediaApiController::class, 'linksBySeasonEventId']);

    // Custody
    Route::get('/custody/meta', [CustodyApiController::class, 'meta']);
    Route::get('/custody/requests', [CustodyApiController::class, 'index']);
    Route::post('/custody/requests', [CustodyApiController::class, 'store']);
    Route::get('/custody/requests/{id}', [CustodyApiController::class, 'show']);
    Route::put('/custody/requests/{id}', [CustodyApiController::class, 'update']);
    Route::delete('/custody/requests/{id}', [CustodyApiController::class, 'destroy']);

    // Place bookings
    Route::get('/place_bookings/meta', [PlaceBookingApiController::class, 'meta']);
    Route::get('/place_bookings/places/{locationId}', [PlaceBookingApiController::class, 'placesByLocation']);
    Route::get('/place_bookings', [PlaceBookingApiController::class, 'index']);
    Route::post('/place_bookings', [PlaceBookingApiController::class, 'store']);
    Route::get('/place_bookings/{id}', [PlaceBookingApiController::class, 'show']);
    Route::put('/place_bookings/{id}', [PlaceBookingApiController::class, 'update']);
    Route::delete('/place_bookings/{id}', [PlaceBookingApiController::class, 'destroy']);

    // Person special cases
    Route::get('/person-special-cases', [PersonSpecialCaseApiController::class, 'index']);
    Route::get('/person-special-cases/persons', [PersonSpecialCaseApiController::class, 'persons']);
    Route::get('/person-special-cases/search/persons', [PersonSpecialCaseApiController::class, 'searchPersons']);
    Route::get('/person-special-cases/{id}', [PersonSpecialCaseApiController::class, 'show']);
    Route::post('/person-special-cases', [PersonSpecialCaseApiController::class, 'store']);
    Route::put('/person-special-cases/{id}', [PersonSpecialCaseApiController::class, 'update']);
    Route::delete('/person-special-cases/{id}', [PersonSpecialCaseApiController::class, 'destroy']);

    // Games
    Route::get('/games', [GamesApiController::class, 'index']);
    Route::get('/games/{id}', [GamesApiController::class, 'show']);
    Route::post('/games', [GamesApiController::class, 'store']);
    Route::put('/games/{id}', [GamesApiController::class, 'update']);
    Route::delete('/games/{id}', [GamesApiController::class, 'destroy']);
});