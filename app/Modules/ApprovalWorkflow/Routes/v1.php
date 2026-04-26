<?php

use App\Modules\ApprovalWorkflow\Controllers\V1\ApprovalGroupController;
use App\Modules\ApprovalWorkflow\Controllers\V1\ApprovalPolicyController;
use App\Modules\ApprovalWorkflow\Controllers\V1\ApprovalStepTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\SyncUserByEmail::class])->group(function () {

    // Approval Groups Configuration
    Route::prefix('groups')->group(function () {
        Route::get('/', [ApprovalGroupController::class, 'index']);
        Route::post('/', [ApprovalGroupController::class, 'store']);
        Route::get('/{id}', [ApprovalGroupController::class, 'show']);
        Route::post('/{id}/sync-employees', [ApprovalGroupController::class, 'syncEmployees']);
        Route::delete('/{id}', [ApprovalGroupController::class, 'destroy']);
    });

    // Approval Policies Configuration
    Route::prefix('policies')->group(function () {
        Route::get('/', [ApprovalPolicyController::class, 'index']);
        Route::post('/', [ApprovalPolicyController::class, 'store']);
        Route::get('/{id}', [ApprovalPolicyController::class, 'show']);
        Route::post('/{id}/steps', [ApprovalPolicyController::class, 'updateSteps']);
        Route::delete('/{id}', [ApprovalPolicyController::class, 'destroy']);
    });

    // Master Data for Step Types
    Route::prefix('step-types')->group(function () {
        Route::get('/', [ApprovalStepTypeController::class, 'index']);
        Route::post('/', [ApprovalStepTypeController::class, 'store']);
        Route::patch('/{id}', [ApprovalStepTypeController::class, 'update']);
        Route::delete('/{id}', [ApprovalStepTypeController::class, 'destroy']);
    });

});
