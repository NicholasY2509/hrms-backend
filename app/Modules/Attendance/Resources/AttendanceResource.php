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
        $attendanceWorkingHour = $this->attendance_working_hour;
        $workingHour = $attendanceWorkingHour?->working_hour;
        $date = $attendanceWorkingHour?->attendance_at;

        $shiftStart = null;
        $shiftEnd = null;

        if ($date && $workingHour) {
            $shiftStart = \Carbon\Carbon::parse($date . ' ' . $workingHour->clock_in);
            $shiftEnd = \Carbon\Carbon::parse($date . ' ' . $workingHour->clock_out);

            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }
        }

        return [
            'id' => $this->id,
            'attendance_at' => $date,
            'check_in' => $this->incoming_scan,
            'check_out' => $this->outgoing_scan,
            'shift_start' => $shiftStart?->toDateTimeString(),
            'shift_end' => $shiftEnd?->toDateTimeString(),
            'status' => $this->attendance_status?->name,
            'all_scans' => $this->all_scans,
            'incoming_photo' => $this->incoming_photo,
            'outgoing_photo' => $this->outgoing_photo,
        ];
    }
}
