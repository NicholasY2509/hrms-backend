<?php

namespace App\Modules\System\Resources;

use App\Modules\Attendance\Resources\AttendanceResource;
use App\Modules\Employee\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
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
            'leave' => $this['leave'],
            'holidays' => $this['holidays']->map(function ($holiday) {
                return [
                    'name' => $holiday->name,
                    'date' => $holiday->date,
                    'description' => $holiday->description,
                ];
            }),
            'tenure' => $this['tenure'],
        ];
    }
}
