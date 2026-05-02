<?php

use App\Http\Middleware\SyncUserByEmail;
use App\Modules\System\Controllers\V1\AuthController;
use App\Modules\System\Controllers\V1\DashboardController;
use App\Modules\System\Controllers\V1\SystemController;
use Illuminate\Support\Facades\Route;

// Standard System Routes
Route::get('/test-passport', [SystemController::class, 'testPassport']);

// Auth Routes
Route::middleware(['api.auth'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
