<?php

use App\Http\Middleware\SyncUserByEmail;
use App\Modules\Attendance\Controllers\V1\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware([SyncUserByEmail::class])->group(function () {
    Route::get('/', [AttendanceController::class, 'index']);
    Route::get('/status', [AttendanceController::class, 'status']);
    Route::get('/working-hour', [AttendanceController::class, 'workingHour']);
    Route::post('/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/check-location', [AttendanceController::class, 'checkLocation']);
});
