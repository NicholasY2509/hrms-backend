<?php

namespace App\Modules\Payroll\Requests\EmployeeSalary;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bpjs_base_amount' => 'sometimes|numeric|min:0',
            'actual_base_amount' => 'sometimes|numeric|min:0',
            'effective_date' => 'sometimes|date',
            'is_active' => 'sometimes|boolean',
        ];
    }
}
