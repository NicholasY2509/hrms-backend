<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeAttendanceStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ];
    }
}
