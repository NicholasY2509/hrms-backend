<?php

use App\Modules\Employee\Controllers\V1\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\SyncUserByEmail::class])->group(function () {
    Route::get('/profile', [EmployeeController::class, 'profile']);
});
