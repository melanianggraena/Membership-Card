<?php

use App\Http\Controllers\Api\MemberAuthController;
use App\Http\Controllers\Api\MemberController;
use Illuminate\Support\Facades\Route;

Route::prefix('member/login')->middleware('throttle:5,1')->group(function () {
    Route::post('/request-otp', [MemberAuthController::class, 'requestOtp']);
    Route::post('/verify-otp', [MemberAuthController::class, 'verifyOtp']);
});

Route::prefix('member')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [MemberAuthController::class, 'logout']);
    Route::get('/profile', [MemberController::class, 'profile']);
    Route::put('/profile', [MemberController::class, 'updateProfile']);
    Route::get('/membership', [MemberController::class, 'membership']);
    Route::get('/balance', [MemberController::class, 'balance']);
    Route::get('/home', [MemberController::class, 'home']);
    Route::get('/transactions', [MemberController::class, 'transactions']);
    Route::get('/transactions/{id}', [MemberController::class, 'transaction']);
    Route::get('/access-history', [MemberController::class, 'accesses']);
    Route::get('/access-history/{id}', [MemberController::class, 'access']);
    Route::get('/promos', [MemberController::class, 'promos']);
    Route::get('/promos/{id}', [MemberController::class, 'promo']);
});
