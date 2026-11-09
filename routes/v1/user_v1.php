<?php

use App\Http\Controllers\V1\User\UserAuthenticationController;
use App\Http\Controllers\V1\User\UserCategoryController;
use App\Http\Controllers\V1\User\UserOrderController;
use App\Http\Controllers\V1\User\UserProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {
    Route::get('/categories', [UserCategoryController::class, 'index']);
    Route::get('/categories/{category}/products', [UserProductController::class, 'index']);
    Route::get('/products/{product}', [UserProductController::class, 'show']);

    Route::post('/register', [UserAuthenticationController::class, 'register']);
    Route::post('/login', [UserAuthenticationController::class, 'login'])->middleware('throttle:user_login');

    Route::middleware(['auth:sanctum', 'auth.user', 'check.device', 'not_verified'])->group(function () {
        Route::post('/verify-email', [UserAuthenticationController::class, 'verify_email'])->middleware('throttle:email_verification');
        Route::post('/resend-verification-code', [UserAuthenticationController::class, 'resend_verification_code'])->middleware('throttle:resend_verification');
    });

    Route::middleware(['auth:sanctum', 'auth.user', 'check.device', 'verified'])->group(function () {
        Route::apiResource('orders', UserOrderController::class)->only(['index', 'store', 'show']);

    });
});
