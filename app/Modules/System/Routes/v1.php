<?php

use App\Modules\System\Controllers\V1\AuthController;
use App\Modules\System\Controllers\V1\Configuration\SystemSettingController;
use App\Modules\System\Controllers\V1\Configuration\TaskController;
use App\Modules\System\Controllers\V1\MobileDashboardController;
use App\Modules\System\Controllers\V1\Portal\Employee\MyDashboardController;
use App\Modules\System\Controllers\V1\Portal\Management\ManagementDashboardController;
use App\Modules\System\Controllers\V1\ReportController;
use App\Modules\System\Controllers\V1\SystemController;
use App\Modules\System\Controllers\V1\MediaController;
use Illuminate\Support\Facades\Route;

// Standard System Routes
Route::get('/test-passport', [SystemController::class, 'testPassport']);
Route::get('/app-config', [SystemController::class, 'appConfig']);

// Auth Routes
Route::middleware(['api.auth'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/mobile-dashboard', [MobileDashboardController::class, 'index']);

    // Media Upload Route
    Route::post('/media/generate-upload-url', [MediaController::class, 'generateUploadUrl']);

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
        ->middleware('role:Admin HRD')
        ->group(function () {
            Route::controller(SystemSettingController::class)->group(function () {
                Route::get('/settings', 'index');
                Route::post('/settings/bulk', 'bulkUpdate');
            });
            
            Route::controller(TaskController::class)->group(function () {
                Route::get('/tasks', 'index');
                Route::post('/tasks/queue/clear', 'clearQueue');
                Route::post('/tasks/queue/restart', 'restartQueue');
            });

            Route::get('passport-clients', [\App\Modules\System\Controllers\V1\Configuration\PassportDataController::class, 'clients']);
            Route::get('passport-roles', [\App\Modules\System\Controllers\V1\Configuration\PassportDataController::class, 'roles']);
            Route::get('global-passport-roles', [\App\Modules\System\Controllers\V1\Configuration\GlobalPassportRoleController::class, 'index']);
            Route::post('global-passport-roles', [\App\Modules\System\Controllers\V1\Configuration\GlobalPassportRoleController::class, 'store']);
        });

    Route::prefix('portal/management')
        ->controller(ManagementDashboardController::class)
        ->middleware('role:Admin HRD')
        ->group(function () {
            Route::get('/dashboard', 'index');
        });
});

