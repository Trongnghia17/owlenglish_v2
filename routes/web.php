<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
| Role System Definitions:
| 0: Admin - Quản lý tài khoản Admin (highest authority)
| 1: Teacher Teaching - Giáo viên giảng dạy
| 2: Teacher Grading - Giáo viên chấm sửa bài
| 3: Teacher Content - Giáo viên làm đề, chủ đề
| 4: Student Care - Chăm sóc học viên
| 5: Assistant Content - Trợ lý chuyên môn làm đề, chủ đề (supports Teacher Content)
| 6: Student Center - Học viên trung tâm
| 7: Student Visitor - Học viên vãng lai (basic access)
|
| Note: Lower number = Higher permission level
*/

/*
|--------------------------------------------------------------------------
| Home & Default Routes
|--------------------------------------------------------------------------
*/

// Home route - Smart redirect based on user role
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();

        // Check if user is active
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa.');
        }
        // Redirect based on role hierarchy
        switch ($user->role) {
            case 0: // Admin
                return redirect()->route('admin.dashboard');
            case 1:
            case 2:
            case 3: // All Teacher types
                return redirect()->route('teacher.dashboard');
            case 4:
            case 5: // All Assistant types
                return redirect()->route('assistant.dashboard');
            case 6:
            case 7: // All Student types
            default:
                return redirect()->route('student.dashboard');
        }
    }

    return redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Error & Status Pages
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
/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Guest only routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Authenticated user routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Role 0 - Highest Authority)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:0'])->prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // User Management - Complete CRUD for all users
    Route::resource('users', UserController::class);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggleStatus');

    // Bulk operations
    Route::post('/users/bulk-update-role', [UserController::class, 'bulkUpdateRole'])->name('users.bulk-update-role');

    // API endpoints for stats
    Route::get('/api/role-stats', [UserController::class, 'getRoleStats'])->name('api.role-stats');

    // Admin Settings
    Route::get('/settings', function() {
        return view('admin.settings.index');
    })->name('settings');
});

/*
|--------------------------------------------------------------------------
| Teacher Routes - Nhóm giáo viên chính (1,2,3)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:0,1,2,3'])->prefix('teacher')->name('teacher.')->group(function () {

    // Teacher Dashboard
    Route::get('/dashboard/{role?}', function($role = null) {
        return view('teacher.dashboard', compact('role'));
    })->name('dashboard');

    // Teaching Management (Role 1 - Teacher Teaching)
    Route::middleware('role:0,1')->prefix('teaching')->name('teaching.')->group(function () {
        Route::get('/classes', function() { return view('teacher.teaching.classes.index'); })->name('classes.index');
        Route::get('/lessons', function() { return view('teacher.teaching.lessons.index'); })->name('lessons.index');
        Route::get('/schedule', function() { return view('teacher.teaching.schedule.index'); })->name('schedule.index');
    });

    // Grading Management (Role 2 - Teacher Grading)
    Route::middleware('role:0,2')->prefix('grading')->name('grading.')->group(function () {
        Route::get('/pending', function() { return view('teacher.grading.pending'); })->name('pending');
        Route::get('/completed', function() { return view('teacher.grading.completed'); })->name('completed');
        Route::get('/statistics', function() { return view('teacher.grading.statistics'); })->name('statistics');
    });

    // Content Creation (Role 3 - Teacher Content + Role 5 - Assistant Content)
    Route::middleware('role:0,3,5')->prefix('content')->name('content.')->group(function () {
        Route::get('/questions', function() { return view('teacher.content.questions.index'); })->name('questions.index');
        Route::get('/exams', function() { return view('teacher.content.exams.index'); })->name('exams.index');
        Route::get('/skills', function() { return view('teacher.content.skills.index'); })->name('skills.index');
    });
});

/*
|--------------------------------------------------------------------------
| Assistant Routes - Nhóm trợ lý (4,5)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:0,4,5'])->prefix('assistant')->name('assistant.')->group(function () {

    // Assistant Dashboard
    Route::get('/dashboard/{role?}', function($role = null) {
        return view('assistant.dashboard', compact('role'));
    })->name('dashboard');

    // Student Care Management (Role 4 - Student Care)
    Route::middleware('role:0,4')->prefix('student-care')->name('student-care.')->group(function () {
        Route::get('/students', function() { return view('assistant.student-care.students.index'); })->name('students.index');
        Route::get('/support', function() { return view('assistant.student-care.support.index'); })->name('support.index');
        Route::get('/feedback', function() { return view('assistant.student-care.feedback.index'); })->name('feedback.index');
    });

    // Content Assistant (Role 5 - Assistant Content)
    Route::middleware('role:0,5')->prefix('content-assist')->name('content-assist.')->group(function () {
        Route::get('/draft', function() { return view('assistant.content-assist.draft.index'); })->name('draft.index');
        Route::get('/review', function() { return view('assistant.content-assist.review.index'); })->name('review.index');
        Route::get('/materials', function() { return view('assistant.content-assist.materials.index'); })->name('materials.index');
    });
});

/*
|--------------------------------------------------------------------------
| Student Routes - Nhóm học sinh (6,7)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:6,7'])->prefix('student')->name('student.')->group(function () {

    // Student Dashboard
    Route::get('/dashboard', function() {
        return view('student.dashboard');
    })->name('dashboard');

    // Learning Routes
    Route::get('/courses', function() { return view('student.courses.index'); })->name('courses.index');
    Route::get('/tests/available', function() { return view('student.tests.available'); })->name('tests.available');
    Route::get('/tests/completed', function() { return view('student.tests.completed'); })->name('tests.completed');
    Route::get('/tests/results', function() { return view('student.tests.results'); })->name('tests.results');
    Route::get('/progress', function() { return view('student.progress.index'); })->name('progress.index');

    // Student Center only features (Role 6)
    Route::middleware('role:6')->group(function() {
        Route::get('/certificates', function() { return view('student.advanced.certificates'); })->name('advanced.certificates');
    });
});

/*
|--------------------------------------------------------------------------
| Common Routes - Tất cả người dùng đã đăng nhập
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Profile Management
    Route::prefix('profile')->name('profile.')->group(function() {
        Route::get('/', function() { return view('profile.index'); })->name('index');
        Route::get('/activity', function() { return view('profile.activity'); })->name('activity');
        Route::get('/settings', function() { return view('profile.settings'); })->name('settings');
    });

    // Calendar & Messages
    Route::get('/calendar', function() { return view('calendar.index'); })->name('calendar.index');
    Route::get('/messages', function() { return view('messages.index'); })->name('messages.index');

    // Help & Support
    Route::get('/help', function() { return view('help.index'); })->name('help.index');
    Route::get('/settings/language', function() { return view('settings.language'); })->name('settings.language');

    // Legal Pages
    Route::get('/privacy', function() { return view('legal.privacy'); })->name('privacy');
    Route::get('/terms', function() { return view('legal.terms'); })->name('terms');
    Route::get('/cookies', function() { return view('legal.cookies'); })->name('cookies');
});

/*
|--------------------------------------------------------------------------
| Error & Status Pages
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
