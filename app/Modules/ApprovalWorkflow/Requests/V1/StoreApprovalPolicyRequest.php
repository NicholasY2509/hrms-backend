<?php

namespace App\Modules\ApprovalWorkflow\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreApprovalPolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'approvable_type' => 'required|string',
            'work_position_id' => 'required|integer',
        ];
    }
}
