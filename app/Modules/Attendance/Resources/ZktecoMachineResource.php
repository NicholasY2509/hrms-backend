<?php

namespace App\Modules\Attendance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZktecoMachineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ip_address' => $this->ip_address,
            'soap_port' => $this->soap_port,
            'udp_port' => $this->udp_port,
            'serial_number' => $this->serial_number,
            'work_location_id' => $this->work_location_id,
            'work_location' => $this->work_location ? [
                'id' => $this->work_location->id,
                'name' => $this->work_location->name,
            ] : null,
            'attendance_location_id' => $this->attendance_location_id,
            'attendance_location' => $this->attendance_location ? [
                'id' => $this->attendance_location->id,
                'name' => $this->attendance_location->name,
            ] : null,
            'online' => (bool) $this->online,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
