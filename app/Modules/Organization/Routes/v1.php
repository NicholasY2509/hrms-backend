<?php

use App\Modules\Organization\Controllers\V1\Portal\Management\DepartmentController;
use App\Modules\Organization\Controllers\V1\Portal\Management\TeamController;
use App\Modules\Organization\Controllers\V1\Portal\Management\WorkLocationController;
use App\Modules\Organization\Controllers\V1\Portal\Management\WorkPositionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::prefix('portal/management')
        ->middleware('role:Admin HRD')
        ->group(function () {
        
        Route::apiResource('work-locations', WorkLocationController::class);
        Route::apiResource('work-positions', WorkPositionController::class);
        Route::apiResource('departments', DepartmentController::class);
        Route::apiResource('teams', TeamController::class);

    });
});
