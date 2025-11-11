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
    
    // Lấy test details
    Route::get('/tests/{id}', [ExamTestController::class, 'show']);
    
    // Lấy skill details
    Route::get('/skills/{id}', [ExamSkillController::class, 'show']);
    
    // Lấy section details
    Route::get('/sections/{id}', [ExamSectionController::class, 'show']);
    
    // Lấy question group và questions
    Route::get('/question-groups/{id}', [ExamQuestionController::class, 'showGroup']);
    Route::get('/question-groups/{groupId}/questions', [ExamQuestionController::class, 'indexQuestions']);
});

// Protected routes (cần auth) - cho admin quản lý
Route::middleware(['auth:sanctum'])->group(function () {
    
    // ==================== EXAMS ====================
    Route::prefix('exams')->group(function () {
        Route::get('/', [ExamController::class, 'index']);
        Route::post('/', [ExamController::class, 'store']);
        Route::get('/{id}', [ExamController::class, 'show']);
        Route::put('/{id}', [ExamController::class, 'update']);
        Route::patch('/{id}', [ExamController::class, 'update']);
        Route::delete('/{id}', [ExamController::class, 'destroy']);
        Route::post('/{id}/restore', [ExamController::class, 'restore']);
        Route::post('/{id}/toggle-active', [ExamController::class, 'toggleActive']);
        
        // Tests under exam
        Route::get('/{examId}/tests', [ExamTestController::class, 'index']);
        Route::post('/{examId}/tests', [ExamTestController::class, 'store']);
        Route::post('/{examId}/tests/reorder', [ExamTestController::class, 'reorder']);
    });
    
    // ==================== TESTS ====================
    Route::prefix('tests')->group(function () {
        Route::get('/{id}', [ExamTestController::class, 'show']);
        Route::put('/{id}', [ExamTestController::class, 'update']);
        Route::patch('/{id}', [ExamTestController::class, 'update']);
        Route::delete('/{id}', [ExamTestController::class, 'destroy']);
        Route::post('/{id}/duplicate', [ExamTestController::class, 'duplicate']);
        
        // Skills under test
        Route::get('/{testId}/skills', [ExamSkillController::class, 'index']);
        Route::post('/{testId}/skills', [ExamSkillController::class, 'store']);
        Route::post('/{testId}/skills/reorder', [ExamSkillController::class, 'reorder']);
    });
    
    // ==================== SKILLS ====================
    Route::prefix('skills')->group(function () {
        Route::get('/{id}', [ExamSkillController::class, 'show']);
        Route::put('/{id}', [ExamSkillController::class, 'update']);
        Route::patch('/{id}', [ExamSkillController::class, 'update']);
        Route::delete('/{id}', [ExamSkillController::class, 'destroy']);
        
        // Sections under skill
        Route::get('/{skillId}/sections', [ExamSectionController::class, 'index']);
        Route::post('/{skillId}/sections', [ExamSectionController::class, 'store']);
        Route::post('/{skillId}/sections/reorder', [ExamSectionController::class, 'reorder']);
    });
    
    // ==================== SECTIONS ====================
    Route::prefix('sections')->group(function () {
        Route::get('/{id}', [ExamSectionController::class, 'show']);
        Route::put('/{id}', [ExamSectionController::class, 'update']);
        Route::patch('/{id}', [ExamSectionController::class, 'update']);
        Route::delete('/{id}', [ExamSectionController::class, 'destroy']);
        
        // Question groups under section
        Route::get('/{sectionId}/question-groups', [ExamQuestionController::class, 'indexGroups']);
        Route::post('/{sectionId}/question-groups', [ExamQuestionController::class, 'storeGroup']);
    });
    
    // ==================== QUESTION GROUPS ====================
    Route::prefix('question-groups')->group(function () {
        Route::get('/{id}', [ExamQuestionController::class, 'showGroup']);
        Route::put('/{id}', [ExamQuestionController::class, 'updateGroup']);
        Route::patch('/{id}', [ExamQuestionController::class, 'updateGroup']);
        Route::delete('/{id}', [ExamQuestionController::class, 'destroyGroup']);
        
        // Questions under group
        Route::get('/{groupId}/questions', [ExamQuestionController::class, 'indexQuestions']);
        Route::post('/{groupId}/questions', [ExamQuestionController::class, 'storeQuestion']);
        Route::post('/{groupId}/questions/bulk', [ExamQuestionController::class, 'bulkStoreQuestions']);
        Route::post('/{groupId}/questions/reorder', [ExamQuestionController::class, 'reorderQuestions']);
    });
    
    // ==================== QUESTIONS ====================
    Route::prefix('questions')->group(function () {
        Route::get('/{id}', [ExamQuestionController::class, 'showQuestion']);
        Route::put('/{id}', [ExamQuestionController::class, 'updateQuestion']);
        Route::patch('/{id}', [ExamQuestionController::class, 'updateQuestion']);
        Route::delete('/{id}', [ExamQuestionController::class, 'destroyQuestion']);
    });
});

