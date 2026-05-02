<?php

namespace App\Modules\Employee\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam employee_id integer required The employee ID. Example: 1
 * @bodyParam work_position_id integer required The work position ID. Example: 1
 * @bodyParam document_no string required The document number. Example: COE-001/2024
 * @bodyParam request_date date required The request date of the certificate. Example: 2024-01-01
 * @bodyParam issued_date date required The issued date of the certificate. Example: 2024-01-05
 * @bodyParam note string The description or purpose of the certificate. Example: For visa application
 * @bodyParam attachment file The scanned document.
 */
class CertificateOfEmploymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'work_position_id' => ['required', 'integer', 'exists:work_positions,id'],
            'document_no' => ['required', 'string', 'max:255'],
            'request_date' => ['required', 'date'],
            'issued_date' => ['required', 'date', 'after_or_equal:request_date'],
            'note' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // max 5MB
        ];
    }
}
