<?php

namespace App\Modules\ShiftExchange\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftExchangeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled via middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date'],
            'original_working_hour_id' => ['required', 'exists:working_hours,id'],
            'requested_working_hour_id' => ['required', 'exists:working_hours,id', 'different:original_working_hour_id'],
            'exchange_with_employee_id' => ['nullable', 'exists:employees,id'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}
