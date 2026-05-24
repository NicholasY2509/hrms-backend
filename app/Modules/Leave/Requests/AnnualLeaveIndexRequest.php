<?php

namespace App\Modules\Leave\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam employee_id integer Filter by employee ID. Example: 1
 * @queryParam status string Filter by status. Example: APPROVED
 * @queryParam search string Search by employee name, NIK, or keterangan. Example: John
 * @queryParam per_page integer Number of items per page. Default: 15. Example: 20
 */
class AnnualLeaveIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'max:50'],
            'search' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
