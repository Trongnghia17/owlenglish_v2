<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;

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

