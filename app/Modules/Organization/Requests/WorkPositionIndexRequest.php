<?php

namespace App\Modules\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WorkPositionIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * The search term to filter by name or alias.
             * @example Manager
             */
            'search' => 'nullable|string',
            
            /**
             * The number of results per page.
             * @example 15
             */
            'per_page' => 'nullable|integer',
        ];
    }
}
