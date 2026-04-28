<?php

use App\Modules\UnpaidLeave\Controllers\V1\Portal\Employee\MyUnpaidLeaveController;
use App\Modules\UnpaidLeave\Controllers\V1\Configuration\UnpaidLeaveTypeController;
use App\Modules\UnpaidLeave\Controllers\V1\Portal\Management\UnpaidLeaveManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\SyncUserByEmail::class])->group(function () {

    // PORTAL (Employee Context)
    Route::prefix('portal')->group(function () {
        Route::prefix('employee')->group(function () {
            Route::prefix('leaves')->group(function () {
                Route::get('/', [MyUnpaidLeaveController::class, 'index']);
                Route::post('/', [MyUnpaidLeaveController::class, 'store']);
                Route::get('/{id}', [MyUnpaidLeaveController::class, 'show']);
            });
        });

        Route::prefix('management')->group(function () {
            Route::prefix('leaves')->group(function () {
                Route::get('/', [UnpaidLeaveManagementController::class, 'index']);
                Route::get('/{id}', [UnpaidLeaveManagementController::class, 'show']);
            });
        });
    });

    // CONFIGURATION (Admin Context)
    Route::prefix('config')->group(function () {
        Route::prefix('types')->group(function () {
            Route::get('/', [UnpaidLeaveTypeController::class, 'index']);
        });
    });

});
