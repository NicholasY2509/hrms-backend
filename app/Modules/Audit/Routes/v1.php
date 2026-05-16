<?php

use App\Modules\Audit\Controllers\V1\Configuration\AuditLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::prefix('configuration')->middleware('role:Admin HRD')->group(function () {
        Route::get('/logs', [AuditLogController::class, 'index']);
        Route::get('/logs/{id}', [AuditLogController::class, 'show']);
    });
});
