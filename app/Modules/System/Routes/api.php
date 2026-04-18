<?php

use App\Modules\System\Controllers\SystemController;
use Illuminate\Support\Facades\Route;

Route::prefix('system')->group(function () {
    Route::get('/test-passport', [SystemController::class, 'testPassport']);
});
