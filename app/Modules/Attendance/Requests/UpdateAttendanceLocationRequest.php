<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string The name of the location. Example: Kantor Cabang
 * @bodyParam latitude string The latitude of the location. Example: -6.654321
 * @bodyParam longitude string The longitude of the location. Example: 106.654321
 * @bodyParam distance int The maximum distance allowed for scanning (in meters). Example: 200
 * @bodyParam work_location_id int The work location ID this scan point belongs to. Example: 2
 */
class UpdateAttendanceLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'latitude' => 'sometimes|string',
            'longitude' => 'sometimes|string',
            'distance' => 'sometimes|integer|min:1',
            'work_location_id' => 'sometimes|integer|exists:work_locations,id',
        ];
    }
}
