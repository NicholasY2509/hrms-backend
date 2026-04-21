<?php

use App\Http\Middleware\SyncUserByEmail;
use App\Modules\Payroll\Controllers\V1\PayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware([SyncUserByEmail::class])->group(function () {
    Route::get('/salary-details', [PayrollController::class, 'salaryDetails']);
});
