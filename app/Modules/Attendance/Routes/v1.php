<?php

use App\Modules\Attendance\Controllers\V1\Configuration\AttendanceSettingController;
use App\Modules\Attendance\Controllers\V1\Portal\Employee\MyAttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {

    Route::prefix('portal')->group(function () {
        
        Route::prefix('employee')->group(function () {
            Route::get('/', [MyAttendanceController::class, 'index']);
            Route::get('/status', [MyAttendanceController::class, 'status']);
            Route::get('/working-hour', [MyAttendanceController::class, 'workingHour']);
            Route::post('/clock-in', [MyAttendanceController::class, 'clockIn']);
            Route::post('/clock-out', [MyAttendanceController::class, 'clockOut']);
            Route::post('/check-location', [MyAttendanceController::class, 'checkLocation']);
        });

        Route::prefix('management')->group(function () {
            Route::get('/attendances', [\App\Modules\Attendance\Controllers\V1\Portal\Management\AttendanceManagementController::class, 'index']);
        });

        Route::prefix('configuration')->group(function () {
            Route::get('/settings', [AttendanceSettingController::class, 'index']);
            Route::put('/settings', [AttendanceSettingController::class, 'update']);
            Route::get('/statuses', [\App\Modules\Attendance\Controllers\V1\Configuration\AttendanceStatusController::class, 'index']);
        });
        
    });
});
