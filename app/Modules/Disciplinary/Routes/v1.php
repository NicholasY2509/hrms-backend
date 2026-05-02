<?php

use App\Modules\Disciplinary\Controllers\V1\Portal\Management\WarningLetterManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal/management')->group(function () {
    Route::apiResource('warning-letters', WarningLetterManagementController::class);
});
