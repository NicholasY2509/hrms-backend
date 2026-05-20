<?php

use App\Modules\Attendance\Controllers\V1\Configuration\AttendanceCalculationSettingController;
use App\Modules\Attendance\Controllers\V1\Configuration\AttendanceSettingController;
use App\Modules\Attendance\Controllers\V1\Configuration\AttendanceStatusController;
use App\Modules\Attendance\Controllers\V1\Portal\Employee\MyAttendanceController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\AttendanceExportController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\AttendanceLocationManagementController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\AttendanceManagementController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\AttendanceUserManagementController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\AttendanceWorkingHourManagementController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\MobileScanManagementController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\ZktecoAttendanceManagementController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\ZktecoMachineManagementController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\ZktecoUserManagementController;
use App\Modules\Attendance\Controllers\V1\Portal\Management\WorkingHourManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {

    Route::prefix('portal')->group(function () {
        
        Route::prefix('employee')->group(function () {

            Route::controller(MyAttendanceController::class)->group(function () {
                Route::get('/', 'index');
                Route::get('/status', 'status');
                Route::get('/working-hour', 'workingHour');
                Route::get('/working-hours', 'myWorkingHours');
                Route::post('/clock-in', 'clockIn');
                Route::post('/clock-out', 'clockOut');
                Route::post('/check-location', 'checkLocation');
            });

        });

        Route::prefix('management')->middleware('role:Admin HRD')->group(function () {

            Route::controller(AttendanceManagementController::class)->group(function () {
                Route::get('/attendances', 'index');
                Route::post('/attendances/calculate', 'calculate');
            });

            Route::controller(AttendanceExportController::class)->group(function () {
                Route::post('/attendances/export', 'export'); 
            });

            Route::controller(MobileScanManagementController::class)->group(function () {
                Route::get('/mobile-scans', 'index');
            });

            Route::controller(AttendanceWorkingHourManagementController::class)->group(function () {
                Route::get('/attendance-working-hours', 'index');
                Route::post('/attendance-working-hours/import', 'import');
                Route::put('/attendance-working-hours/{attendanceWorkingHour}', 'update');
            });

            Route::apiResource('working-hours', WorkingHourManagementController::class);

            Route::apiResource('attendance-locations', AttendanceLocationManagementController::class);

            Route::apiResource('attendance-users', AttendanceUserManagementController::class);

            Route::apiResource('zkteco-machines', ZktecoMachineManagementController::class);

            Route::controller(ZktecoAttendanceManagementController::class)->group(function () {
                Route::get('/zkteco-attendances', 'index');
                Route::post('/zkteco-attendances/sync', 'sync');
            });

            Route::controller(ZktecoUserManagementController::class)->group(function () {
                Route::get('/zkteco-users', 'index');
                Route::post('/zkteco-users/sync', 'sync');
            });
        });
        
        Route::prefix('configuration')->middleware('role:Admin HRD')->group(function () {
            
            Route::controller(AttendanceSettingController::class)->group(function () {
                Route::get('/settings', 'index');
                Route::put('/settings', 'update');
            });

            Route::controller(AttendanceCalculationSettingController::class)->group(function () {
                Route::get('/calculation-settings', 'index');
                Route::put('/calculation-settings', 'update');
            });

            Route::controller(AttendanceStatusController::class)->group(function () {
                Route::get('/statuses', 'index');
            });
        });
        
    });
});
