<?php

namespace App\Modules\Attendance\Resources;

use App\Modules\Employee\Resources\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AttendanceWorkingHourResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $workingHour = $this->working_hour;
        $date = $this->attendance_at;

        $shiftStart = null;
        $shiftEnd = null;

        if ($date && $workingHour) {
            $shiftStart = Carbon::parse($date . ' ' . $workingHour->clock_in);
            $shiftEnd = Carbon::parse($date . ' ' . $workingHour->clock_out);

            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }
        }

        return [
            'id' => $this->id,
            'date' => $date,
            'shift_start' => $shiftStart?->toDateTimeString(),
            'shift_end' => $shiftEnd?->toDateTimeString(),
            'status' => $this->attendance?->attendance_status?->name,
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'working_hour' => $this->whenLoaded('working_hour'),
            'attendance' => $this->whenLoaded('attendance'),
            'working_hour_id' => $this->working_hour_id,
            'employee_id' => $this->employee_id,
        ];
    }
}
