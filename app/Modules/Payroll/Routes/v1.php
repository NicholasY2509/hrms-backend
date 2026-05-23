<?php

use App\Modules\Payroll\Controllers\V1\Configuration\SalaryComponentController;
use App\Modules\Payroll\Controllers\V1\Configuration\TaxPtkpSettingController;
use App\Modules\Payroll\Controllers\V1\Portal\Management\EmployeeSalaryController;
use App\Modules\Payroll\Controllers\V1\Portal\Management\EmployeeSalaryComponentController;
use App\Modules\Payroll\Controllers\V1\Portal\Employee\MySalaryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth'])->group(function () {
    Route::prefix('configuration')
        ->middleware('role:Admin HRD')
        ->group(function () {
            Route::get('tax-ptkp-settings', [TaxPtkpSettingController::class, 'index']);
            Route::apiResource('salary-components', SalaryComponentController::class);
        });

    Route::prefix('portal/employee')->group(function () {
        Route::get('my-salary', [MySalaryController::class, 'index']);
    });

    Route::get('salary-details', [MySalaryController::class, 'indexOld']);


    Route::prefix('portal/management')
        ->middleware('role:Admin HRD')
        ->group(function () {
            Route::get('employee-salaries', [EmployeeSalaryController::class, 'index']);
            Route::post('employee-salaries', [EmployeeSalaryController::class, 'store']);

            Route::apiResource('employee-salary-components', EmployeeSalaryComponentController::class);
        });
});
