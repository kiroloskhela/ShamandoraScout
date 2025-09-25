<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PersonController;
use App\Http\Controllers\API\AttendanceApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




//Login API "Add rate limiting to login endpoint to prevent brute-force attacks."
Route::post('/login', [\App\Http\Controllers\API\LoginApiController::class, 'apiLogin'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
  
    //Person API
    Route::get('/show-persons', [\App\Http\Controllers\API\PersonApiController::class, 'ShowPersons']);
    Route::get('/person/{id}', [\App\Http\Controllers\API\PersonApiController::class, 'ShowProfile']);
   
    //Calendar API
    Route::get('/calendar/{id}', [\App\Http\Controllers\API\PersonApiController::class, 'ShowCalendar']);
  
    // Attendance API
    Route::get('/attendance/events', [AttendanceApiController::class, 'events']); // ?person_id or ?season_id
    Route::get('/attendance/persons/{seasonEventId}', [AttendanceApiController::class, 'personsBySeasonEventId']); // recommended
    Route::get('/attendance/persons', [AttendanceApiController::class, 'persons']); // legacy query version
    Route::post('/attendance/save', [AttendanceApiController::class, 'save']);

});