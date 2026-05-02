<?php

namespace App\Modules\ApprovalWorkflow\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApprovalRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_position_id' => 'nullable|exists:work_positions,id',
            'work_location_id' => 'nullable|exists:work_locations,id',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'steps' => 'nullable|array',
            'steps.*.type_slug' => 'required|string',
            'steps.*.target_id' => 'nullable|integer',
            'steps.*.sequence' => 'nullable|integer',
        ];
    }
}
