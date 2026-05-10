<?php

namespace App\Modules\System\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class ReportStoreRequest extends FormRequest
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
            'name' => 'nullable|string',
            'type' => 'required|string',
            'format' => 'required|in:excel,pdf,csv,txt',
            'filters' => 'nullable|array'
        ];
    }

    /**
     * Body parameters for Scribe documentation.
     *
     * @return array
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The name of the report.',
                'example' => 'Monthly Attendance Report',
            ],
            'type' => [
                'description' => 'The type of report to generate.',
                'example' => 'attendance_list',
            ],
            'format' => [
                'description' => 'The format of the report.',
                'example' => 'excel',
            ],
            'filters' => [
                'description' => 'Optional filters for the report.',
                'example' => ['department_id' => 1],
            ],
        ];
    }
}
