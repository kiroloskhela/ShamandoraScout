<?php

use App\Http\Controllers\EventProgramController;
use App\Http\Controllers\MyEventProgramController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Event Program (camp leader missions)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'checkAuth:SuperAdmin', 'can.permission:web.system.manage'])->group(function () {
    Route::get('/event-program', [EventProgramController::class, 'index'])->name('event-program.index');
    Route::get('/event-program/guide', [EventProgramController::class, 'downloadGuide'])->name('event-program.guide');
    Route::get('/event-program/open/{seasonEventId}', [EventProgramController::class, 'open'])->name('event-program.open');
    Route::get('/event-program/import-session/{sessionId}', [EventProgramController::class, 'review'])->name('event-program.import.review');
    Route::post('/event-program/import-session/{sessionId}/answer', [EventProgramController::class, 'answer'])->name('event-program.import.answer');
    Route::post('/event-program/days/{dayId}/slots', [EventProgramController::class, 'storeSlot'])->name('event-program.slots.store');
    Route::post('/event-program/slots/{slotId}/assignments', [EventProgramController::class, 'storeAssignment'])->name('event-program.assignments.store');
    Route::delete('/event-program/resources/{resourceId}', [EventProgramController::class, 'destroyResource'])->name('event-program.resources.destroy');

    Route::get('/event-program/{id}', [EventProgramController::class, 'show'])->name('event-program.show')->whereNumber('id');
    Route::post('/event-program/{id}/meta', [EventProgramController::class, 'updateMeta'])->name('event-program.meta')->whereNumber('id');
    Route::post('/event-program/{id}/publish', [EventProgramController::class, 'publish'])->name('event-program.publish')->whereNumber('id');
    Route::post('/event-program/{id}/unpublish', [EventProgramController::class, 'unpublish'])->name('event-program.unpublish')->whereNumber('id');
    Route::post('/event-program/{id}/days', [EventProgramController::class, 'storeDay'])->name('event-program.days.store')->whereNumber('id');
    Route::post('/event-program/{id}/resources', [EventProgramController::class, 'storeResource'])->name('event-program.resources.store')->whereNumber('id');
    Route::get('/event-program/{id}/import', [EventProgramController::class, 'importForm'])->name('event-program.import')->whereNumber('id');
    Route::post('/event-program/{id}/import', [EventProgramController::class, 'import'])->name('event-program.import.store')->whereNumber('id');
    Route::post('/event-program/{id}/refresh', [EventProgramController::class, 'refresh'])->name('event-program.refresh')->whereNumber('id');
    Route::post('/event-program/{id}/whatsapp', [EventProgramController::class, 'sendWhatsApp'])->name('event-program.whatsapp')->whereNumber('id');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/my-program', [MyEventProgramController::class, 'index'])->name('my-program.index');
    Route::get('/my-program/{seasonEventId}', [MyEventProgramController::class, 'show'])->name('my-program.show')->whereNumber('seasonEventId');
    Route::get('/my-program/{seasonEventId}/day/{dayNumber}', [MyEventProgramController::class, 'day'])->name('my-program.day')->whereNumber(['seasonEventId', 'dayNumber']);
});
