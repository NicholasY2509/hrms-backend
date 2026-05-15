<?php

use App\Modules\CertificateOfEmployment\Controllers\V1\Portal\Employee\CertificateOfEmploymentController as EmployeeCoeController;
use App\Modules\CertificateOfEmployment\Controllers\V1\Portal\Management\CertificateOfEmploymentManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    // Portal Routes
    Route::prefix('portal')->group(function () {
        // Employee Self-Service
        Route::prefix('employee')->group(function () {
            Route::get('certificate-of-employments', [EmployeeCoeController::class, 'index']);
            Route::post('certificate-of-employments', [EmployeeCoeController::class, 'store']);
        });

        // Operational Oversight (Management)
        Route::prefix('management')->group(function () {
            Route::get('certificate-of-employments', [CertificateOfEmploymentManagementController::class, 'index']);
            Route::post('certificate-of-employments', [CertificateOfEmploymentManagementController::class, 'store']);
            Route::get('certificate-of-employments/{certificate_of_employment}', [CertificateOfEmploymentManagementController::class, 'show']);
            Route::post('certificate-of-employments/{certificate_of_employment}/settle', [CertificateOfEmploymentManagementController::class, 'settle']);
            Route::post('certificate-of-employments/{certificate_of_employment}/export', [CertificateOfEmploymentManagementController::class, 'export']);
        });
    });
});
