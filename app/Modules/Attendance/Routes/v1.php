<?php

use App\Modules\Attendance\Controllers\V1\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\SyncUserByEmail::class])->group(function () {
    Route::get('/', [AttendanceController::class, 'index']);
    Route::get('/status', [AttendanceController::class, 'status']);
    Route::post('/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/clock-out', [AttendanceController::class, 'clockOut']);
});
