<?php

use App\Http\Middleware\SyncUserByEmail;
use App\Modules\Attendance\Controllers\V1\Portal\Employee\MyAttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware([SyncUserByEmail::class])->group(function () {

    Route::prefix('portal')->group(function () {
        
        Route::prefix('employee')->group(function () {
            Route::get('/', [MyAttendanceController::class, 'index']);
            Route::get('/status', [MyAttendanceController::class, 'status']);
            Route::get('/working-hour', [MyAttendanceController::class, 'workingHour']);
            Route::post('/clock-in', [MyAttendanceController::class, 'clockIn']);
            Route::post('/clock-out', [MyAttendanceController::class, 'clockOut']);
            Route::post('/check-location', [MyAttendanceController::class, 'checkLocation']);
        });

    });

});
