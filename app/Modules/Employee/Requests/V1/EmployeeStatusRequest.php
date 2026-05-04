<?php

namespace App\Modules\Employee\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeStatusRequest extends FormRequest
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
        if ($this->isMethod('GET')) {
            return [
                'search' => 'nullable|string',
                'per_page' => 'nullable|integer|min:1|max:100',
            ];
        }

        $rules = [
            'name' => [
                $this->isMethod('POST') ? 'required' : 'sometimes',
                'string',
                'max:255',
            ],
        ];

        if ($this->isMethod('POST')) {
            $rules['name'][] = 'unique:employee_statuses,name';
        }

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'][] = 'unique:employee_statuses,name,' . $this->route('employee_status');
        }

        return $rules;
    }

    /**
     * Get query parameter documentation for Scribe.
     */
    public function queryParameters(): array
    {
        return [
            'per_page' => [
                'description' => 'Number of results per page.',
                'example' => 15,
            ],
            'search' => [
                'description' => 'Search by name.',
                'example' => 'Permanent',
            ],
        ];
    }
}
