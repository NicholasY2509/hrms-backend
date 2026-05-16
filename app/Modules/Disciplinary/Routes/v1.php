<?php

use App\Modules\Disciplinary\Controllers\V1\Portal\Management\WarningLetterManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::prefix('portal/management')->middleware('role:Admin HRD')->group(function () {
        Route::post('warning-letters/{warning_letter}/settle', [WarningLetterManagementController::class, 'settle']);
        Route::get('warning-letters/{warning_letter}/export', [WarningLetterManagementController::class, 'export']);
        Route::apiResource('warning-letters', WarningLetterManagementController::class);
    });

    Route::prefix('configuration')->middleware('role:Admin HRD')->group(function () {
        Route::apiResource('warning-letter-types', \App\Modules\Disciplinary\Controllers\V1\Configuration\WarningLetterTypeController::class);
    });
});
