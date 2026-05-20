<?php

use App\Modules\UnpaidLeave\Controllers\V1\Portal\Employee\MyUnpaidLeaveController;
use App\Modules\UnpaidLeave\Controllers\V1\Portal\Management\HolidayManagementController;
use App\Modules\UnpaidLeave\Controllers\V1\Portal\Management\UnpaidLeaveTypeController;
use App\Modules\UnpaidLeave\Controllers\V1\Portal\Management\UnpaidLeaveManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {

    // PORTAL (Employee Context)
    Route::prefix('portal')->group(function () {
        Route::prefix('employee')->group(function () {
            Route::prefix('leaves')->group(function () {
                Route::get('/', [MyUnpaidLeaveController::class, 'index']);
                Route::post('/', [MyUnpaidLeaveController::class, 'store']);
                Route::get('/{id}', [MyUnpaidLeaveController::class, 'show']);
            });
        });

        Route::prefix('management')->middleware('role:Admin HRD')->group(function () {
            Route::prefix('leaves')->group(function () {
                Route::get('/', [UnpaidLeaveManagementController::class, 'index']);
                Route::get('/calendar', [UnpaidLeaveManagementController::class, 'calendar']);
                Route::get('/{id}', [UnpaidLeaveManagementController::class, 'show']);
                Route::post('/{id}/settle', [UnpaidLeaveManagementController::class, 'settle']);
            });

            Route::prefix('types')->controller(UnpaidLeaveTypeController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{id}', 'show');
                Route::put('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
            });

            Route::prefix('holidays')->controller(HolidayManagementController::class)->group(function () {
                Route::get('/', 'index');
                Route::post('/', 'store');
                Route::get('/{id}', 'show');
                Route::put('/{id}', 'update');
                Route::delete('/{id}', 'destroy');
                Route::post('/auto-insert-sundays', 'autoInsertSundays');
            });
        });
    });


});
