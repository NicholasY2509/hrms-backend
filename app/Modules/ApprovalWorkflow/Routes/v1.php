<?php

use App\Modules\ApprovalWorkflow\Controllers\V1\ApprovalGroupController;
use App\Modules\ApprovalWorkflow\Controllers\V1\ApprovalSchemeController;
use App\Modules\ApprovalWorkflow\Controllers\V1\ApprovalRuleController;
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

    // Approval Schemes (Top-level categories like Unpaid Leave, Overtime)
    Route::prefix('schemes')->group(function () {
        Route::get('/', [ApprovalSchemeController::class, 'index']);
        Route::post('/', [ApprovalSchemeController::class, 'store']);
        Route::get('/{id}', [ApprovalSchemeController::class, 'show']);
        Route::patch('/{id}', [ApprovalSchemeController::class, 'update']);
        Route::delete('/{id}', [ApprovalSchemeController::class, 'destroy']);
    });

    // Approval Rules (The actual flows: Default or Position-specific)
    Route::prefix('rules')->group(function () {
        Route::post('/', [ApprovalRuleController::class, 'store']);
        Route::patch('/{id}', [ApprovalRuleController::class, 'update']);
        Route::delete('/{id}', [ApprovalRuleController::class, 'destroy']);
    });

    // Master Data for Step Types
    Route::prefix('step-types')->group(function () {
        Route::get('/', [ApprovalStepTypeController::class, 'index']);
        Route::post('/', [ApprovalStepTypeController::class, 'store']);
        Route::patch('/{id}', [ApprovalStepTypeController::class, 'update']);
        Route::delete('/{id}', [ApprovalStepTypeController::class, 'destroy']);
    });

});
