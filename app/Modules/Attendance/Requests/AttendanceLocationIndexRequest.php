<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam search string Filter by location name. Example: Kantor
 * @queryParam work_location_id int Filter by work location ID. Example: 1
 * @queryParam per_page int Results per page. Default: 15. Example: 15
 */
class AttendanceLocationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'work_location_id' => 'nullable|integer|exists:work_locations,id',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
