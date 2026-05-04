<?php

use App\Modules\Disciplinary\Controllers\V1\Portal\Management\WarningLetterManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::prefix('portal/management')->group(function () {
        Route::apiResource('warning-letters', WarningLetterManagementController::class);
    });

    Route::prefix('configuration')->group(function () {
        Route::apiResource('warning-letter-types', \App\Modules\Disciplinary\Controllers\V1\Configuration\WarningLetterTypeController::class);
    });
});
