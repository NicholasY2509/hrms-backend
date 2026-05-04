<?php

use App\Modules\Payroll\Controllers\V1\PayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::get('/salary-details', [PayrollController::class, 'salaryDetails']);
});
