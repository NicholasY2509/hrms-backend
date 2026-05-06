<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string required The name of the working hour. Example: Shift Pagi
 * @bodyParam clock_in string required The clock-in time (HH:mm). Example: 08:00
 * @bodyParam clock_out string required The clock-out time (HH:mm). Example: 17:00
 */
class StoreWorkingHourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i',
        ];
    }
}
