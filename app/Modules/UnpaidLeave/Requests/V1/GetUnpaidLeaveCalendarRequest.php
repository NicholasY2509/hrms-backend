<?php

namespace App\Modules\UnpaidLeave\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class GetUnpaidLeaveCalendarRequest extends FormRequest
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
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'employee_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'unpaid_leave_type_id' => 'nullable|integer',
            'status' => 'nullable|string|in:pending,approved,rejected,cancelled',
        ];
    }

    /**
     * Get the query parameters for documentation.
     */
    public function queryParameters(): array
    {
        return [
            'start_date' => [
                'description' => 'The start date of the calendar range (YYYY-MM-DD).',
                'example' => '2024-05-01',
            ],
            'end_date' => [
                'description' => 'The end date of the calendar range (YYYY-MM-DD).',
                'example' => '2024-05-31',
            ],
            'employee_id' => [
                'description' => 'Filter by specific employee.',
                'example' => 1,
            ],
            'department_id' => [
                'description' => 'Filter by department.',
                'example' => 5,
            ],
            'unpaid_leave_type_id' => [
                'description' => 'Filter by unpaid leave type.',
                'example' => 1,
            ],
            'status' => [
                'description' => 'Filter by request status.',
                'example' => 'approved',
            ],
        ];
    }
}
