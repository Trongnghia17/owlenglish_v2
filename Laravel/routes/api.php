<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});
