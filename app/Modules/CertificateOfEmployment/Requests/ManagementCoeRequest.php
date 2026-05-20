<?php

namespace App\Modules\CertificateOfEmployment\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam employee_id integer required The ID of the employee. Example: 1
 */
class ManagementCoeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
        ];
    }
}
