<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam employee_id int required The ID of the employee. Example: 1
 * @bodyParam uid string required The unique ID from the biometric machine. Example: 123
 * @bodyParam zkteco_machine_id int The ID of the ZKTeco machine. Example: 1
 */
class StoreAttendanceUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|integer|exists:employees,id|unique:attendance_users,employee_id,NULL,id,deleted_at,NULL',
            'uid' => 'required|string|max:255',
            'zkteco_machine_id' => 'nullable|integer',
        ];
    }
}
