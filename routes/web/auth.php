<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LoginController;

/*
|--------------------------------------------------------------------------
| Public / UI Pages
|--------------------------------------------------------------------------

/*
|--------------------------------------------------------------------------
| Auth (Login / Register / Forgot Password)
|--------------------------------------------------------------------------
*/
Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['ar', 'en'])
    ->name('locale.switch');

Route::get('/login-auth', [LoginController::class, 'show'])->name('login-auth');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1')->name('login');

Route::get('/forgot-password', [ForgotPasswordController::class, 'showForm'])
    ->name('forgot-password.form');

Route::post('/forgot-password', [ForgotPasswordController::class, 'handle'])
    ->middleware('throttle:5,1')
    ->name('forgot-password.handle');

Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])
    ->name('password.reset');

Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])
    ->middleware('throttle:5,1')
    ->name('password.reset.submit');
