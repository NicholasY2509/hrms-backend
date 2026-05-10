<?php

use App\Modules\ApprovalWorkflow\Controllers\V1\Configuration\ApprovalGroupController;
use App\Modules\ApprovalWorkflow\Controllers\V1\Configuration\ApprovalSchemeController;
use App\Modules\ApprovalWorkflow\Controllers\V1\Configuration\ApprovalRuleController;
use App\Modules\ApprovalWorkflow\Controllers\V1\Configuration\ApprovalStepTypeController;
use App\Modules\ApprovalWorkflow\Controllers\V1\Portal\Management\ApprovalActionController;
use App\Modules\ApprovalWorkflow\Controllers\V1\Portal\Management\EmployeeSearchController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.auth')->group(function () {

    /**
     * --------------------------------------------------------------------------
     * CONFIGURATION (IT / Admin Only)
     * --------------------------------------------------------------------------
     */
    Route::prefix('config')->group(function () {
        // Approval Groups Configuration
        Route::prefix('groups')->controller(ApprovalGroupController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::post('/{id}/sync-employees', 'syncEmployees');
            Route::delete('/{id}', 'destroy');
        });

        // Approval Schemes
        Route::prefix('schemes')->controller(ApprovalSchemeController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{id}', 'show');
            Route::patch('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        // Approval Rules
        Route::prefix('rules')->controller(ApprovalRuleController::class)->group(function () {
            Route::post('/', 'store');
            Route::patch('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });

        // Step Types
        Route::prefix('step-types')->controller(ApprovalStepTypeController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::patch('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
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
            Route::prefix('actions')->controller(ApprovalActionController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('{id}/approve', 'approve');
                Route::post('{id}/reject', 'reject');
            });

            Route::get('employee-search', [EmployeeSearchController::class, 'search']);
        });

    });

});
