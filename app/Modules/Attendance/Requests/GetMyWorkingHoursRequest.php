<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam start_date date Filter by start date (YYYY-MM-DD). Example: 2026-05-01
 * @queryParam end_date date Filter by end date (YYYY-MM-DD). Example: 2026-05-31
 * @queryParam per_page int Results per page. Default: 15. Example: 15
 */
class GetMyWorkingHoursRequest extends FormRequest
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
            'start_date' => 'nullable|date_format:Y-m-d',
            'end_date'   => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'per_page'   => 'nullable|integer|min:1|max:100',
        ];
    }
}
