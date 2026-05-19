<?php

use App\Modules\System\Controllers\V1\AuthController;
use App\Modules\System\Controllers\V1\Configuration\SystemSettingController;
use App\Modules\System\Controllers\V1\MobileDashboardController;
use App\Modules\System\Controllers\V1\Portal\Employee\MyDashboardController;
use App\Modules\System\Controllers\V1\Portal\Management\ManagementDashboardController;
use App\Modules\System\Controllers\V1\ReportController;
use App\Modules\System\Controllers\V1\SystemController;
use Illuminate\Support\Facades\Route;

// Standard System Routes
Route::get('/test-passport', [SystemController::class, 'testPassport']);

// Auth Routes
Route::middleware(['api.auth'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/mobile-dashboard', [MobileDashboardController::class, 'index']);

    // Employee Portal Routes (Web Dashboard)
    Route::prefix('portal/employee')
        ->controller(MyDashboardController::class)
        ->group(function () {
            Route::get('/dashboard', 'index');
        });

    // Report Routes
    Route::prefix('reports')->controller(ReportController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::get('/{report}', 'show');
    });

    Route::prefix('configuration')
        ->controller(SystemSettingController::class)
        ->middleware('role:Admin HRD')
        ->group(function () {
            Route::get('/settings', 'index');
            Route::post('/settings/bulk', 'bulkUpdate');
        });

    Route::prefix('portal/management')
        ->controller(ManagementDashboardController::class)
        ->middleware('role:Admin HRD')
        ->group(function () {
            Route::get('/dashboard', 'index');
        });
});

