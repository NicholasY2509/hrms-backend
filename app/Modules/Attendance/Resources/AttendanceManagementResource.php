<?php

namespace App\Modules\Attendance\Resources;

use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceManagementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $attendanceWorkingHour = $this->attendance_working_hour;
        $employee = $attendanceWorkingHour?->employee;
        $workingHour = $attendanceWorkingHour?->working_hour;

        return [
            'id' => $this->id,
            'employee_id' => $employee?->id,
            'employee' => $employee ? [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'nik' => $employee->employee_id_number,
            ] : null,
            'attendance_at' => $attendanceWorkingHour?->attendance_at,
            'working_hour' => $workingHour ? [
                'id' => $workingHour->id,
                'name' => $workingHour->name,
                'clock_in' => $workingHour->clock_in,
                'clock_out' => $workingHour->clock_out,
            ] : null,
            'check_in' => $this->incoming_scan,
            'check_out' => $this->outgoing_scan,
            'attendance_status_id' => $this->attendance_status_id,
            'status' => $this->attendance_status?->name,
            'incoming_photo' => $this->incoming_photo ? StorageService::url($this->incoming_photo) : null,
            'outgoing_photo' => $this->outgoing_photo ? StorageService::url($this->outgoing_photo) : null,
            'incoming_location' => $this->incoming_location ? [
                'id' => $this->incoming_location->id,
                'name' => $this->incoming_location->name,
            ] : null,
            'outgoing_location' => $this->outgoing_location ? [
                'id' => $this->outgoing_location->id,
                'name' => $this->outgoing_location->name,
            ] : null,
            'all_scans' => $this->all_scans,
            'mobile_scans' => $this->mobile_scans,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
