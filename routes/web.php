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
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\MessagesController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Admin\AdminOrderController;

Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::resource('categories', AdminCategoryController::class);
    Route::resource('users', AdminUserController::class);
    Route::post('users/{user}/ban', [AdminUserController::class, 'ban'])->name('users.ban');
    Route::post('users/{user}/unban', [AdminUserController::class, 'unban'])->name('users.unban');
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
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/{product_id}/images/{image_id}/set-default', [ProductController::class, 'setDefaultImage'])
        ->name('products.images.setDefault');
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
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

    // Messaging Functions
    Route::get('/messages', [MessagesController::class, 'index'])->name('messages.index');
    Route::get('/messages/create', [MessagesController::class, 'create'])->name('messages.create');
    Route::get('/messages/{conversation}', [MessagesController::class, 'show'])->name('messages.show');
    Route::post('/messages', [MessagesController::class, 'store'])->name('messages.store');
    Route::delete('/messages/{message}', [MessagesController::class, 'destroy'])->name('messages.destroy');
    Route::delete('/messages', [MessagesController::class, 'deleteAll'])->name('messages.deleteAll');
    Route::delete('/conversations/{conversation}', [MessagesController::class, 'destroyConversation'])->name('conversations.destroy');
    Route::delete('/conversations', [MessagesController::class, 'deleteAllConversations'])->name('conversations.deleteAll');
    Route::post('/messages/mark-all-read', [MessagesController::class, 'markAllAsRead'])->name('messages.markAllRead');

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'addToCart'])->name('cart.add');
    Route::delete('/cart/remove/{cartItem}', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    // Order Routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/accept', [OrderController::class, 'accept'])->name('orders.accept');
    Route::post('/orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete'])->name('orders.complete');
});