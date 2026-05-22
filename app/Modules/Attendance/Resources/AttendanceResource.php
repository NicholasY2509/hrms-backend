<?php

namespace App\Modules\Attendance\Resources;

use App\Modules\Attendance\Services\MobileAttendanceService;
use App\Services\StorageService;
use Carbon\Carbon;
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
            $shiftStart = Carbon::parse($date . ' ' . $workingHour->clock_in);
            $shiftEnd = Carbon::parse($date . ' ' . $workingHour->clock_out);

            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }
        }

        $isClockedIn = false;
        if ($this->resource) {
            $attendanceService = app(MobileAttendanceService::class);
            $isClockedIn = $attendanceService->isCurrentlyClockedIn($this->resource);
        }

        $now = Carbon::now();
        $isLocked = false;
        $lockTitle = null;
        $lockMessage = null;

        if ($shiftStart && $shiftEnd) {
            if (!$isClockedIn) {
                $windowStart = (clone $shiftStart)->subHour();
                $clockInDeadline = (clone $shiftEnd)->subHour();
                
                if ($now->lessThan($windowStart)) {
                    $isLocked = true;
                    $lockTitle = 'Shift Belum Tersedia';
                    $lockMessage = 'Shift Anda belum dimulai. Anda baru dapat absen masuk pada pukul ' . $windowStart->format('H:i') . '.';
                } elseif ($now->greaterThanOrEqualTo($shiftEnd)) {
                    $isLocked = true;
                    $lockTitle = 'Shift Sudah Berakhir';
                    $lockMessage = 'Shift Anda untuk jadwal ini sudah berakhir.';
                } elseif ($now->greaterThanOrEqualTo($clockInDeadline)) {
                    $isLocked = true;
                    $lockTitle = 'Batas Absen Masuk Berakhir';
                    $lockMessage = 'Anda tidak dapat absen masuk karena waktu shift akan segera berakhir (< 1 jam).';
                }
            } else {
                $windowEnd = (clone $shiftEnd)->addHours(5);
                if ($now->greaterThan($windowEnd)) {
                    $isLocked = true;
                    $lockTitle = 'Batas Absen Berakhir';
                    $lockMessage = 'Batas waktu untuk melakukan absensi pulang pada sesi ini sudah berakhir.';
                }
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
            'is_clocked_in' => $isClockedIn,
            'is_locked' => $isLocked,
            'lock_title' => $lockTitle,
            'lock_message' => $lockMessage,
            'mobile_scans' => MobileScanResource::collection(collect($this->mobile_scans ?? [])),
            'all_scans' => $this->all_scans,
            'incoming_photo' => StorageService::url($this->incoming_photo),
            'outgoing_photo' => StorageService::url($this->outgoing_photo),
        ];
    }
}
