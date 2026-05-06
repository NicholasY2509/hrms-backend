<?php

namespace App\Modules\Attendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @bodyParam name string The name of the machine. Example: Mesin Belakang
 * @bodyParam ip_address string The IP address of the machine. Example: 192.168.1.202
 * @bodyParam soap_port int SOAP port. Example: 80
 * @bodyParam udp_port int UDP port. Example: 4370
 * @bodyParam serial_number string The serial number of the machine. Example: CK987654321
 * @bodyParam work_location_id int The work location ID. Example: 2
 * @bodyParam attendance_location_id int The attendance location ID. Example: 2
 * @bodyParam online boolean Whether the machine is online. Example: true
 */
class UpdateZktecoMachineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'ip_address' => 'sometimes|ip',
            'soap_port' => 'nullable|integer',
            'udp_port' => 'nullable|integer',
            'serial_number' => 'nullable|string|max:255',
            'work_location_id' => 'sometimes|integer|exists:work_locations,id',
            'attendance_location_id' => 'nullable|integer|exists:attendance_locations,id',
            'online' => 'nullable|boolean',
        ];
    }
}
