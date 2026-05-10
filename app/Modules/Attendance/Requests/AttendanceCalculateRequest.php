<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCalculateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * @queryParam start_date required The start date for calculation. Example: 2024-05-01
     * @queryParam end_date required The end date for calculation. Example: 2024-05-31
     */
    public function bodyParameters(): array
    {
        return [
            'start_date' => [
                'description' => 'The start date for calculation',
                'example' => '2024-05-01',
            ],
            'end_date' => [
                'description' => 'The end date for calculation',
                'example' => '2024-05-31',
            ],
        ];
    }
}
