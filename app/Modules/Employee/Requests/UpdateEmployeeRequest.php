<?php

namespace App\Modules\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
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
        $employeeId = $this->route('employee');

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'initial_name' => ['nullable', 'string', 'max:50'],
            'employee_id_number' => ['sometimes', 'required', 'string', 'max:255', 'unique:employees,employee_id_number,' . $employeeId],
            'id_card_number' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'department_id' => ['sometimes', 'required', 'exists:departments,id'],
            'work_position_id' => ['sometimes', 'required', 'exists:work_positions,id'],
            'work_location_id' => ['sometimes', 'required', 'exists:work_locations,id'],
            'supervisor_id' => ['nullable', 'exists:supervisors,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'gender_id' => ['nullable', 'integer'],
            'marital_status_id' => ['nullable', 'integer'],
            'religion_id' => ['nullable', 'integer'],
            'blood_group_id' => ['nullable', 'integer'],
            'place_birth' => ['nullable', 'string', 'max:255'],
            'date_birth' => ['nullable', 'date'],
            'join_date' => ['sometimes', 'required', 'date'],
            'resign_date' => ['nullable', 'date'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'handphone' => ['nullable', 'string', 'max:20'],
            'current_address' => ['nullable', 'string'],
            'residence_address' => ['nullable', 'string'],
            'annual_leave_1' => ['nullable', 'integer'],
            'annual_leave_2' => ['nullable', 'integer'],
            'annual_leave_3' => ['nullable', 'integer'],
            'is_get_annual_leave' => ['nullable', 'boolean'],
            'avatar' => ['nullable', 'string'],
        ];
    }
}
