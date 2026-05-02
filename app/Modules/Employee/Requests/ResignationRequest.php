<?php

namespace App\Modules\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam employee_id integer required The employee ID. Example: 1
 * @bodyParam effective_date date required The effective date of resignation. Example: 2024-01-01
 * @bodyParam reason string The reason for resignation. Example: Better opportunity
 * @bodyParam attachment file The scanned resignation letter.
 */
class ResignationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'effective_date' => ['required', 'date'],
            'reason' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // max 5MB
        ];
    }
}
