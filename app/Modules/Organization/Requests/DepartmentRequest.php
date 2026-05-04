<?php

namespace App\Modules\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string required The name of the department. Example: Engineering
 * @bodyParam heads array optional Array of department head assignments per work location.
 * @bodyParam heads.*.work_location_id integer required The work location ID. Example: 1
 * @bodyParam heads.*.employee_id integer required The employee ID to assign as head. Example: 5
 */
class DepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'heads' => ['nullable', 'array'],
            'heads.*.work_location_id' => ['nullable', 'integer', 'exists:work_locations,id'],
            'heads.*.employee_id' => ['required_with:heads', 'integer', 'exists:employees,id'],
        ];
    }
}
