<?php

namespace App\Modules\Career\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam employee_id integer Filter by employee ID. Example: 1
 * @queryParam career_type_id integer Filter by career type ID. Example: 1
 * @queryParam per_page integer Number of items per page. Default: 15. Example: 20
 */
class CareerIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'career_type_id' => ['nullable', 'integer', 'exists:career_types,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
