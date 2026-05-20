<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceExportRequest extends FormRequest
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
            'type' => 'required|in:daily_report,personal_report,team_report',
            'format' => 'required|in:excel,pdf,csv,txt',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'employee_id' => 'nullable|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'team_id' => 'nullable|exists:teams,id',
            'work_position_id' => 'nullable|exists:work_positions,id',
            'attendance_status_id' => 'nullable|exists:attendance_statuses,id',
        ];
    }
}
