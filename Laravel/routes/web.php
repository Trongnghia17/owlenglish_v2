<?php

use App\Http\Controllers\Api\AuthApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;


/*
|--------------------------------------------------------------------------
| Home Route
|--------------------------------------------------------------------------
*/
Route::get('/', [AuthController::class, 'home'])->name('home');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Role 0)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'permission:manage_users'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Admin Settings (placeholder)
    Route::get('/settings', function() {
        return view('admin.settings.index');
    })->name('settings');
});

/*
|--------------------------------------------------------------------------
| Error Pages
|--------------------------------------------------------------------------
*/
Route::prefix('error')->group(function () {
    Route::get('/403', function () {
        return view('errors.403');
    })->name('error.403');

    Route::get('/404', function () {
        return view('errors.404');
    })->name('error.404');

    Route::get('/500', function () {
        return view('errors.500');
    })->name('error.500');
});

Route::get('/oauth/google/redirect', [AuthApiController::class, 'googleRedirect'])->name('oauth.google.redirect');
Route::get('/oauth/google/callback', [AuthApiController::class, 'googleCallback'])->name('oauth.google.callback');

Route::get('/oauth/facebook/redirect', [AuthApiController::class, 'facebookRedirect'])->name('oauth.facebook.redirect');
Route::get('/oauth/facebook/callback', [AuthApiController::class, 'facebookCallback'])->name('oauth.facebook.callback');
