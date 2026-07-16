<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedicineInventoryController;

Route::middleware(['auth', 'checkAuth:SuperAdmin|AdminFirstAid'])->group(function () {
    // Medicine Inventory
    Route::get('/medicine', [MedicineInventoryController::class, 'index'])->name('medicine.index');
    Route::get('/medicine/add', [MedicineInventoryController::class, 'create'])->name('medicine.create');
    Route::post('/medicine/insert', [MedicineInventoryController::class, 'insert'])->name('medicine.insert');
    Route::get('/medicine/edit/{id}', [MedicineInventoryController::class, 'edit'])->name('medicine.edit');
    Route::patch('/medicine/update/{id}', [MedicineInventoryController::class, 'update'])->name('medicine.update');
    Route::get('/medicine/delete/{id}', [MedicineInventoryController::class, 'delete'])->name('medicine.delete');
    Route::delete('/medicine/destroy/{id}', [MedicineInventoryController::class, 'destroy'])->name('medicine.destroy');
    Route::get('/medicine/dispense', [MedicineInventoryController::class, 'dispense'])->name('medicine.dispense');
    Route::post('/medicine/dispense', [MedicineInventoryController::class, 'storeDispense'])->name('medicine.dispense.store');
    Route::get('/medicine/records', [MedicineInventoryController::class, 'records'])->name('medicine.records');
    Route::get('/medicine/locations', [MedicineInventoryController::class, 'locations'])->name('medicine.locations');
    Route::post('/medicine/locations', [MedicineInventoryController::class, 'storeLocation'])->name('medicine.locations.store');
    Route::patch('/medicine/locations/{id}', [MedicineInventoryController::class, 'updateLocation'])->name('medicine.locations.update');
    Route::delete('/medicine/locations/{id}', [MedicineInventoryController::class, 'destroyLocation'])->name('medicine.locations.destroy');
    Route::get('/medicine/locks', [MedicineInventoryController::class, 'locks'])->name('medicine.locks');
    Route::post('/medicine/locks', [MedicineInventoryController::class, 'storeLock'])->name('medicine.locks.store');
    Route::get('/medicine/locks/{id}/release', [MedicineInventoryController::class, 'releaseLock'])->name('medicine.locks.release');
    Route::get('/medicine/{id}/stock', [MedicineInventoryController::class, 'stock'])->name('medicine.stock');
    Route::patch('/medicine/{id}/stock', [MedicineInventoryController::class, 'updateStock'])->name('medicine.stock.update');
    Route::post('/medicine/{id}/restock', [MedicineInventoryController::class, 'restock'])->name('medicine.restock');
    Route::get('/medicine/search-persons', [MedicineInventoryController::class, 'searchPersons'])->name('medicine.search-persons');
});
