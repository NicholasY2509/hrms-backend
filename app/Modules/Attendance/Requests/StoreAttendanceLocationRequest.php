<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string required The name of the location. Example: Kantor Pusat
 * @bodyParam latitude string required The latitude of the location. Example: -6.123456
 * @bodyParam longitude string required The longitude of the location. Example: 106.123456
 * @bodyParam distance int required The maximum distance allowed for scanning (in meters). Example: 100
 * @bodyParam work_location_id int required The work location ID this scan point belongs to. Example: 1
 */
class StoreAttendanceLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'distance' => 'required|integer|min:1',
            'work_location_id' => 'required|integer|exists:work_locations,id',
        ];
    }
}
