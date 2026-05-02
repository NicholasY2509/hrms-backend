<?php

namespace App\Modules\ApprovalWorkflow\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'approval_scheme_id' => 'required|exists:approval_schemes,id',
            'work_position_id' => 'nullable|exists:work_positions,id',
            'work_location_id' => 'nullable|exists:work_locations,id',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'steps' => 'nullable|array',
            'steps.*.type_slug' => 'required|string',
            'steps.*.target_id' => 'nullable|integer',
            'steps.*.sequence' => 'nullable|integer',
        ];
    }
}
