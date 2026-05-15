<?php

use App\Modules\Overtime\Controllers\V1\Portal\Employee\MyOvertimeController;
use App\Modules\Overtime\Controllers\V1\Portal\Management\OvertimeManagementController;
use App\Modules\Overtime\Controllers\V1\Portal\Management\OvertimeTypeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {

    Route::prefix('portal')->group(function () {
        
        Route::prefix('employee')->group(function () {
            Route::get('/', [MyOvertimeController::class, 'index']);
            Route::post('/', [MyOvertimeController::class, 'store']);
            Route::get('/{id}', [MyOvertimeController::class, 'show']);
            Route::post('/{id}/settle', [MyOvertimeController::class, 'settle']);
        });

        Route::prefix('management')->group(function() {
            
            Route::prefix('types')->group(function () {
                Route::get('/', [OvertimeTypeController::class, 'index']);
                Route::post('/', [OvertimeTypeController::class, 'store']);
                Route::get('/{id}', [OvertimeTypeController::class, 'show']);
                Route::put('/{id}', [OvertimeTypeController::class, 'update']);
                Route::delete('/{id}', [OvertimeTypeController::class, 'destroy']);
            });

            Route::get('/', [OvertimeManagementController::class, 'index']);
            Route::get('/export', [OvertimeManagementController::class, 'export']);
            Route::get('/{id}', [OvertimeManagementController::class, 'show']);
            Route::patch('/{id}', [OvertimeManagementController::class, 'update']);
            Route::post('/{id}/settle', [OvertimeManagementController::class, 'settle']);
        });
    });
    
});
