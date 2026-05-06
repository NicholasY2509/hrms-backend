<?php

namespace App\Modules\Attendance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceMobileScanResource extends JsonResource
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
            'attendance_id' => $this->attendance_id,
            'employee_id' => $this->employee_id,
            'scan_type' => $this->scan_type,
            'scan_time' => $this->scan_time,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'location' => [
                'id' => $this->location_id,
                'name' => $this->location?->name,
            ],
            'photo' => $this->photo,
            'device_id' => $this->device_id,
            'created_at' => $this->created_at,
        ];
    }
}
