<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam settings object required An array/object of key-value pairs representing the settings to update. Example: {"attendance_clock_in_start_minutes": "90", "attendance_min_gap_minutes": "45"}
 */
class UpdateAttendanceSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.*' => ['required'], // Can be string, integer, etc based on the setting type
        ];
    }
}
