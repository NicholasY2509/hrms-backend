<?php

namespace App\Modules\ApprovalWorkflow\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApprovalStepTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'slug' => "required|string|unique:approval_step_types,slug,{$id}",
            'name' => 'required|string|max:255',
            'needs_target' => 'required|boolean',
            'description' => 'nullable|string',
        ];
    }
}
