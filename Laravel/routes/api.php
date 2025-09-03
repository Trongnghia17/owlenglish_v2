<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;

Route::get('/oauth/google/redirect', [AuthApiController::class, 'googleRedirect'])->name('oauth.google.redirect');
Route::get('/oauth/google/callback', [AuthApiController::class, 'googleCallback'])->name('oauth.google.callback');
