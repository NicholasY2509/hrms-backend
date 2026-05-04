<?php

namespace App\Modules\ApprovalWorkflow\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalRequestTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'model_class' => 'required|string|unique:approval_request_types,model_class',
            'slug' => 'required|string|unique:approval_request_types,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
