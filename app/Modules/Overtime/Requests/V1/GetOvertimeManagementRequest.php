<?php

namespace App\Modules\Overtime\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class GetOvertimeManagementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'type' => 'nullable|string|in:UMUM,DAC,NATIONAL',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'is_settled' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     * Get the query parameters for documentation.
     */
    public function queryParameters(): array
    {
        return [
            'employee_id' => [
                'description' => 'Filter by employee ID.',
                'example' => 1,
            ],
            'department_id' => [
                'description' => 'Filter by department ID.',
                'example' => 1,
            ],
            'type' => [
                'description' => 'Filter by overtime type (UMUM, DAC, NATIONAL).',
                'example' => 'UMUM',
            ],
            'start_date' => [
                'description' => 'Filter by start date (YYYY-MM-DD).',
                'example' => '2024-01-01',
            ],
            'end_date' => [
                'description' => 'Filter by end date (YYYY-MM-DD).',
                'example' => '2024-12-31',
            ],
            'is_settled' => [
                'description' => 'Filter by settlement status.',
                'example' => 1,
            ],
            'per_page' => [
                'description' => 'Results per page.',
                'example' => 15,
            ],
        ];
    }
}
