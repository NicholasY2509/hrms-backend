<?php

use App\Modules\System\Controllers\V1\AuthController;
use App\Modules\System\Controllers\V1\DashboardController;
use App\Modules\System\Controllers\V1\Portal\Employee\NotificationController;
use App\Modules\System\Controllers\V1\SystemController;
use Illuminate\Support\Facades\Route;

// Standard System Routes
Route::get('/test-passport', [SystemController::class, 'testPassport']);

// Auth Routes
Route::middleware(['api.auth'])->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Notification Routes
    Route::prefix('portal/employee/notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });

    Route::prefix('portal/management')
        ->middleware('role:admin,Admin HRD')
        ->group(function () {
            Route::get('/dashboard', [\App\Modules\System\Controllers\V1\Portal\Management\ManagementDashboardController::class, 'index']);
        });
});
