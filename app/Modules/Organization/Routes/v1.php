<?php

use App\Modules\Organization\Controllers\V1\Portal\Management\DepartmentController;
use App\Modules\Organization\Controllers\V1\Portal\Management\TeamController;
use App\Modules\Organization\Controllers\V1\Portal\Management\WorkLocationController;
use App\Modules\Organization\Controllers\V1\Portal\Management\WorkPositionController;
use App\Modules\Organization\Controllers\V1\Configuration\PositionHierarchyMatrixController;
use App\Modules\Organization\Controllers\V1\Configuration\OrganizationChartController;
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

    Route::prefix('config')
        ->middleware('role:Admin HRD')
        ->group(function () {
            
        Route::apiResource('position-hierarchy-matrices', PositionHierarchyMatrixController::class);
        Route::get('work-positions/{id}/roles', [\App\Modules\Organization\Controllers\V1\Configuration\WorkPositionRoleController::class, 'index']);
        Route::post('work-positions/{id}/roles', [\App\Modules\Organization\Controllers\V1\Configuration\WorkPositionRoleController::class, 'store']);
        Route::get('org-chart', [OrganizationChartController::class, 'index']);
        Route::get('org-chart/{positionId}/employees', [OrganizationChartController::class, 'employees']);

    });
});
