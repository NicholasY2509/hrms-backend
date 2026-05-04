<?php

namespace App\Modules\Career\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam employee_id integer required The employee ID. Example: 1
 * @bodyParam career_type_id integer required The career type ID. Example: 1
 * @bodyParam before_employee_status_id integer required The before employee status ID. Example: 1
 * @bodyParam before_work_position_id integer required The before work position ID. Example: 1
 * @bodyParam before_department_id integer required The before department ID. Example: 1
 * @bodyParam before_work_location_id integer required The before work location ID. Example: 1
 * @bodyParam before_team_id integer The before team ID. Example: 1
 * @bodyParam before_supervisor_id integer The before supervisor ID. Example: 1
 * @bodyParam after_employee_status_id integer required The after employee status ID. Example: 2
 * @bodyParam after_work_position_id integer required The after work position ID. Example: 2
 * @bodyParam after_department_id integer required The after department ID. Example: 2
 * @bodyParam after_work_location_id integer required The after work location ID. Example: 2
 * @bodyParam after_team_id integer The after team ID. Example: 2
 * @bodyParam after_supervisor_id integer The after supervisor ID. Example: 2
 * @bodyParam career_at date required The effective date of the career change. Example: 2024-01-01
 * @bodyParam note string The note/reason for the career change. Example: Promotion due to excellent performance
 */
class CareerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'career_type_id' => ['required', 'integer', 'exists:career_types,id'],
            
            'before_employee_status_id' => ['required', 'integer'],
            'before_work_position_id' => ['required', 'integer'],
            'before_department_id' => ['required', 'integer'],
            'before_work_location_id' => ['required', 'integer'],
            'before_team_id' => ['nullable', 'integer'],
            'before_supervisor_id' => ['nullable', 'integer'],
            
            'after_employee_status_id' => ['required', 'integer'],
            'after_work_position_id' => ['required', 'integer'],
            'after_department_id' => ['required', 'integer'],
            'after_work_location_id' => ['required', 'integer'],
            'after_team_id' => ['nullable', 'integer'],
            'after_supervisor_id' => ['nullable', 'integer'],
            
            'career_at' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ];
    }
}
