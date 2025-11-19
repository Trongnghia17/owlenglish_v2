<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\ExamTestController;
use App\Http\Controllers\Api\ExamSkillController;
use App\Http\Controllers\Api\ExamSectionController;
use App\Http\Controllers\Api\ExamQuestionController;

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});

Route::prefix('otp')->group(function () {
    // Gửi mã OTP
    Route::post('/send', [AuthApiController::class, 'sendOtp']);
    // Xác thực OTP
    Route::post('/verify', [AuthApiController::class, 'verifyOtp']);
});

Route::post('/login', [AuthApiController::class, 'login']);

// ==================== EXAM MANAGEMENT ROUTES ====================

// Public routes (không cần auth) - cho học viên xem và làm bài
Route::prefix('public')->group(function () {
    // Lấy danh sách exams public
    Route::get('/exams', [ExamController::class, 'index']);
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
});

// Protected routes (cần auth) - REMOVED - Admin uses Blade views, not API
// Students only need public read-only routes above
