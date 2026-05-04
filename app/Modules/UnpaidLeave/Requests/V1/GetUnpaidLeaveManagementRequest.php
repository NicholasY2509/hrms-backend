<?php

namespace App\Modules\UnpaidLeave\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class GetUnpaidLeaveManagementRequest extends FormRequest
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
            'unpaid_leave_type_id' => 'nullable|integer',
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date' => 'nullable|date_format:Y-m-d',
            'settle_status' => 'nullable|string|in:settled,unsettled',
            'status' => 'nullable|string|in:pending,approved,rejected,cancelled',
            'department_ids' => 'nullable|string', // Comma-separated or handled as needed
            'search' => 'nullable|string',
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
            'unpaid_leave_type_id' => [
                'description' => 'Filter by unpaid leave type ID.',
                'example' => 1,
            ],
            'start_date' => [
                'description' => 'Filter by start date (YYYY-MM-DD).',
                'example' => '2024-01-01',
            ],
            'end_date' => [
                'description' => 'Filter by end date (YYYY-MM-DD).',
                'example' => '2024-12-31',
            ],
            'settle_status' => [
                'description' => 'Filter by settlement status.',
                'example' => 'unsettled',
            ],
            'status' => [
                'description' => 'Filter by request status.',
                'example' => 'pending',
            ],
            'department_ids' => [
                'description' => 'Comma-separated list of department IDs.',
                'example' => '1,2',
            ],
            'search' => [
                'description' => 'Search by employee name or ID number.',
                'example' => 'John',
            ],
            'per_page' => [
                'description' => 'Results per page.',
                'example' => 15,
            ],
        ];
    }
}
