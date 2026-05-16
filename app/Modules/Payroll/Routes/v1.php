<?php

use App\Modules\Payroll\Controllers\V1\Configuration\SalaryComponentController;
use App\Modules\Payroll\Controllers\V1\Configuration\TaxPtkpSettingController;
use App\Modules\Payroll\Controllers\V1\Portal\Management\EmployeeSalaryController;
use App\Modules\Payroll\Controllers\V1\Portal\Management\EmployeeSalaryComponentController;
use Illuminate\Support\Facades\Route;

Route::prefix('configuration')->group(function () {
    Route::get('tax-ptkp-settings', [TaxPtkpSettingController::class, 'index']);
    Route::apiResource('salary-components', SalaryComponentController::class);
});

Route::prefix('portal/management')->group(function () {
    Route::get('employee-salaries', [EmployeeSalaryController::class, 'index']);
    Route::post('employee-salaries', [EmployeeSalaryController::class, 'store']);

    Route::apiResource('employee-salary-components', EmployeeSalaryComponentController::class);
});
