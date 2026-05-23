<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam employee_id integer Filter by employee ID. Example: 1
 * @queryParam department_id integer Filter by department ID. Example: 1
 * @queryParam work_location_id integer Filter by work location ID. Example: 1
 * @queryParam start_date string Filter by start date (YYYY-MM-DD). Example: 2024-05-01
 * @queryParam end_date string Filter by end date (YYYY-MM-DD). Example: 2024-05-31
 * @queryParam attendance_status_id integer Filter by attendance status ID. Example: 1
 * @queryParam search string Search by employee name or NIK. Example: John
 * @queryParam per_page integer Number of items per page. Default: 15. Example: 20
 */
class AttendanceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (empty($this->start_date) && empty($this->end_date)) {
            $today = \Carbon\Carbon::today()->format('Y-m-d');
            $this->merge([
                'start_date' => $today,
                'end_date' => $today,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'work_location_id' => ['nullable', 'integer', 'exists:work_locations,id'],
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'attendance_status_id' => ['nullable', 'integer', 'exists:attendance_statuses,id'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
