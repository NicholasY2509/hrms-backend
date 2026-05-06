<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam search string Filter by employee name or machine UID. Example: Budi
 * @queryParam employee_id int Filter by employee ID. Example: 1
 * @queryParam per_page int Results per page. Default: 15. Example: 15
 */
class AttendanceUserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'employee_id' => 'nullable|integer|exists:employees,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
