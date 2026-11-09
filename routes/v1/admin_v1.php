<?php

use App\Http\Controllers\V1\Admin\AdminAuthenticationController;
use App\Http\Controllers\V1\Admin\AdminCategoryController;
use App\Http\Controllers\V1\Admin\AdminOrderController;
use App\Http\Controllers\V1\Admin\AdminProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthenticationController::class, 'login'])->middleware('throttle:admin_login');

    Route::middleware(['auth:sanctum', 'auth.admin', 'check.device'])->group(function () {
        Route::apiResource('categories', AdminCategoryController::class);
        Route::get('categories/{category}/products', [AdminProductController::class, 'index']);
        Route::post('products', [AdminProductController::class, 'store']);
        Route::get('products/{product}', [AdminProductController::class, 'show']);
        Route::put('products/{product}', [AdminProductController::class, 'update']);
        Route::patch('products/{product}', [AdminProductController::class, 'update']);
        Route::delete('products/{product}', [AdminProductController::class, 'destroy']);
        Route::post('products/{product}/images', [AdminProductController::class, 'storeImages']);
        Route::delete('products/{product}/images/{productImage}', [AdminProductController::class, 'destroyImage']);
        Route::get('orders', [AdminOrderController::class, 'index']);
        Route::get('orders/statuses', [AdminOrderController::class, 'statuses']);
        Route::get('orders/{order}', [AdminOrderController::class, 'show']);
        Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
    });
});
