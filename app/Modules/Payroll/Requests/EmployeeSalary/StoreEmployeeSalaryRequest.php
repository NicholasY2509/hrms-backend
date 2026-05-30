<?php

namespace App\Modules\Payroll\Requests\EmployeeSalary;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id',
            'bpjs_base_amount' => 'required|numeric|min:0',
            'actual_base_amount' => 'required|numeric|min:0',
            'effective_date' => 'required|date',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
