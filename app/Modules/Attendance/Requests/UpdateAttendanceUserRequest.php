<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam employee_id int The ID of the employee. Example: 1
 * @bodyParam uid string The unique ID from the biometric machine. Example: 124
 * @bodyParam zkteco_machine_id int The ID of the ZKTeco machine. Example: 2
 */
class UpdateAttendanceUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $attendanceUser = $this->route('attendance_user');
        $id = is_object($attendanceUser) ? $attendanceUser->id : $attendanceUser;

        return [
            'employee_id' => 'sometimes|integer|exists:employees,id|unique:attendance_users,employee_id,' . $id . ',id,deleted_at,NULL',
            'uid' => 'sometimes|string|max:255',
            'zkteco_machine_id' => 'nullable|integer',
        ];
    }
}
