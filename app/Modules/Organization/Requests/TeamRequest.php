<?php

namespace App\Modules\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string required The name of the team. Example: Backend
 * @bodyParam work_location_id integer The ID of the work location. Example: 1
 * @bodyParam team_head_id integer The ID of the employee who heads the team. Example: 1
 */
class TeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'work_location_id' => ['nullable', 'integer', 'exists:work_locations,id'],
            'team_head_id' => ['nullable', 'integer', 'exists:employees,id'],
        ];
    }
}
