<?php

use App\Modules\Leave\Controllers\V1\Portal\Management\AnnualLeaveManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::prefix('portal/management')->middleware('role:Admin HRD')->group(function () {
        Route::get('annual-leaves/summary/{employee}', [AnnualLeaveManagementController::class, 'employeeSummary']);
        Route::get('annual-leaves/summary', [AnnualLeaveManagementController::class, 'summary']);
        Route::get('annual-leaves', [AnnualLeaveManagementController::class, 'index']);
        Route::post('annual-leaves', [AnnualLeaveManagementController::class, 'store']);
        Route::put('annual-leaves/adjust/{employee}', [AnnualLeaveManagementController::class, 'adjust']);
    });
});
