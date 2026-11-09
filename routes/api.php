<?php

use App\Http\Controllers\V1\Mutual\MutualAuthenticationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require __DIR__.'/v1/user_v1.php';
    require __DIR__.'/v1/admin_v1.php';

    Route::controller(MutualAuthenticationController::class)->group(function(){

    Route::post('refresh', 'refresh')->middleware('can.refresh');

        Route::middleware(['auth:sanctum'])->group(function(){
            Route::post('logout', 'logout')->name('logout');
             Route::middleware('verified')->group(function(){
                Route::post('logout-other-devices', 'logoutOtherDevices')->name('logout_other_devices');
            Route::post('logout-all-devices', 'logoutAllDevices')->name('logout_all_devices');
            });
        });

    });
});
