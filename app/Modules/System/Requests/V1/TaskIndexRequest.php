<?php

namespace App\Modules\System\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam page int The page number for pagination. Example: 1
 * @queryParam per_page int The number of items per page. Example: 15
 * @queryParam type string Filter by task type. Example: attendance_working_hour_import
 * @queryParam status string Filter by task status (pending, processing, completed, failed). Example: completed
 * @queryParam search string Search by task message. Example: success
 * @queryParam start_date date Filter by completed_at starting date (Y-m-d). Example: 2024-01-01
 * @queryParam end_date date Filter by completed_at ending date (Y-m-d). Example: 2024-01-31
 */
class TaskIndexRequest extends FormRequest
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
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'type' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:pending,processing,completed,failed'],
            'search' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
