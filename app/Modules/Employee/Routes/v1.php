<?php

use App\Modules\Employee\Controllers\V1\Configuration\EmployeeStatusController;
use App\Modules\Employee\Controllers\V1\Portal\Employee\MyProfileController;
use App\Modules\Employee\Controllers\V1\Portal\Employee\FaceController;
use App\Modules\Employee\Controllers\V1\Portal\Employee\MyResignationController;
use App\Modules\Employee\Controllers\V1\Portal\Management\CertificateOfEmploymentController;
use App\Modules\Employee\Controllers\V1\Portal\Management\EmployeeSearchController;
use App\Modules\Employee\Controllers\V1\Portal\Management\EmployeeManagementController;
use App\Modules\Employee\Controllers\V1\Portal\Management\EmployeeDetailController;
use App\Modules\Employee\Controllers\V1\Portal\Management\ResignationController;
use App\Modules\Employee\Controllers\V1\Portal\Management\SupervisorManagementController;
use App\Modules\Employee\Controllers\V1\Portal\Management\SupervisorSearchController;
use App\Modules\Employee\Controllers\V1\Portal\Management\EmployeeTaxProfileController;
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

            // Resignation
            Route::get('resignations', [MyResignationController::class, 'index']);
            Route::post('resignations', [MyResignationController::class, 'store']);
        });

        // Management Context
        Route::prefix('management')
            ->middleware('role:Admin HRD')
            ->group(function () {
                Route::get('/search', EmployeeSearchController::class);
                Route::get('/supervisors-search', SupervisorSearchController::class);
                Route::get('/employees/{id}/details/{type}', [EmployeeDetailController::class, 'show']);
                Route::put('/employees/{id}/details/{type}', [EmployeeDetailController::class, 'update']);
                Route::get('/employees/generate-nik', [EmployeeManagementController::class, 'generateNik']);
                Route::get('/employees/summary', [EmployeeManagementController::class, 'summary']);
                Route::apiResource('employees', EmployeeManagementController::class);
                Route::apiResource('supervisors', SupervisorManagementController::class);
                Route::post('resignations/{resignation}/settle', [ResignationController::class, 'settle']);
                Route::get('resignations/{resignation}/export', [ResignationController::class, 'export']);
                Route::apiResource('resignations', ResignationController::class)->only(['index', 'show']);

                // Tax Profiles
                Route::get('employee-tax-profiles/{employee_id}', [EmployeeTaxProfileController::class, 'show']);
                Route::post('employee-tax-profiles', [EmployeeTaxProfileController::class, 'store']);
            });

        // Configuration Context
        Route::prefix('configuration')
            ->middleware('role:Admin HRD')
            ->group(function () {
                Route::apiResource('employee-statuses', EmployeeStatusController::class);
            });

    });

});
