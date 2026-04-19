<?php

use App\Modules\UnpaidLeave\Controllers\V1\UnpaidLeaveController;
use App\Modules\UnpaidLeave\Controllers\V1\UnpaidLeaveTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\SyncUserByEmail::class])->group(function () {

    // Unpaid Leaves Resource
    Route::prefix('leaves')->group(function () {
        Route::get('/', [UnpaidLeaveController::class, 'index']);
        Route::post('/', [UnpaidLeaveController::class, 'store']);
        Route::get('/{id}', [UnpaidLeaveController::class, 'show']);
    });

    // Unpaid Leave Types Resource
    Route::prefix('types')->group(function () {
        Route::get('/', [UnpaidLeaveTypeController::class, 'index']);
    });

});
