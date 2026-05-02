<?php

namespace App\Modules\Overtime\Requests;

use App\Modules\Overtime\Models\Overtime;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOvertimeRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'overtime_type_id' => 'nullable|exists:overtime_types,id',
            'estimated_overtime_price' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'type' => 'nullable|in:' . Overtime::TYPE_GENERAL . ',' . Overtime::TYPE_DAC . ',' . Overtime::TYPE_HOLIDAY,
        ];
    }
}
