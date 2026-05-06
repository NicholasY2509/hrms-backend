<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam uid string Filter by machine UID. Example: 123
 * @queryParam zkteco_machine_id int Filter by machine ID. Example: 1
 * @queryParam start_date date Filter by start date (YYYY-MM-DD). Example: 2024-01-01
 * @queryParam end_date date Filter by end date (YYYY-MM-DD). Example: 2024-01-31
 * @queryParam per_page int Results per page. Default: 15. Example: 15
 */
class ZktecoAttendanceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'uid' => 'nullable|string|max:255',
            'zkteco_machine_id' => 'nullable|integer|exists:zkteco_machines,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
