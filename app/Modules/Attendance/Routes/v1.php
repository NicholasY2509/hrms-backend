<?php

use App\Modules\Attendance\Controllers\V1\Configuration\AttendanceSettingController;
use App\Modules\Attendance\Controllers\V1\Configuration\AttendanceStatusController;
use App\Modules\Attendance\Controllers\V1\Portal\Employee\MyAttendanceController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\AttendanceManagementController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\AttendanceWorkingHourManagementController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\MobileScanManagementController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\WorkingHourManagementController;
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
            Route::get('/attendances', [AttendanceManagementController::class, 'index']);
            Route::get('/mobile-scans', [MobileScanManagementController::class, 'index']);
            Route::get('/attendance-working-hours', [AttendanceWorkingHourManagementController::class, 'index']);
            
            Route::apiResource('working-hours', WorkingHourManagementController::class);
            Route::apiResource('attendance-locations', \App\Modules\Attendance\Controllers\V1\Portal\Management\AttendanceLocationManagementController::class);
            Route::apiResource('attendance-users', \App\Modules\Attendance\Controllers\V1\Portal\Management\AttendanceUserManagementController::class);
            Route::apiResource('zkteco-machines', \App\Modules\Attendance\Controllers\V1\Portal\Management\ZktecoMachineManagementController::class);
            Route::get('/zkteco-attendances', [\App\Modules\Attendance\Controllers\V1\Portal\Management\ZktecoAttendanceManagementController::class, 'index']);
            Route::get('/zkteco-users', [\App\Modules\Attendance\Controllers\V1\Portal\Management\ZktecoUserManagementController::class, 'index']);
        });

        Route::prefix('configuration')->group(function () {
            Route::get('/settings', [AttendanceSettingController::class, 'index']);
            Route::put('/settings', [AttendanceSettingController::class, 'update']);
            Route::get('/statuses', [AttendanceStatusController::class, 'index']);
        });
        
    });
});
