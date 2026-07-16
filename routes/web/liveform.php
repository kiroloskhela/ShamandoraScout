<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LiveFormEnrolmentController;

/*
|--------------------------------------------------------------------------
| Live Form (Public)
|--------------------------------------------------------------------------
*/

Route::middleware(['liveform.open'])->group(function () {
    Route::get('/liveform', [LiveFormEnrolmentController::class, 'createLiveForm'])->name('person.liveform-create');
    Route::post('/liveform/step1', [LiveFormEnrolmentController::class, 'insertLiveForm'])->name('person.liveform-insert');

    Route::get('/liveform/step2', [LiveFormEnrolmentController::class, 'showLiveFormStep2'])->name('person.liveform-step2');
    Route::post('/liveform/step2', [LiveFormEnrolmentController::class, 'saveLiveFormStep2'])->name('person.liveform-step2-save');

    Route::get('/liveform/questions', [LiveFormEnrolmentController::class, 'getLiveformQuestions'])->name('person.entry-questions-liveform');
    Route::post('/liveform/questions', [LiveFormEnrolmentController::class, 'submitLiveformQuestions'])->name('person.entry-questions-submit-liveform');

    Route::get('/liveform/resume/{id}', [LiveFormEnrolmentController::class, 'resumeLegacyLiveformQuestions'])
        ->name('person.liveform-resume-questions');

    Route::post('/liveform/resume/{id}', [LiveFormEnrolmentController::class, 'submitLegacyLiveformQuestions'])
        ->name('person.liveform-resume-questions-submit');
});

Route::get('/liveform/apologize', fn() => view('person.liveform-limit-exceeded'));
Route::get('/liveform/finalize', fn() => view('person.liveform-finalize'));
Route::get('/liveform/waiting', fn() => view('person.liveform-waiting-list'));
