<?php

use App\Modules\Leave\Controllers\V1\Portal\Management\AnnualLeaveManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::prefix('portal/management')->group(function () {
        Route::get('annual-leaves', [AnnualLeaveManagementController::class, 'index']);
    });
});
