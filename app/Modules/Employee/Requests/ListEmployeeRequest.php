<?php

namespace App\Modules\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam search string Filter by name, employee ID number, or full name. Example: NICHOLAS YANG
 * @queryParam work_position_id integer Filter by work position ID. Example: 1
 * @queryParam team_id integer Filter by team ID. Example: 1
 * @queryParam department_id integer Filter by department ID. Example: 1
 * @queryParam work_location_id integer Filter by work location ID. Example: 1
 * @queryParam work_employee_status_id integer Filter by work employee status ID. Default: 1. Example: 1
 * @queryParam per_page integer Number of items per page. Default: 15. Example: 20
 */
class ListEmployeeRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'work_position_id' => ['nullable', 'integer', 'exists:work_positions,id'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'work_location_id' => ['nullable', 'integer', 'exists:work_locations,id'],
            'work_employee_status_id' => ['nullable', 'integer', 'exists:work_employee_statuses,id'],
            'supervisor_employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
