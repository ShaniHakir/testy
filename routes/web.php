<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\GpgController;

// Home Route
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Registration Routes
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Login Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Profile Route
Route::get('/profile', [ProfileController::class, 'show'])->name('profile');

// Settings Routes
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::get('/settings/password', [SettingsController::class, 'password'])->name('settings.password');
Route::get('/settings/pin', [SettingsController::class, 'pin'])->name('settings.pin');
Route::get('/settings/jabber', [SettingsController::class, 'jabber'])->name('settings.jabber');
Route::get('/settings/about', [SettingsController::class, 'about'])->name('settings.about');
Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
Route::put('/settings/pin', [SettingsController::class, 'updatePin'])->name('settings.pin.update');
Route::put('/settings/jabber', [SettingsController::class, 'updateJabber'])->name('settings.jabber.update');
Route::put('/settings/about', [SettingsController::class, 'updateAbout'])->name('settings.about.update');

// GPG
Route::get('/settings/gpg', [GpgController::class, 'showGpgSettings'])->name('settings.gpg');
Route::post('/settings/gpg/store', [GpgController::class, 'store'])->name('gpg.store');
Route::get('/settings/gpg/verify', [GpgController::class, 'verify'])->name('gpg.verify');
Route::post('/settings/gpg/verify', [GpgController::class, 'checkVerification'])->name('gpg.checkVerification');
Route::delete('/settings/gpg', [GpgController::class, 'delete'])->name('gpg.delete');
Route::post('/settings/gpg/toggle2fa', [GpgController::class, 'toggle2fa'])->name('gpg.toggle2fa');
Route::post('/two_factor/verify', [App\Http\Controllers\Auth\LoginController::class, 'verifyTwoFactor'])->name('two_factor.verify');
