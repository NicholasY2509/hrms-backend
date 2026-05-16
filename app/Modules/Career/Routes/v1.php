<?php

use App\Modules\Career\Controllers\V1\Configuration\CareerTypeController;
use App\Modules\Career\Controllers\V1\Portal\Management\CareerManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::prefix('portal/management')->middleware('role:Admin HRD')->group(function () {
        Route::post('careers/{career}/settle', [CareerManagementController::class, 'settle']);
        Route::get('careers/{career}/export', [CareerManagementController::class, 'export']);
        Route::apiResource('careers', CareerManagementController::class);
    });

    Route::prefix('configuration')->middleware('role:Admin HRD')->group(function () {
        Route::apiResource('career-types', CareerTypeController::class);
    });
});
