<?php

namespace App\Modules\ApprovalWorkflow\Requests\V1\Portal\Management;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam type string Filter by request type (e.g., Overtime, UnpaidLeave).
 * @queryParam per_page int Results per page. Default: 15.
 */
class GetApprovalActionRequest extends FormRequest
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
            'type' => 'nullable',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
