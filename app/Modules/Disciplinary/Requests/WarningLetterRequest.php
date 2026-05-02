<?php

namespace App\Modules\Disciplinary\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam employee_id integer required The employee ID. Example: 1
 * @bodyParam warning_letter_type_id integer required The warning letter type ID. Example: 1
 * @bodyParam document_no string required The document number. Example: WL-001/2024
 * @bodyParam name string required The title or name of the warning. Example: Surat Peringatan I
 * @bodyParam warning_at date required The date of the warning. Example: 2024-01-01
 * @bodyParam start_date date required The start date of the warning period. Example: 2024-01-01
 * @bodyParam end_date date required The end date of the warning period. Example: 2024-06-01
 * @bodyParam note string The description or reason for the warning. Example: Late attendance 5 times
 * @bodyParam attachment file The scanned document or evidence.
 */
class WarningLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'warning_letter_type_id' => ['required', 'integer', 'exists:warning_letter_types,id'],
            'document_no' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'warning_at' => ['required', 'date'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'note' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'], // max 5MB
        ];
    }
}
