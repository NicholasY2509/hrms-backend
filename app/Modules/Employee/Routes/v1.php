<?php

use App\Modules\Employee\Controllers\V1\Portal\Employee\MyProfileController;
use App\Modules\Employee\Controllers\V1\Portal\Employee\FaceController;
use App\Modules\Employee\Controllers\V1\Portal\Management\EmployeeSearchController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\SyncUserByEmail::class])->group(function () {

    Route::prefix('portal')->group(function () {
        
        // Employee Context
        Route::prefix('employee')->group(function () {
            Route::get('/profile', [MyProfileController::class, 'profile']);
            
            // Face Recognition
            Route::prefix('face')->group(function () {
                Route::get('/status', [FaceController::class, 'status']);
                Route::post('/register', [FaceController::class, 'register']);
                Route::post('/verify', [FaceController::class, 'verify']);
            });
        });

        // Management Context
        Route::prefix('management')->group(function () {
            Route::get('/search', EmployeeSearchController::class);
        });

    });

});
