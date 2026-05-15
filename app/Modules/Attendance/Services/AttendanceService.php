<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Jobs\ImportAttendanceWorkingHourJob;
use App\Modules\Attendance\Models\AttendanceWorkingHour;
use App\Modules\Attendance\Repositories\AttendanceRepository;
use App\Modules\System\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\Attendance\Models\AttendanceMobileScan;
use Illuminate\Support\Facades\Storage;
use App\Services\StorageService;
use Carbon\Carbon;

class AttendanceService
{
    protected AttendanceRepository $attendanceRepository;

    public function __construct(
        AttendanceRepository $attendanceRepository
    ) {
        $this->attendanceRepository = $attendanceRepository;
    }

    /**
     * Get paginated attendances for management.
     */
    public function getPaginated(array $filters, int $perPage = 15)
    {
        return $this->attendanceRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get all attendance statuses.
     */
    public function getAllStatuses(): Collection
    {
        return $this->attendanceRepository->getAllStatuses();
    }

    /**
     * Get paginated mobile scans with filters.
     */
    public function getMobileScansPaginated(array $filters, int $perPage = 15)
    {
        return AttendanceMobileScan::with(['employee', 'location', 'attendance.attendance_working_hour'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get paginated attendance working hours (schedules) with filters.
     */
    public function getWorkingHoursPaginated(array $filters, int $perPage = 15)
    {
        return AttendanceWorkingHour::with(['employee', 'working_hour', 'attendance'])
            ->filter($filters)
            ->orderBy('attendance_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Start the asynchronous import process for attendance working hours.
     */
    public function importWorkingHours(array $data, int $userId): Task
    {
        $file = $data['file'];
        $path = StorageService::store($file, 'imports/attendance-working-hours');
        $absolutePath = Storage::path($path);

        $task = Task::create([
            'user_id' => $userId,
            'type' => 'attendance_working_hour_import',
            'status' => 'pending',
            'message' => 'Menunggu antrian...',
        ]);

        ImportAttendanceWorkingHourJob::dispatch(
            $task->id,
            $absolutePath,
            $data['month'],
            $data['upload_type'],
            $data['day_type'] ?? null
        );

        return $task;
    }

    /**
     * Update an attendance working hour (schedule).
     */
    public function updateWorkingHour(AttendanceWorkingHour $attendanceWorkingHour, array $data): AttendanceWorkingHour
    {
        if (isset($data['attendance_at'])) {
            $data['attendance_at'] = Carbon::parse($data['attendance_at'])->format('Y-m-d');
        }

        $attendanceWorkingHour->update($data);
        return $attendanceWorkingHour->load(['employee', 'working_hour', 'attendance']);
    }
}
