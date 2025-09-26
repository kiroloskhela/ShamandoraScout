<?php






use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\LoginApiController;
use App\Http\Controllers\API\TokenApiController;
use App\Http\Controllers\API\PersonApiController;
use App\Http\Controllers\API\AttendanceApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth endpoints
Route::post('/login',   [LoginApiController::class, 'apiLogin'])->middleware('throttle:5,1');
Route::post('/refresh', [TokenApiController::class, 'refresh'])->middleware('throttle:10,1');
// Route::post('/logout',  [LoginApiController::class, 'logout'])->middleware(['auth:sanctum']); 
// (You can add 'check.token.expiry' here too if you want logout blocked on expired tokens.)

// Protected API
Route::middleware(['auth:sanctum', 'check.token.expiry'])->group(function () {
    // Persons
    Route::get('/show-persons', [PersonApiController::class, 'ShowPersons']);
    Route::get('/person/{id}',  [PersonApiController::class, 'ShowProfile']);
    Route::get('/calendar/{id}', [PersonApiController::class, 'ShowCalendar']);

    // Attendance
    Route::get('/attendance/events',                         [AttendanceApiController::class, 'events']);
    Route::get('/attendance/persons/{seasonEventId}',        [AttendanceApiController::class, 'personsBySeasonEventId']);
    Route::get('/attendance/persons',                        [AttendanceApiController::class, 'persons']); // legacy
    Route::post('/attendance/save',                          [AttendanceApiController::class, 'save']);
});