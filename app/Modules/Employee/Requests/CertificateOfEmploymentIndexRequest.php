<?php

namespace App\Modules\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam employee_id integer Filter by employee ID. Example: 1
 * @queryParam per_page integer Number of items per page. Default: 15. Example: 20
 */
class CertificateOfEmploymentIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
