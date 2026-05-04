<?php

namespace App\Modules\ApprovalWorkflow\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePolicyStepsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'steps' => 'required|array',
            'steps.*.type_slug' => 'required|exists:approval_step_types,slug',
            'steps.*.target_id' => 'nullable|integer',
            'steps.*.sequence' => 'nullable|integer',
        ];
    }
}
