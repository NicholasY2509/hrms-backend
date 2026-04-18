<?php

namespace App\Modules\Attendance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'attendance_at' => $this->attendance_working_hour?->attendance_at,
            'check_in' => $this->incoming_time,
            'check_out' => $this->outgoing_time,
            'status' => $this->attendance_status?->name,
            'all_scans' => $this->all_scans,
            'incoming_photo' => $this->incoming_photo,
            'outgoing_photo' => $this->outgoing_photo,
        ];
    }
}
