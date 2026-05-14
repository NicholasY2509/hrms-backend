<?php

namespace App\Modules\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeDetailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = $this->route('type');
        $employeeId = $this->route('id');

        return match ($type) {
            'overview' => [
                'employee_id_number' => ['sometimes', 'required', 'string', 'max:255', 'unique:employees,employee_id_number,' . $employeeId],
                'initial_name' => ['nullable', 'string', 'max:50'],
                'company_email' => ['nullable', 'email', 'max:255'],
                'work_position_id' => ['sometimes', 'required', 'exists:work_positions,id'],
                'department_id' => ['sometimes', 'required', 'exists:departments,id'],
                'work_location_id' => ['sometimes', 'required', 'exists:work_locations,id'],
                'team_id' => ['nullable', 'exists:teams,id'],
                'supervisor_id' => ['nullable', 'exists:employees,id'],
                'employee_status_id' => ['nullable', 'exists:employee_statuses,id'],
                'work_employee_status_id' => ['nullable', 'exists:work_employee_statuses,id'],
                'annual_leave_2' => ['nullable', 'integer'],
                'annual_leave_3' => ['nullable', 'integer'],
            ],
            'personal' => [
                'full_name' => ['required', 'string', 'max:255'],
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['nullable', 'string', 'max:255'],
                'phone_number' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'gender_id' => ['required', 'exists:genders,id'],
                'marital_status_id' => ['required', 'exists:marital_statuses,id'],
                'id_card_number' => ['required', 'string', 'max:255'],
                'religion_id' => ['required', 'exists:religions,id'],
                'blood_group_id' => ['required', 'exists:blood_groups,id'],
                'birth_place' => ['required', 'string', 'max:255'],
                'birth_date' => ['required', 'date'],
                'current_address' => ['required', 'string'],
                'residence_address' => ['required', 'string'],
            ],
            'family' => [
                '*.id' => ['nullable', 'integer'],
                '*.full_name' => ['required', 'string', 'max:255'],
                '*.family_relationship_id' => ['required', 'exists:family_relationships,id'],
                '*.gender_id' => ['required', 'exists:genders,id'],
                '*.place_birth' => ['nullable', 'string', 'max:255'],
                '*.date_birth' => ['required', 'date'],
                '*.id_card_number' => ['required', 'string', 'max:255'],
            ],
            'education' => [
                '*.id' => ['nullable', 'integer'],
                '*.study' => ['nullable', 'max:255'],
                '*.start_year' => ['required', 'string', 'size:4'],
                '*.end_year' => ['required', 'string', 'size:4'],
                '*.school_name' => ['required', 'string', 'max:255'],
            ],
            'experience' => [
                '*.id' => ['nullable', 'integer'],
                '*.office_name' => ['nullable', 'string', 'max:255'],
                '*.office_address' => ['nullable', 'string'],
                '*.office_phone' => ['required', 'string', 'max:20'],
                '*.start_year' => ['nullable', 'string', 'size:4'],
                '*.end_year' => ['nullable', 'string', 'size:4'],
                '*.work_position' => ['nullable', 'string', 'max:255'],
                '*.reason' => ['nullable', 'string'],
            ],
            'vehicle' => [
                '*.id' => ['nullable', 'integer'],
                '*.vehicle_name' => ['required', 'string', 'max:255'],
                '*.vehicle_year' => ['required', 'string', 'size:4'],
                '*.plate_number' => ['required', 'string', 'max:50'],
                '*.vehicle_owner' => ['required', 'string', 'max:255'],
            ],
            'license' => [
                '*.id' => ['nullable', 'integer'],
                '*.license_number' => ['required', 'string', 'max:50'],
                '*.driver_license_type_id' => ['required', 'exists:driver_license_types,id'],
            ],
            'insurance' => [
                '*.id' => ['nullable', 'integer'],
                '*.insurance_name' => ['required', 'string', 'max:255'],
                '*.card_number' => ['required', 'string', 'max:50'],
            ],
            'bank' => [
                '*.id' => ['nullable', 'integer'],
                '*.bank_name' => ['required', 'string', 'max:255'],
                '*.account_number' => ['required', 'string', 'max:50'],
                '*.account_name' => ['required', 'string', 'max:255'],
            ],
            'training', 'performance', 'inventory', 'warning', 'contract', 'emergency', 'attachment', 'social_security' => [
                'id' => ['nullable', 'integer'],
                '*' => ['sometimes', 'required'],
            ],
            default => [],
        };
    }
}
