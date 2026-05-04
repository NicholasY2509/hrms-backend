<?php

use App\Modules\Career\Controllers\V1\Configuration\CareerTypeController;
use App\Modules\Career\Controllers\V1\Portal\Management\CareerManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::prefix('portal/management')->group(function () {
        Route::apiResource('careers', CareerManagementController::class);
    });

    Route::prefix('configuration')->group(function () {
        Route::apiResource('career-types', CareerTypeController::class);
    });
});
