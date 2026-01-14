<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ExamCollectionController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\ExamFilterController;
use App\Http\Controllers\Api\ExamTestController;
use App\Http\Controllers\Api\ExamSkillController;
use App\Http\Controllers\Api\ExamSectionController;
use App\Http\Controllers\Api\ExamQuestionController;
use App\Http\Controllers\Api\PaymentPackageController;
use App\Http\Controllers\Api\PayosWebhookController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TestResultController;
use App\Http\Controllers\Api\UserNoteController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', function (Request $request) {
        return $request->user();
    });
    Route::post('/user/profile', [StudentController::class, 'updateProfile']);
    Route::get('/user/login-history', [StudentController::class, 'loginHistory']);
    Route::post('/user/device/action', [StudentController::class, 'deviceAction']);
    Route::post('/payments/create', [PaymentPackageController::class, 'create']);
    
    // Test Results
    Route::post('/test-results/submit', [TestResultController::class, 'submit']);
    Route::post('/test-results/draft', [TestResultController::class, 'saveDraft']);
    Route::get('/test-results/{id}', [TestResultController::class, 'show']);
    Route::get('/test-results', [TestResultController::class, 'history']);
    
    // User Notes
    Route::get('/user-notes', [UserNoteController::class, 'index']);
    Route::post('/user-notes', [UserNoteController::class, 'store']);
    Route::get('/user-notes/{id}', [UserNoteController::class, 'show']);
    Route::put('/user-notes/{id}', [UserNoteController::class, 'update']);
    Route::delete('/user-notes/{id}', [UserNoteController::class, 'destroy']);
});

Route::prefix('otp')->group(function () {
    // Gửi mã OTP
    Route::post('/send', [AuthApiController::class, 'sendOtp']);
    // Xác thực OTP
    Route::post('/verify', [AuthApiController::class, 'verifyOtp']);
});

Route::post('/login', [AuthApiController::class, 'login']);

// Public routes (không cần auth) - cho học viên xem và làm bài , xem gói cú
Route::prefix('public')->group(function () {
    // Lấy danh sách exams public
    Route::get('/exams', [ExamController::class, 'index']);
    Route::get('/exam-collections', [ExamController::class, 'getExamCollections']);
    Route::get('/section-filters', [ExamController::class, 'getSectionFilters']);
    Route::get('/exams/{id}', [ExamController::class, 'show']);
    Route::get('exams_detail/{examId}', [ExamController::class, 'getExamDetails']);
    Route::get('get_listening/{skillId}/{sectionId?}', [ExamController::class, 'getListeningContent']);
    // Lấy test details
    Route::get('/tests/{id}', [ExamTestController::class, 'show']);

    // Lấy danh sách skills public
    Route::get('/skills', [ExamSkillController::class, 'index']);

    // Lấy skill details
    Route::get('/skills/{id}', [ExamSkillController::class, 'show']);

    // Lấy section details
    Route::get('/sections/{id}', [ExamSectionController::class, 'show']);

    // Lấy question group và questions
    Route::get('/question-groups/{id}', [ExamQuestionController::class, 'showGroup']);
    Route::get('/question-groups/{groupId}/questions', [ExamQuestionController::class, 'indexQuestions']);

    // Lấy danh sách gói nạp tiền
    Route::get('/payment-packages', [PaymentPackageController::class, 'index']);
    Route::post('/payos/webhook', [PayosWebhookController::class, 'handle']);

    Route::get('/collections', [ExamCollectionController::class, 'index']);
    Route::get('/filters', [ExamFilterController::class, 'index']);
});
Route::get('/payment/success/{orderCode}', [PaymentPackageController::class, 'success']);
Route::get('/payment/cancel/{orderCode}', [PaymentPackageController::class, 'cancel']);
// Protected routes (cần auth) - REMOVED - Admin uses Blade views, not API
// Students only need public read-only routes above
