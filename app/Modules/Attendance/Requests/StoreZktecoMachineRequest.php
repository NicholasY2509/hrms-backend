<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string required The name of the machine. Example: Mesin Depan
 * @bodyParam ip_address string required The IP address of the machine. Example: 192.168.1.201
 * @bodyParam soap_port int SOAP port (usually 80). Example: 80
 * @bodyParam udp_port int UDP port (usually 4370). Example: 4370
 * @bodyParam serial_number string The serial number of the machine. Example: CK123456789
 * @bodyParam work_location_id int required The work location ID. Example: 1
 * @bodyParam attendance_location_id int The attendance location ID. Example: 1
 */
class StoreZktecoMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'soap_port' => 'nullable|integer',
            'udp_port' => 'nullable|integer',
            'serial_number' => 'nullable|string|max:255',
            'work_location_id' => 'required|integer|exists:work_locations,id',
            'attendance_location_id' => 'nullable|integer|exists:attendance_locations,id',
        ];
    }
}
