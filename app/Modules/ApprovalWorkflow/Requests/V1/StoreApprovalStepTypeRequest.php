<?php

namespace App\Modules\ApprovalWorkflow\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalStepTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => 'required|string|unique:approval_step_types,slug',
            'name' => 'required|string|max:255',
            'needs_target' => 'required|boolean',
            'description' => 'nullable|string',
        ];
    }
}
