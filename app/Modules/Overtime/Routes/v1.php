<?php

use App\Modules\Overtime\Controllers\OvertimeController;
use Illuminate\Support\Facades\Route;

Route::middleware([\App\Http\Middleware\SyncUserByEmail::class])->group(function () {

    Route::get('/', [OvertimeController::class, 'index']);
    Route::post('/', [OvertimeController::class, 'store']);
    Route::get('/{id}', [OvertimeController::class, 'show']);
    Route::post('/{id}/settle', [OvertimeController::class, 'settle']);

});
