<?php

namespace App\Modules\Attendance\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZktecoAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uid' => $this->uid,
            'timestamp' => $this->timestamp,
            'attendance_at' => $this->attendance_at,
            'zkteco_machine_id' => $this->zkteco_machine_id,
            'machine' => $this->machine ? [
                'id' => $this->machine->id,
                'name' => $this->machine->name,
            ] : null,
            'attendance_user' => $this->attendance_user ? [
                'id' => $this->attendance_user->id,
                'employee_id' => $this->attendance_user->employee_id,
                'employee_name' => $this->attendance_user->employee?->full_name,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }
}
