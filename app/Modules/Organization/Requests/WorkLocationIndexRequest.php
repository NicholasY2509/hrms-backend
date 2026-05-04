<?php

namespace App\Modules\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam search string Filter by name. Example: Head Office
 * @queryParam per_page integer Number of items per page. Default: 15. Example: 20
 */
class WorkLocationIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
