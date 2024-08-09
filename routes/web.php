<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\GpgController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CategoryController;
use App\Http\Middleware\VendorAdminMiddleware;
use App\Models\ProductImage;


Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::resource('categories', AdminCategoryController::class);
});

// Registration Routes
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Login Routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Home Route
Route::get('/', [HomeController::class, 'index'])->name('home');

// Category Route
Route::get('/category/{id}', [CategoryController::class, 'show'])->name('category.show');

// Profile Route
Route::get('/profile', [ProfileController::class, 'show'])->name('profile');

// GPG
Route::get('/settings/gpg', [GpgController::class, 'showGpgSettings'])->name('settings.gpg');
Route::post('/settings/gpg/store', [GpgController::class, 'store'])->name('gpg.store');
Route::get('/settings/gpg/verify', [GpgController::class, 'verify'])->name('gpg.verify');
Route::post('/settings/gpg/verify', [GpgController::class, 'checkVerification'])->name('gpg.checkVerification');
Route::delete('/settings/gpg', [GpgController::class, 'delete'])->name('gpg.delete');
Route::post('/settings/gpg/toggle2fa', [GpgController::class, 'toggle2fa'])->name('gpg.toggle2fa');
Route::post('/two_factor/verify', [App\Http\Controllers\Auth\LoginController::class, 'verifyTwoFactor'])->name('two_factor.verify');

// Product Routes (only for vendors and admins)
Route::middleware(['auth', 'vendor.admin'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::match(['post', 'patch'], '/products/{product}/images/{image}/set-default', [ProductController::class, 'setDefaultImage'])->name('products.images.setDefault');
    Route::delete('/products/images/{productImage}', [ProductController::class, 'deleteImage'])->name('products.images.delete');

});

Route::middleware(['auth'])->group(function () {
    // Wallets
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');

    // Vendor upgrade routes
    Route::get('/settings/confirm-vendor-upgrade', [VendorController::class, 'showUpgradeConfirmation'])->name('vendor.upgrade');
    Route::post('/settings/upgrade-to-vendor', [VendorController::class, 'upgradeToVendor'])->name('vendor.upgrade.confirm');

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
});