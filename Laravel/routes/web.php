<?php

use App\Http\Controllers\Api\AuthApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ExamController as AdminExamController;
use App\Http\Controllers\Admin\ExamTestController;
use App\Http\Controllers\Admin\ExamSkillController;
use App\Http\Controllers\Admin\ExamSectionController;
use App\Http\Controllers\Admin\ExamQuestionController;
use App\Http\Controllers\Admin\ImageUploadController;
use App\Http\Controllers\Admin\StudentController;

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

Route::middleware(['auth', 'role:1,2,3,4,5'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Image Upload for Editor
    Route::post('/upload-image', [ImageUploadController::class, 'upload'])->name('upload.image');

    // User management routes - only for Super Admin and Org Admin
    Route::middleware('role:1,2')->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');
        Route::put('users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');

        Route::resource('students', StudentController::class);
        Route::patch('students/{student}/toggle-status', [StudentController::class, 'toggleStatus'])->name('students.toggleStatus');
        Route::post('students/{student}/restore', [StudentController::class, 'restore'])->name('students.restore');
        Route::delete('students/{student}/force-delete', [StudentController::class, 'forceDelete'])->name('students.forceDelete');
    });

    // Exam Management Routes
    Route::resource('exams', AdminExamController::class);
    Route::patch('exams/{exam}/toggle-active', [AdminExamController::class, 'toggleActive'])->name('exams.toggle-active');
    
    // Exam Test Management Routes (Nested Resource)
    Route::prefix('exams/{exam}')->name('exams.')->group(function () {
        Route::resource('tests', ExamTestController::class);
        Route::post('tests/{test}/duplicate', [ExamTestController::class, 'duplicate'])->name('tests.duplicate');
        
        // Exam Skill Management Routes (Nested under Test)
        Route::prefix('tests/{test}')->name('tests.')->group(function () {
            Route::resource('skills', ExamSkillController::class);
            
            // Exam Section Management Routes (Nested under Skill)
            Route::prefix('skills/{skill}')->name('skills.')->group(function () {
                Route::resource('sections', ExamSectionController::class);
                
                // Exam Question Management Routes (Nested under Section)
                Route::prefix('sections/{section}')->name('sections.')->group(function () {
                    Route::resource('questions', ExamQuestionController::class);
                });
            });
        });
    });

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
