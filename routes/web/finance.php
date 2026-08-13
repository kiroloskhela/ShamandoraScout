<?php

use App\Http\Controllers\SeasonEventBookingFinanceController;
use App\Http\Controllers\SeasonEventFinanceController;
use App\Http\Controllers\SeasonEventWaitingListController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'checkAuth:SuperAdmin|Finance|AdminFinance', 'can.permission:web.finance.manage'])->group(function () {

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [SeasonEventFinanceController::class, 'index'])->name('index');
        Route::get('/create', [SeasonEventFinanceController::class, 'create'])->name('create');
        Route::get('/get-events-for-season', [SeasonEventFinanceController::class, 'getEventsForSeason'])->name('getEventsForSeason');
        Route::post('/store', [SeasonEventFinanceController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [SeasonEventFinanceController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [SeasonEventFinanceController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [SeasonEventFinanceController::class, 'delete'])->name('delete');
        Route::post('/destroy/{id}', [SeasonEventFinanceController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('event-booking-finance')->name('eventBookingFinance.')->group(function () {
        Route::get('/selector', [SeasonEventBookingFinanceController::class, 'selector'])->name('selector');
        Route::get('/get-events-with-plan', [SeasonEventBookingFinanceController::class, 'getEventsWithPlan'])->name('getEventsWithPlan');

        Route::get('/event/{seasonEventID}', [SeasonEventBookingFinanceController::class, 'index'])->name('index');

        Route::get('/event/{seasonEventID}/create', [SeasonEventBookingFinanceController::class, 'create'])->name('create');
        Route::get('/event/{seasonEventID}/search-eligible-persons', [SeasonEventBookingFinanceController::class, 'searchEligiblePersons'])->name('searchEligiblePersons');
        Route::get('/event/{seasonEventID}/search-guests', [SeasonEventBookingFinanceController::class, 'searchEligibleGuests'])->name('searchGuests');
        Route::get('/event/{seasonEventID}/search-families', [SeasonEventBookingFinanceController::class, 'searchEligibleFamilies'])->name('searchFamilies');
        Route::get('/event/{seasonEventID}/create-guest-family', [SeasonEventBookingFinanceController::class, 'createGuestFamily'])->name('createGuestFamily');

        Route::post('/event/{seasonEventID}/store', [SeasonEventBookingFinanceController::class, 'store'])->name('store');

        Route::get('/booking/{bookingID}/installment/create', [SeasonEventBookingFinanceController::class, 'createInstallment'])->name('createInstallment');
        Route::post('/booking/{bookingID}/installment/store', [SeasonEventBookingFinanceController::class, 'storeInstallment'])->name('storeInstallment');

        Route::get('/payment/{paymentID}/edit-last', [SeasonEventBookingFinanceController::class, 'editLastPayment'])->name('editLastPayment');
        Route::post('/payment/{paymentID}/update-last', [SeasonEventBookingFinanceController::class, 'updateLastPayment'])->name('updateLastPayment');

        Route::get('/booking/{bookingID}/refund', [SeasonEventBookingFinanceController::class, 'refundPage'])->name('refundPage');
        Route::post('/booking/{bookingID}/refund', [SeasonEventBookingFinanceController::class, 'refundStore'])->name('refundStore');

        Route::get('/booking/{bookingID}/partial-refund', [SeasonEventBookingFinanceController::class, 'partialRefundPage'])->name('partialRefundPage');
        Route::post('/booking/{bookingID}/partial-refund', [SeasonEventBookingFinanceController::class, 'partialRefundStore'])->name('partialRefundStore');

        Route::get('/receipt/{paymentID}/print', [SeasonEventBookingFinanceController::class, 'printReceipt'])->name('printReceipt');

        Route::get('/event/{seasonEventID}/export/today', [SeasonEventBookingFinanceController::class, 'exportToday'])->name('exportToday');
        Route::get('/event/{seasonEventID}/export/all', [SeasonEventBookingFinanceController::class, 'exportAll'])->name('exportAll');

        Route::get('/booking/{bookingID}/show', [SeasonEventBookingFinanceController::class, 'show'])->name('show');
        Route::post('/booking/{bookingID}/update-shirt-size', [SeasonEventBookingFinanceController::class, 'updateShirtSize'])->name('updateShirtSize');
        Route::post('/booking/{bookingID}/send-qr', [SeasonEventBookingFinanceController::class, 'sendQr'])->name('sendQr');

        Route::middleware(['checkAuth:SuperAdmin', 'can.permission:web.finance.delete_booking'])->group(function () {
            Route::get('/booking/{bookingID}/delete', [SeasonEventBookingFinanceController::class, 'deletePage'])->name('deletePage');
            Route::delete('/booking/{bookingID}/delete', [SeasonEventBookingFinanceController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('event-waiting-list')->name('eventWaitingList.')->group(function () {
        Route::get('/selector', [SeasonEventWaitingListController::class, 'selector'])->name('selector');
        Route::get('/events', [SeasonEventWaitingListController::class, 'getEvents'])->name('events');

        Route::get('/{seasonEventID}', [SeasonEventWaitingListController::class, 'index'])->name('index');
        Route::get('/{seasonEventID}/search-eligible-persons', [SeasonEventWaitingListController::class, 'searchEligiblePersons'])->name('searchEligiblePersons');
        Route::post('/{seasonEventID}/store', [SeasonEventWaitingListController::class, 'store'])->name('store');

        Route::get('/delete/{waitingListID}', [SeasonEventWaitingListController::class, 'deletePage'])->name('deletePage');
        Route::delete('/delete/{waitingListID}', [SeasonEventWaitingListController::class, 'destroy'])->name('destroy');
    });

});
