<?php

namespace App\Modules\Leave\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnualLeaveStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'total' => ['required', 'numeric'],
            'annual_leave_year' => ['nullable', 'integer'],
            'annual_leave_at' => ['required', 'date'],
            'status' => ['required', 'string'],
            'keterangan' => ['required', 'string', 'max:255'],
            'deduction_details' => ['nullable', 'array'],
            'balance_before' => ['nullable', 'array'],
            'balance_after' => ['nullable', 'array'],
        ];
    }
    
    /**
     * @bodyParam employee_id integer required The ID of the employee. Example: 1
     * @bodyParam total float required The total amount of leave. Example: 2
     * @bodyParam annual_leave_year integer The year of the leave. Example: 2024
     * @bodyParam annual_leave_at date required The date of the leave. Example: 2024-05-01
     * @bodyParam status string required The status or type of leave log. Example: Tambah
     * @bodyParam keterangan string required Reason for adjustment. Example: Adjusting balance manually
     * @bodyParam deduction_details array Optional details of deduction. Example: []
     */
}
