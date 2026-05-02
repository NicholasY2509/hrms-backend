<?php

use App\Modules\Career\Controllers\V1\Portal\Management\CareerManagementController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal/management')->group(function () {
    Route::apiResource('careers', CareerManagementController::class);
});
