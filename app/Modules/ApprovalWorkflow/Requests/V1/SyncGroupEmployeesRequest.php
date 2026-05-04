<?php

namespace App\Modules\ApprovalWorkflow\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class SyncGroupEmployeesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ];
    }
}
