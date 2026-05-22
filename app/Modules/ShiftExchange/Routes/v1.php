<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ShiftExchange\Controllers\V1\Portal\Employee\MyShiftExchangeController;
use App\Modules\ShiftExchange\Controllers\V1\Portal\Management\ShiftExchangeManagementController;

Route::prefix('v1/portal')->middleware(['api.auth'])->group(function () {
    // Employee Routes
    Route::prefix('employee')->group(function () {
        Route::prefix('shift-exchanges')->group(function () {
            Route::get('/', [MyShiftExchangeController::class, 'index']);
            Route::post('/', [MyShiftExchangeController::class, 'store']);
            Route::get('/working-hour', [MyShiftExchangeController::class, 'getEmployeeWorkingHour']);
            Route::get('/{id}', [MyShiftExchangeController::class, 'show']);
        });
    });

    // Management Routes
    Route::prefix('management')->group(function () {
        Route::prefix('shift-exchanges')->group(function () {
            Route::get('/', [ShiftExchangeManagementController::class, 'index']);
            Route::get('/{id}', [ShiftExchangeManagementController::class, 'show']);
            Route::post('/{id}/settle', [ShiftExchangeManagementController::class, 'settle']);
        });
    });
});
