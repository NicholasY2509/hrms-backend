<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchUpdateAttendanceStatusRequest extends FormRequest
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
            'attendance_ids' => ['required', 'array', 'min:1'],
            'attendance_ids.*' => ['required', 'integer', 'exists:attendances,id'],
            'attendance_status_id' => ['required', 'integer', 'exists:attendance_statuses,id'],
        ];
    }
}
