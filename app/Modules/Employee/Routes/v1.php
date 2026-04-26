<?php

use App\Modules\Employee\Controllers\V1\EmployeeController;
use App\Modules\Employee\Controllers\V1\EmployeeSearchController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\SyncUserByEmail::class])->group(function () {
    Route::get('/profile', [EmployeeController::class, 'profile']);

    // Face Recognition
    Route::prefix('face')->group(function () {
        Route::get('/status', [\App\Modules\Employee\Controllers\V1\FaceController::class, 'status']);
        Route::post('/register', [\App\Modules\Employee\Controllers\V1\FaceController::class, 'register']);
        Route::post('/verify', [\App\Modules\Employee\Controllers\V1\FaceController::class, 'verify']);
    });

    Route::get('/search', EmployeeSearchController::class);
});
