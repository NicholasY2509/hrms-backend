<?php

use App\Modules\User\Controllers\V1\Portal\Management\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::prefix('portal/management')->group(function () {
        Route::apiResource('users', UserManagementController::class);
    });
});
