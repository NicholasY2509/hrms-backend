<?php

use App\Modules\Employee\Controllers\V1\Configuration\EmployeeStatusController;
use App\Modules\Employee\Controllers\V1\Portal\Employee\MyProfileController;
use App\Modules\Employee\Controllers\V1\Portal\Employee\FaceController;
use App\Modules\Employee\Controllers\V1\Portal\Management\CertificateOfEmploymentController;
use App\Modules\Employee\Controllers\V1\Portal\Management\EmployeeSearchController;
use App\Modules\Employee\Controllers\V1\Portal\Management\EmployeeManagementController;
use App\Modules\Employee\Controllers\V1\Portal\Management\ResignationController;
use App\Modules\Employee\Controllers\V1\Portal\Management\SupervisorManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {

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
            Route::apiResource('employees', EmployeeManagementController::class);
            Route::apiResource('supervisors', SupervisorManagementController::class);
            Route::apiResource('certificate-of-employments', CertificateOfEmploymentController::class);
            Route::apiResource('resignations', ResignationController::class);
        });

        // Configuration Context
        Route::prefix('configuration')->group(function () {
            Route::apiResource('employee-statuses', EmployeeStatusController::class);
        });

    });

});
