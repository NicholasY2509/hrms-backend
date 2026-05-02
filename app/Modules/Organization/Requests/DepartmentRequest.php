<?php

namespace App\Modules\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string required The name of the department. Example: Engineering
 * @bodyParam dept_head_id integer The ID of the employee who heads the department. Example: 1
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
            'dept_head_id' => ['nullable', 'integer', 'exists:employees,id'],
        ];
    }
}
