<?php

use App\Modules\ApprovalWorkflow\Controllers\V1\Configuration\ApprovalGroupController;
use App\Modules\ApprovalWorkflow\Controllers\V1\Configuration\ApprovalSchemeController;
use App\Modules\ApprovalWorkflow\Controllers\V1\Configuration\ApprovalRuleController;
use App\Modules\ApprovalWorkflow\Controllers\V1\Configuration\ApprovalStepTypeController;
use App\Modules\ApprovalWorkflow\Controllers\V1\Portal\Management\ApprovalActionController;
use App\Modules\ApprovalWorkflow\Controllers\V1\Portal\Management\EmployeeSearchController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\SyncUserByEmail::class])->group(function () {

    /**
     * --------------------------------------------------------------------------
     * CONFIGURATION (IT / Admin Only)
     * --------------------------------------------------------------------------
     */
    Route::prefix('config')->group(function () {
        // Approval Groups Configuration
        Route::prefix('groups')->group(function () {
            Route::get('/', [ApprovalGroupController::class, 'index']);
            Route::post('/', [ApprovalGroupController::class, 'store']);
            Route::post('/{id}/sync-employees', [ApprovalGroupController::class, 'syncEmployees']);
            Route::delete('/{id}', [ApprovalGroupController::class, 'destroy']);
        });

        // Approval Schemes
        Route::prefix('schemes')->group(function () {
            Route::get('/', [ApprovalSchemeController::class, 'index']);
            Route::post('/', [ApprovalSchemeController::class, 'store']);
            Route::get('/{id}', [ApprovalSchemeController::class, 'show']);
            Route::patch('/{id}', [ApprovalSchemeController::class, 'update']);
            Route::delete('/{id}', [ApprovalSchemeController::class, 'destroy']);
        });

        // Approval Rules
        Route::prefix('rules')->group(function () {
            Route::post('/', [ApprovalRuleController::class, 'store']);
            Route::patch('/{id}', [ApprovalRuleController::class, 'update']);
            Route::delete('/{id}', [ApprovalRuleController::class, 'destroy']);
        });

        // Step Types
        Route::prefix('step-types')->group(function () {
            Route::get('/', [ApprovalStepTypeController::class, 'index']);
            Route::post('/', [ApprovalStepTypeController::class, 'store']);
            Route::patch('/{id}', [ApprovalStepTypeController::class, 'update']);
            Route::delete('/{id}', [ApprovalStepTypeController::class, 'destroy']);
        });
    });

    /**
     * --------------------------------------------------------------------------
     * PORTAL (Employees / Managers / HR Usage)
     * --------------------------------------------------------------------------
     */
    Route::prefix('portal')->group(function () {
        
        // Management Context (HR/Managers)
        Route::prefix('management')->group(function () {
            Route::prefix('actions')->group(function () {
                Route::get('/', [ApprovalActionController::class, 'index']);
                Route::post('{id}/approve', [ApprovalActionController::class, 'approve']);
                Route::post('{id}/reject', [ApprovalActionController::class, 'reject']);
            });

            Route::get('employee-search', [EmployeeSearchController::class, 'search']);
        });

    });

});
