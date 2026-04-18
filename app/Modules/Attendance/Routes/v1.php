<?php

use App\Modules\Attendance\Controllers\V1\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\SyncUserByEmail::class])->group(function () {
    Route::get('/status', [AttendanceController::class, 'status']);
});
