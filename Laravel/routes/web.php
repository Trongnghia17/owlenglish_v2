<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\AuthApiController;

/*
|--------------------------------------------------------------------------
| Role System Definitions
|--------------------------------------------------------------------------
| 0: Admin - Quản lý tài khoản Admin (highest authority)
| 1: Teacher Teaching - Giáo viên giảng dạy
| 2: Teacher Grading - Giáo viên chấm sửa bài
| 3: Teacher Content - Giáo viên làm đề, chủ đề
| 4: Student Care - Chăm sóc học viên
| 5: Assistant Content - Trợ lý chuyên môn làm đề, chủ đề
| 6: Student Center - Học viên trung tâm
| 7: Student Visitor - Học viên vãng lai (basic access)
|
| Note: Lower number = Higher permission level
*/

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
Route::middleware(['auth', 'role:0'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', UserController::class);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
    Route::post('/users/bulk-update-role', [UserController::class, 'bulkUpdateRole'])->name('users.bulk-update-role');
    Route::get('/api/role-stats', [UserController::class, 'getRoleStats'])->name('api.role-stats');

    // Admin Settings (placeholder)
    Route::get('/settings', function() {
        return view('admin.settings.index');
    })->name('settings');
});

/*
|--------------------------------------------------------------------------
| Placeholder Dashboard Routes
|--------------------------------------------------------------------------
*/
// Teacher Dashboard placeholder
Route::middleware(['auth', 'role:1,2,3'])->get('/teacher/dashboard', function() {
    return view('dashboard.placeholder', [
        'role' => 'teacher',
        'title' => 'Dashboard Giáo viên',
        'description' => 'Chức năng dành cho giáo viên đang được phát triển'
    ]);
})->name('teacher.dashboard');

// Assistant Dashboard placeholder
Route::middleware(['auth', 'role:4,5'])->get('/assistant/dashboard', function() {
    return view('dashboard.placeholder', [
        'role' => 'assistant',
        'title' => 'Dashboard Trợ lý',
        'description' => 'Chức năng dành cho trợ lý đang được phát triển'
    ]);
})->name('assistant.dashboard');

// Student Dashboard placeholder
Route::middleware(['auth', 'role:6,7'])->get('/student/dashboard', function() {
    return view('dashboard.placeholder', [
        'role' => 'student',
        'title' => 'Dashboard Học viên',
        'description' => 'Chức năng dành cho học viên đang được phát triển'
    ]);
})->name('student.dashboard');

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



