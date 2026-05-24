<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\Attendance;
use App\Modules\Attendance\Models\AttendanceSetting;
use App\Modules\Attendance\Repositories\AttendanceRepository;
use App\Modules\Attendance\Jobs\ProcessAttendanceCalculationJob;
use App\Modules\System\Models\Task;
use App\Modules\System\Services\TaskService;
use App\Modules\System\Traits\HasTaskProgress;
use App\Modules\UnpaidLeave\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AttendanceCalculationService
{
    use HasTaskProgress;

    protected AttendanceRepository $attendanceRepository;
    protected TaskService $taskService;

    public function __construct(AttendanceRepository $attendanceRepository, TaskService $taskService)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->taskService = $taskService;
    }

    /**
     * Initiate a background attendance calculation.
     *
     * @param string $startDate
     * @param string $endDate
     * @param array $payload
     * @return Task
     */
    public function initiateCalculation(string $startDate, string $endDate, array $payload = []): Task
    {
        $task = $this->taskService->createTask(
            'attendance_calculation',
            'Waiting for background process...',
            $payload
        );

        ProcessAttendanceCalculationJob::dispatch(
            $task,
            $startDate,
            $endDate
        );

        return $task;
    }

    /**
     * Main entry point for attendance calculation.
     */
    public function calculate(string $startDate, string $endDate): void
    {
        ini_set('memory_limit', '1024M');

        DB::beginTransaction();

        try {
            $this->updateProgress(2, 'Mengambil daftar karyawan...');
            $employees = $this->attendanceRepository->getEmployeesForCalculation();
            $dates = CarbonPeriod::create($startDate, $endDate)->toArray();
            $totalSteps = count($employees) * count($dates);
            $currentStep = 0;

            $this->updateProgress(5, 'Mengecek hari libur...');
            $holidays = Holiday::query()
                ->whereBetween('date', [$startDate, $endDate])
                ->pluck('date')
                ->toArray();

            $this->updateProgress(8, 'Memuat data cuti dan izin...');
            $employeeIds = $employees->pluck('id')->toArray();
            $unpaidLeaves = $this->attendanceRepository->getLeavesInRange($employeeIds, $startDate, $endDate)
                ->groupBy('employee_id');

            $this->updateProgress(12, 'Memuat jadwal kerja...');
            $attendanceWorkingHours = $this->attendanceRepository->getWorkingHoursInRange($employeeIds, $startDate, $endDate)
                ->groupBy('employee_id')
                ->map(fn($ewh) => $ewh->keyBy('attendance_at'));

            $this->updateProgress(15, 'Memuat data absensi yang sudah ada...');
            $workingHourIds = $attendanceWorkingHours->flatMap(fn($ewh) => $ewh->pluck('id'))->toArray();
            $existingAttendances = $this->attendanceRepository->getAttendancesByWorkingHourIds($workingHourIds)
                ->keyBy('attendance_working_hour_id');

            $this->updateProgress(18, 'Memuat data log mesin absensi...');
            $uids = $employees->flatMap->attendance_users->pluck('uid')->unique()->toArray();
            $queryEndDate = Carbon::parse($endDate)->addDay()->format('Y-m-d');
            $zktecoAttendances = $this->attendanceRepository->getZktecoAttendancesInRange($uids, $startDate, $queryEndDate)
                ->groupBy('uid')
                ->map(fn($ua) => $ua->groupBy('attendance_at'));

            $this->updateProgress(20, 'Memetakan tanggal registrasi karyawan...');
            $registrationMap = $this->preCalculateRegistrationMap($employees, $startDate, $endDate);

            foreach ($employees as $employee) {
                $attendanceUsers = $employee->attendance_users;
                $employeeLeaves = $unpaidLeaves->get($employee->id, collect());
                $effectiveDate = $registrationMap->get($employee->id);

                if (!$effectiveDate) {
                    $currentStep += count($dates);
                    continue;
                }

                $employeeWorkingHours = $attendanceWorkingHours->get($employee->id, collect());

                foreach ($dates as $date) {
                    $attendanceAt = $date->format('Y-m-d');
                    $isRegistered = $effectiveDate <= $attendanceAt;

                    if (!$isRegistered) {
                        $currentStep++;
                        continue;
                    }

                    $workingHourRecord = $employeeWorkingHours->get($attendanceAt);
                    if (!$workingHourRecord) {
                        throw new \Exception("Data Jam Kerja untuk {$employee->full_name} pada tanggal {$attendanceAt} tidak ditemukan");
                    }

                    $isHoliday = in_array($attendanceAt, $holidays);
                    $leaves = $this->categorizeLeaves($employeeLeaves, $attendanceAt);
                    
                    $isNightShift = false;
                    if ($workingHourRecord->working_hour->clock_in && $workingHourRecord->working_hour->clock_out) {
                        $isNightShift = $workingHourRecord->working_hour->clock_in > $workingHourRecord->working_hour->clock_out;
                    }

                    $attendance = $existingAttendances->get($workingHourRecord->id);

                    if ($attendance) {
                        $this->updateExistingAttendance(
                            $attendance,
                            $attendanceAt,
                            $workingHourRecord,
                            $zktecoAttendances,
                            $attendanceUsers,
                            $leaves,
                            $isHoliday,
                            $isRegistered,
                            $isNightShift
                        );
                    } else {
                        $attendance = $this->createNewAttendance(
                            $attendanceAt,
                            $workingHourRecord,
                            $zktecoAttendances,
                            $attendanceUsers,
                            $leaves,
                            $isHoliday,
                            $isRegistered,
                            $isNightShift
                        );
                    }

                    $attendance->save();
                    
                    $currentStep++;
                    $progress = 20 + round(($currentStep / $totalSteps) * 80);
                    if ($currentStep % 50 === 0) {
                        $this->updateProgress($progress, "Memproses: {$employee->full_name} ({$attendanceAt})");
                    }
                }
            }

            DB::commit();
            $this->completeTask('Attendance calculation completed.');
        } catch (Throwable $e) {
            DB::rollBack();
            try {
                Log::error("Attendance Calculation Error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            } catch (Throwable $logE) {
                // Ignore log errors so the task failure can be recorded
            }
            $this->failTask($e->getMessage());
            throw $e;
        }
    }

    private function preCalculateRegistrationMap(Collection $employees, string $startDate, string $endDate): Collection
    {
        $map = collect();
        $uids = $employees->flatMap->attendance_users->pluck('uid')->unique()->toArray();
        $earliestLogs = $this->attendanceRepository->getEarliestAttendances($uids);

        foreach ($employees as $employee) {
            $effectiveDate = $employee->join_date;

            if (!$effectiveDate) {
                $earliestLog = null;
                foreach ($employee->attendance_users as $user) {
                    $log = $earliestLogs->get($user->uid);
                    if ($log) {
                        $date = $log->min_created ?? $log->min_updated;
                        if (!$earliestLog || ($date && $date < $earliestLog)) {
                            $earliestLog = $date;
                        }
                    }
                }
                
                $regDate = $employee->attendance_users->first()?->created_at;
                if ($earliestLog && $regDate) {
                    $attDate = Carbon::parse($earliestLog);
                    $sysRegDate = Carbon::parse($regDate);
                    // Legacy logic: if logs are > 60 days before reg, assume reused UID
                    $effectiveDate = ($attDate->lt($sysRegDate) && $attDate->diffInDays($sysRegDate) > 60) 
                        ? $regDate : min($earliestLog, $regDate);
                } else {
                    $effectiveDate = $earliestLog ?: $regDate;
                }
            }

            if (!$effectiveDate) {
                $map->put($employee->id, null);
                continue;
            }

            $map->put($employee->id, Carbon::parse($effectiveDate)->format('Y-m-d'));
        }

        return $map;
    }

    private function categorizeLeaves(Collection $employeeLeaves, string $date): array
    {
        $activeLeaves = $employeeLeaves->filter(fn($l) => $l->start_date <= $date && $l->end_date >= $date);
        
        return [
            'paid' => $activeLeaves->firstWhere('unpaid_leave_type_id', AttendanceSetting::getValue('attendance_leave_type_paid_id', 9)),
            'sick' => $activeLeaves->firstWhere('unpaid_leave_type_id', AttendanceSetting::getValue('attendance_leave_type_sick_id', 10)),
            'out_of_office' => $activeLeaves->firstWhere('unpaid_leave_type_id', AttendanceSetting::getValue('attendance_leave_type_ooo_id', 21)),
            'any' => $activeLeaves->first()
        ];
    }

    private function updateExistingAttendance($attendance, $date, $workingHour, $zkLogs, $users, $leaves, $isHoliday, $isRegistered, $isNightShift): void
    {
        $isStatusChangeAllowed = !$attendance->is_manual_override && in_array($attendance->attendance_status_id, [
            AttendanceSetting::getValue('attendance_status_alpha_id', 2),
            AttendanceSetting::getValue('attendance_status_tap_id', 8),
            AttendanceSetting::getValue('attendance_status_late_id', 9),
            AttendanceSetting::getValue('attendance_status_present_id', 12),
            AttendanceSetting::getValue('attendance_status_late_tap_id', 13),
        ]);

        $scans = $this->processAllScans($attendance, $zkLogs, $users, $date, $isNightShift);

        if ($scans->isNotEmpty()) {
            $first = $scans->first();
            $lastCandidate = $scans->last();
            
            $firstDT = Carbon::parse($first->attendance_at . ' ' . $first->timestamp);
            $lastDT = Carbon::parse($lastCandidate->attendance_at . ' ' . $lastCandidate->timestamp);
            
            $minGap = AttendanceSetting::getValue('attendance_calc_min_gap', 30);
            $last = ($firstDT->diffInMinutes($lastDT) >= $minGap) ? $lastCandidate : null;

            $this->processIncomingScan($attendance, $first, $workingHour, $isStatusChangeAllowed, $isNightShift);
            if ($last) {
                $this->processOutgoingScan($attendance, $last, $workingHour, $isStatusChangeAllowed, $isNightShift);
            }
        } else if ($isStatusChangeAllowed) {
            $this->setStatusByLeave($attendance, $leaves, $isHoliday, $isRegistered);
        }

        // Handle TAP status
        if (!$attendance->outgoing_scan && $attendance->incoming_scan && $isStatusChangeAllowed) {
            $checkDate = $isNightShift ? Carbon::parse($date)->addDay()->format('Y-m-d') : $date;
            $now = Carbon::now();
            if ($now->format('Y-m-d') > $checkDate || ($now->format('Y-m-d') == $checkDate && $now->format('H:i:s') > '12:00:00')) {
                if ($attendance->attendance_status_id != AttendanceSetting::getValue('attendance_status_off_id', 11)) {
                    $attendance->attendance_status_id = $attendance->late_time 
                        ? AttendanceSetting::getValue('attendance_status_late_tap_id', 13) 
                        : AttendanceSetting::getValue('attendance_status_tap_id', 8);
                }
            }
        }

        // Final status correction
        if ($isStatusChangeAllowed && $attendance->incoming_scan && $attendance->outgoing_scan) {
            if ($leaves['any'] || $isHoliday) {
                $this->setStatusByLeave($attendance, $leaves, $isHoliday, $isRegistered);
            } else {
                $attendance->attendance_status_id = $attendance->late_time 
                    ? AttendanceSetting::getValue('attendance_status_late_id', 9) 
                    : AttendanceSetting::getValue('attendance_status_present_id', 12);
            }
        }
    }

    private function createNewAttendance($date, $workingHour, $zkLogs, $users, $leaves, $isHoliday, $isRegistered, $isNightShift): Attendance
    {
        $attendance = new Attendance();
        $attendance->attendance_working_hour_id = $workingHour->id;

        $scans = $this->processAllScans($attendance, $zkLogs, $users, $date, $isNightShift);

        if ($scans->isEmpty()) {
            $this->setStatusByLeave($attendance, $leaves, $isHoliday, $isRegistered);
        } else {
            $first = $scans->first();
            $lastCandidate = $scans->last();
            $minGap = AttendanceSetting::getValue('attendance_calc_min_gap', 30);
            
            $firstDT = Carbon::parse($first->attendance_at . ' ' . $first->timestamp);
            $lastDT = Carbon::parse($lastCandidate->attendance_at . ' ' . $lastCandidate->timestamp);
            $last = ($firstDT->diffInMinutes($lastDT) >= $minGap) ? $lastCandidate : null;

            $this->processIncomingScan($attendance, $first, $workingHour, true, $isNightShift);
            if ($last) {
                $this->processOutgoingScan($attendance, $last, $workingHour, true, $isNightShift);
            }
        }

        return $attendance;
    }

    private function processAllScans($attendance, $zkLogs, $users, $date, $isNightShift): Collection
    {
        $all = collect();
        $searchDates = [$date];
        if ($isNightShift) {
            $searchDates[] = Carbon::parse($date)->addDay()->format('Y-m-d');
        }

        foreach ($users as $user) {
            $uidData = $zkLogs->get($user->uid, collect());
            foreach ($searchDates as $sd) {
                $dateScans = $uidData->get($sd, collect());
                if ($user->zkteco_machine_id) {
                    $dateScans = $dateScans->filter(fn($s) => $s->zkteco_machine_id == $user->zkteco_machine_id);
                }
                $all = $all->merge($dateScans);
            }
        }

        // Merge mobile scans from the attendance record if they exist
        if ($attendance && $attendance->mobile_scans) {
            $mobile = is_array($attendance->mobile_scans) ? $attendance->mobile_scans : json_decode($attendance->mobile_scans, true);
            if (is_array($mobile)) {
                foreach ($mobile as $ms) {
                    $all->push((object)[
                        'timestamp' => $ms['time'],
                        'attendance_at' => $date,
                        'is_mobile' => true,
                        'photo' => $ms['photo'] ?? null,
                        'latitude' => $ms['latitude'] ?? null,
                        'longitude' => $ms['longitude'] ?? null,
                        'location_id' => $ms['location_id'] ?? null,
                    ]);
                }
            }
        }

        if ($isNightShift) {
            $midpoint = AttendanceSetting::getValue('attendance_calc_night_midpoint', '12:00:00');
            $endBuffer = AttendanceSetting::getValue('attendance_calc_night_end_buffer', '15:00:00');
            $all = $all->filter(function($s) use ($date, $midpoint, $endBuffer) {
                return $s->attendance_at == $date ? $s->timestamp >= $midpoint : $s->timestamp < $endBuffer;
            });
        } else {
            $all = $all->filter(fn($s) => $s->attendance_at == $date);
        }

        $sorted = $all->sortBy(fn($s) => $s->attendance_at . ' ' . $s->timestamp)->values();
        
        if ($sorted->isNotEmpty()) {
            $attendance->all_scans = $sorted->map(function($s) {
                $isMobile = isset($s->is_mobile) && $s->is_mobile;
                return [
                    'time' => $s->timestamp,
                    'machine_name' => $isMobile ? 'Mobile App' : ($s->zkteco_machine?->name ?? 'Unknown Machine'),
                    'is_mobile' => $isMobile,
                    'location_id' => $isMobile ? ($s->location_id ?? null) : ($s->zkteco_machine?->work_location_id == 1 ? 9 : 10),
                ];
            })->toArray();
        }

        return $sorted;
    }

    private function processIncomingScan($attendance, $scan, $workingHour, $allowed, $isNightShift): void
    {
        $newTime = $scan->timestamp;
        $isEarlier = is_null($attendance->incoming_scan);

        if (!$isEarlier) {
            $curr = $attendance->incoming_scan;
            if ($newTime < $curr) $isEarlier = true;
            
            if ($isNightShift) {
                $mid = AttendanceSetting::getValue('attendance_calc_night_midpoint', '12:00:00');
                if ($newTime >= $mid && $curr < $mid) $isEarlier = true;
                if ($newTime < $mid && $curr >= $mid) $isEarlier = false;
            }
        }

        if ($isEarlier || $attendance->incoming_scan === $newTime) {
            $attendance->incoming_scan = $newTime;
            if ($isEarlier || is_null($attendance->incoming_location_id)) {
                if (isset($scan->is_mobile)) {
                    $attendance->incoming_location_id = $scan->location_id;
                    $attendance->incoming_photo = $scan->photo;
                    $attendance->incoming_latitude = $scan->latitude;
                    $attendance->incoming_longitude = $scan->longitude;
                } else {
                    $attendance->incoming_location_id = $scan->zkteco_machine?->work_location_id == 1 ? 9 : 10;
                    $attendance->incoming_machine_id = $scan->zkteco_machine_id;
                }
            }

            $t = Carbon::parse($attendance->attendance_working_hour->attendance_at . ' ' . $newTime);
            if ($isNightShift && $newTime < AttendanceSetting::getValue('attendance_calc_night_midpoint', '12:00:00')) {
                $t->addDay();
            }

            $cin = Carbon::parse($attendance->attendance_working_hour->attendance_at . ' ' . $workingHour->working_hour->clock_in);
            $attendance->late_time = $t->gt($cin) ? $t->diff($cin)->format('%H:%I:00') : null;

            if ($allowed && $attendance->attendance_status_id != AttendanceSetting::getValue('attendance_status_off_id', 11)) {
                $attendance->attendance_status_id = $attendance->late_time 
                    ? AttendanceSetting::getValue('attendance_status_late_id', 9) 
                    : AttendanceSetting::getValue('attendance_status_present_id', 12);
            }
        }
    }

    private function processOutgoingScan($attendance, $scan, $workingHour, $allowed, $isNightShift): void
    {
        $newTime = $scan->timestamp;
        $isLater = is_null($attendance->outgoing_scan);

        if (!$isLater) {
            $curr = $attendance->outgoing_scan;
            if ($newTime > $curr) $isLater = true;
            
            if ($isNightShift) {
                $mid = AttendanceSetting::getValue('attendance_calc_night_midpoint', '12:00:00');
                if ($newTime < $mid && $curr >= $mid) $isLater = true;
                if ($newTime >= $mid && $curr < $mid) $isLater = false;
            }
        }

        if ($isLater || $attendance->outgoing_scan === $newTime) {
            $attendance->outgoing_scan = $newTime;
            if ($isLater || is_null($attendance->outgoing_location_id)) {
                if (isset($scan->is_mobile)) {
                    $attendance->outgoing_location_id = $scan->location_id;
                    $attendance->outgoing_photo = $scan->photo;
                    $attendance->outgoing_latitude = $scan->latitude;
                    $attendance->outgoing_longitude = $scan->longitude;
                } else {
                    $attendance->outgoing_location_id = $scan->zkteco_machine?->work_location_id == 1 ? 9 : 10;
                    $attendance->outgoing_machine_id = $scan->zkteco_machine_id;
                }
            }

            $t = Carbon::parse($attendance->attendance_working_hour->attendance_at . ' ' . $newTime);
            if ($isNightShift && $newTime < AttendanceSetting::getValue('attendance_calc_night_midpoint', '12:00:00')) {
                $t->addDay();
            }

            $cout = Carbon::parse($attendance->attendance_working_hour->attendance_at . ' ' . $workingHour->working_hour->clock_out);
            if ($isNightShift) {
                $cout->addDay();
            }
            
            $attendance->early_time = $cout->gt($t) ? $cout->diff($t)->format('%H:%I:00') : null;

            if ($allowed && $attendance->attendance_status_id != AttendanceSetting::getValue('attendance_status_off_id', 11)) {
                $attendance->attendance_status_id = $attendance->late_time 
                    ? AttendanceSetting::getValue('attendance_status_late_id', 9) 
                    : AttendanceSetting::getValue('attendance_status_present_id', 12);
            }
        }
    }

    private function setStatusByLeave($attendance, $leaves, $isHoliday, $isRegistered): void
    {
        if ($attendance->attendance_status_id == AttendanceSetting::getValue('attendance_status_off_id', 11)) return;
        if (!$isRegistered) {
            $attendance->attendance_status_id = null;
            return;
        }

        if ($isHoliday) {
            $attendance->attendance_status_id = AttendanceSetting::getValue('attendance_status_holiday_id', 10);
        } elseif ($leaves['paid']) {
            $attendance->attendance_status_id = AttendanceSetting::getValue('attendance_status_leave_id', 5);
        } elseif ($leaves['sick']) {
            $attendance->attendance_status_id = AttendanceSetting::getValue('attendance_status_sick_id', 3);
        } elseif ($leaves['out_of_office']) {
            $attendance->attendance_status_id = AttendanceSetting::getValue('attendance_status_ooo_id', 7);
        } elseif ($leaves['any']) {
            $attendance->attendance_status_id = AttendanceSetting::getValue('attendance_status_permission_id', 4);
        } else {
            $attendance->attendance_status_id = AttendanceSetting::getValue('attendance_status_alpha_id', 2);
        }
    }
}
