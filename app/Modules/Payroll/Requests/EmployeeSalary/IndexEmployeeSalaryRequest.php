<?php

namespace App\Modules\Payroll\Requests\EmployeeSalary;

use Illuminate\Foundation\Http\FormRequest;

class IndexEmployeeSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'nullable|integer|min:1|max:100',
            'search' => 'nullable|string|max:255',
        ];
    }

    public function queryParameters(): array
    {
        return [
            'per_page' => [
                'description' => 'Number of items per page.',
                'example' => 15,
            ],
            'search' => [
                'description' => 'Search by employee name or ID.',
                'example' => 'John',
            ]
        ];
    }
}
