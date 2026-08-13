<?php

use App\Http\Controllers\AdminCustodyRequestController;
use App\Http\Controllers\AdminPlaceBookingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryIssueController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\SecretaryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Secretary (SuperAdmin|Secretary)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminSecretary|Secretary', 'can.permission:web.secretary.manage'])->group(function () {
    Route::get('/secretary', [SecretaryController::class, 'index'])->name('secretary.index');
    Route::get('/secretary/add', [SecretaryController::class, 'create'])->name('secretary.create');
    Route::post('/secretary/insert', [SecretaryController::class, 'insert'])->name('secretary.insert');

    Route::get('/secretary/edit/{id}', [SecretaryController::class, 'edit'])->name('secretary.edit');
    Route::patch('/secretary/update/{id}', [SecretaryController::class, 'updates'])->name('secretary.update');

    Route::get('/secretary/download/{id}', [SecretaryController::class, 'download'])->name('secretary.download');

    Route::get('/secretary/delete/{id}', [SecretaryController::class, 'deletes'])->name('secretary.delete');
    Route::delete('/secretary/destroy/{id}', [SecretaryController::class, 'destroy'])->name('secretary.destroy');

    Route::post('/secretary/upload', [SecretaryController::class, 'upload'])->name('secretary.upload');

    Route::get('/admin/place-bookings', [AdminPlaceBookingController::class, 'index'])->name('admin.place_bookings.index');
    Route::get('/admin/place-bookings/{id}', [AdminPlaceBookingController::class, 'show'])->name('admin.place_bookings.show');

    Route::post('/admin/place-bookings/{id}/approve', [AdminPlaceBookingController::class, 'approve'])->name('admin.place_bookings.approve');
    Route::post('/admin/place-bookings/{id}/reject', [AdminPlaceBookingController::class, 'reject'])->name('admin.place_bookings.reject');
    Route::post('/admin/place-bookings/{id}/approve-edit', [AdminPlaceBookingController::class, 'approveWithEdit'])->name('admin.place_bookings.approve_edit');

    // Events (SuperAdmin included via checkAuth list; delete/destroy stay SuperAdmin-only)
    Route::get('/event', [EventController::class, 'index'])->name('event.index');
    Route::get('/event/add-recursive', [EventController::class, 'createRecursive'])->name('event.create-recursive');
    Route::post('/event/insert-recursive', [EventController::class, 'insertRecursive'])->name('event.insert-recursive');
    Route::get('/event/add', [EventController::class, 'create'])->name('event.create');
    Route::post('/event/insert', [EventController::class, 'insert'])->name('event.insert');
    Route::get('/event/edit/{id}', [EventController::class, 'edit'])->name('event.edit');
    Route::patch('/event/update/{id}', [EventController::class, 'updates'])->name('event.update');

    // Locations
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('/locations/create', [LocationController::class, 'create'])->name('locations.create');
    Route::post('/locations/insert', [LocationController::class, 'insert'])->name('locations.insert');
    Route::get('/locations/edit/{id}', [LocationController::class, 'edit'])->name('locations.edit');
    Route::patch('/locations/updates/{id}', [LocationController::class, 'updates'])->name('locations.updates');
    Route::get('/locations/deletes/{id}', [LocationController::class, 'deletes'])->name('locations.deletes');
    Route::delete('/locations/destroy/{id}', [LocationController::class, 'destroy'])->name('locations.destroy');

    // PlaceTypes
    Route::get('/place', [PlaceController::class, 'index'])->name('place.index');
    Route::get('/place/add', [PlaceController::class, 'create'])->name('place.create');
    Route::post('/place/insert', [PlaceController::class, 'insert'])->name('place.insert');
    Route::get('/place/edit/{id}', [PlaceController::class, 'edit'])->name('place.edit');
    Route::patch('/place/update/{id}', [PlaceController::class, 'updates'])->name('place.update');
    Route::get('/place/delete/{id}', [PlaceController::class, 'deletes'])->name('place.delete');
    Route::delete('/place/destroy/{id}', [PlaceController::class, 'destroy'])->name('place.destroy');

});
Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminInventory', 'can.permission:web.inventory.review'])->group(function () {

    Route::post('/admin/custody-requests/{id}/approve', [AdminCustodyRequestController::class, 'approve'])->name('admin.custody_requests.approve');
    Route::post('/admin/custody-requests/{id}/reject', [AdminCustodyRequestController::class, 'reject'])->name('admin.custody_requests.reject');

});

Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminInventory|Inventory', 'can.permission:web.inventory.manage|web.inventory.review'])->group(function () {
    Route::get('/admin/custody-requests', [AdminCustodyRequestController::class, 'index'])->name('admin.custody_requests.index');
    Route::get('/admin/custody-requests/{id}', [AdminCustodyRequestController::class, 'show'])->name('admin.custody_requests.show');
});

Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminInventory|Inventory', 'can.permission:web.inventory.manage'])->group(function () {

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/add', [InventoryController::class, 'create'])->name('inventory.create');
    Route::post('/inventory/insert', [InventoryController::class, 'insert'])->name('inventory.insert');
    Route::get('/inventory/edit/{id}', [InventoryController::class, 'edit'])->name('inventory.edit');
    Route::patch('/inventory/update/{id}', [InventoryController::class, 'updates'])->name('inventory.update');
    Route::get('/inventory/delete/{id}', [InventoryController::class, 'deletes'])->name('inventory.delete');
    Route::delete('/inventory/destroy/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    // Inventory Issue
    Route::get('/inventory-issue', [InventoryIssueController::class, 'index'])->name('inventory-issue.index');
    Route::get('/inventory-issue/getEventsForSeason', [InventoryIssueController::class, 'getEventsForSeason'])->name('inventory-issue.getEventsForSeason');
    Route::post('/inventory-issue/generate', [InventoryIssueController::class, 'generate'])->name('inventory-issue.generate');

});
