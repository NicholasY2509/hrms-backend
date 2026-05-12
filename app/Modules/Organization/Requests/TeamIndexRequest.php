<?php

namespace App\Modules\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam search string Filter by name. Example: Backend
 * @queryParam work_location_id integer Filter by work location. Example: 1
 * @queryParam per_page integer Number of items per page. Default: 15. Example: 20
 */
class TeamIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'work_location_id' => ['nullable', 'integer', 'exists:work_locations,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
