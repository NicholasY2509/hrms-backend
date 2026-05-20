<?php

use App\Modules\User\Controllers\V1\Portal\Management\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::prefix('portal/management')->middleware('role:Admin HRD')->group(function () {
        Route::apiResource('users', UserManagementController::class);
    });
});
