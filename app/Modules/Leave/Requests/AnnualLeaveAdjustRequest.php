<?php

namespace App\Modules\Leave\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnualLeaveAdjustRequest extends FormRequest
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
            'annual_leave_2' => ['required', 'numeric'],
            'annual_leave_3' => ['required', 'numeric'],
            'keterangan' => ['required', 'string', 'max:255'],
        ];
    }
    
    /**
     * @bodyParam annual_leave_2 float required The new amount for last year's annual leave. Example: 9
     * @bodyParam annual_leave_3 float required The new amount for current year's annual leave. Example: 3
     * @bodyParam keterangan string required Reason for adjustment. Example: Adjusting balance manually
     */
}
