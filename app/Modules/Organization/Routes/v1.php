<?php

use App\Modules\Organization\Controllers\V1\DepartmentController;
use App\Modules\Organization\Controllers\V1\TeamController;
use App\Modules\Organization\Controllers\V1\WorkPositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('work-positions')->group(function () {
    Route::get('/', [WorkPositionController::class, 'index']);
    Route::post('/', [WorkPositionController::class, 'store']);
    Route::get('/{id}', [WorkPositionController::class, 'show']);
    Route::put('/{workPosition}', [WorkPositionController::class, 'update']);
    Route::delete('/{workPosition}', [WorkPositionController::class, 'destroy']);
});

Route::prefix('departments')->group(function () {
    Route::get('/', [DepartmentController::class, 'index']);
    Route::post('/', [DepartmentController::class, 'store']);
    Route::get('/{id}', [DepartmentController::class, 'show']);
    Route::put('/{department}', [DepartmentController::class, 'update']);
    Route::delete('/{department}', [DepartmentController::class, 'destroy']);
});

Route::prefix('teams')->group(function () {
    Route::get('/', [TeamController::class, 'index']);
    Route::post('/', [TeamController::class, 'store']);
    Route::get('/{id}', [TeamController::class, 'show']);
    Route::put('/{team}', [TeamController::class, 'update']);
    Route::delete('/{team}', [TeamController::class, 'destroy']);
});
