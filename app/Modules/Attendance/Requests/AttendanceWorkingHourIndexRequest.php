<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam employee_id int Filter by employee ID. Example: 1
 * @queryParam start_date date Filter by start date (YYYY-MM-DD). Example: 2024-01-01
 * @queryParam end_date date Filter by end date (YYYY-MM-DD). Example: 2024-01-31
 * @queryParam per_page int Results per page. Default: 15. Example: 15
 */
class AttendanceWorkingHourIndexRequest extends FormRequest
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
            'employee_id' => 'nullable|integer|exists:employees,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
