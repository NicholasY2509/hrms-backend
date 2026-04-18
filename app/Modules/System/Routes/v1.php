<?php

use App\Modules\System\Controllers\V1\SystemController;
use Illuminate\Support\Facades\Route;

Route::get('/test-passport', [SystemController::class, 'testPassport']);
