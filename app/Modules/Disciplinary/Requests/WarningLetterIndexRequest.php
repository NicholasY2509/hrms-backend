<?php

namespace App\Modules\Disciplinary\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam employee_id integer Filter by employee ID. Example: 1
 * @queryParam warning_letter_type_id integer Filter by warning letter type ID. Example: 1
 * @queryParam per_page integer Number of items per page. Default: 15. Example: 20
 */
class WarningLetterIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'warning_letter_type_id' => ['nullable', 'integer', 'exists:warning_letter_types,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
