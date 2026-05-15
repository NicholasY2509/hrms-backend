<?php

namespace App\Modules\UnpaidLeave\Requests\V1;

use Illuminate\Foundation\Http\FormRequest;

class GetHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    /**
     * @queryParam search string Filter by name.
     * @queryParam start_date string Filter by start date (YYYY-MM-DD).
     * @queryParam end_date string Filter by end date (YYYY-MM-DD).
     * @queryParam per_page integer Number of items per page. Defaults to 15.
     * @queryParam page integer Page number.
     */
    public function bodyParameters(): array
    {
        return [];
    }
}
