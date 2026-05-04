<?php

namespace App\Modules\UnpaidLeave\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnpaidLeaveTypeRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'background_color' => 'nullable|string|max:255',
            'border_color' => 'nullable|string|max:255',
            'text_color' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:0',
            'is_annual_leave_deduction' => 'sometimes|required|boolean',
        ];
    }
}
