<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam working_hour_id int required The ID of the working hour (shift). Example: 1
 * @bodyParam attendance_at date required The date of the attendance. Example: 2024-01-01
 */
class AttendanceWorkingHourUpdateRequest extends FormRequest
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
            'working_hour_id' => 'required|integer|exists:working_hours,id',
            'attendance_at' => 'required|date',
        ];
    }
}
