<?php

use App\Modules\Organization\Controllers\V1\WorkPositionController;
use Illuminate\Support\Facades\Route;

Route::prefix('work-positions')->group(function () {
    Route::get('/', [WorkPositionController::class, 'index']);
    Route::post('/', [WorkPositionController::class, 'store']);
    Route::get('/{id}', [WorkPositionController::class, 'show']);
    Route::put('/{workPosition}', [WorkPositionController::class, 'update']);
    Route::delete('/{workPosition}', [WorkPositionController::class, 'destroy']);
});
