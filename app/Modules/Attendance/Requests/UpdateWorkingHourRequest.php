<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string The name of the working hour. Example: Shift Sore
 * @bodyParam clock_in string The clock-in time (HH:mm). Example: 14:00
 * @bodyParam clock_out string The clock-out time (HH:mm). Example: 22:00
 */
class UpdateWorkingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'clock_in' => 'sometimes|date_format:H:i',
            'clock_out' => 'sometimes|date_format:H:i',
        ];
    }
}
