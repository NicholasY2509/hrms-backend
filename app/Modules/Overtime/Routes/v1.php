<?php

use App\Modules\Overtime\Controllers\V1\Portal\Employee\MyOvertimeController;
use App\Modules\Overtime\Controllers\V1\Portal\Management\OvertimeManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\SyncUserByEmail::class])->group(function () {

    Route::prefix('portal')->group(function () {
        
        Route::prefix('employee')->group(function () {
            Route::get('/', [MyOvertimeController::class, 'index']);
            Route::post('/', [MyOvertimeController::class, 'store']);
            Route::get('/{id}', [MyOvertimeController::class, 'show']);
            Route::post('/{id}/settle', [MyOvertimeController::class, 'settle']);
        });

        Route::prefix('management')->group(function () {
            Route::get('/', [OvertimeManagementController::class, 'index']);
            Route::get('/{id}', [OvertimeManagementController::class, 'show']);
        });

    });

});
