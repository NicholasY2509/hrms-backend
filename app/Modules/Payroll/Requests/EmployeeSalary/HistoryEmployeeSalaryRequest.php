<?php

namespace App\Modules\Payroll\Requests\EmployeeSalary;

use Illuminate\Foundation\Http\FormRequest;

class HistoryEmployeeSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
        ];
    }

    public function queryParameters(): array
    {
        return [
            'employee_id' => [
                'description' => 'The ID of the employee.',
                'example' => 1,
            ],
        ];
    }
}
