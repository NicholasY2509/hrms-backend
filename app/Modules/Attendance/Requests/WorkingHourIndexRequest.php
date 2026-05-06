<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam search string Filter by working hour name. Example: Pagi
 * @queryParam per_page int Results per page. Default: 15. Example: 15
 */
class WorkingHourIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }
}
