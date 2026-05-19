<?php

namespace App\Modules\System\Resources;

use App\Modules\Attendance\Resources\AttendanceResource;
use App\Modules\Employee\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'employee' => $this['employee'] ? new EmployeeResource($this['employee']) : null,
            'attendance' => $this['attendance'] ? new AttendanceResource($this['attendance']) : null,
            'pending_requests' => $this['pending_requests'],
            'holidays' => $this['holidays']->map(function ($holiday) {
                return [
                    'name' => $holiday->name,
                    'date' => $holiday->date,
                    'description' => $holiday->description,
                ];
            })->values(),
            'tenure' => $this['tenure'],
            'attendance_summary' => $this['attendance_summary'],
            'attendance_rate' => $this['attendance_rate'],
        ];
    }
}
