<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Repositories\AttendanceRepository;
use App\Modules\System\Services\ReportService;
use Illuminate\Support\Facades\Auth;

class AttendanceExportService
{
    protected $repository;
    protected $reportService;

    public function __construct(
        AttendanceRepository $repository,
        ReportService $reportService
    ) {
        $this->repository = $repository;
        $this->reportService = $reportService;
    }

    /**
     * Start the export process.
     */
    public function export(array $params)
    {
        $payload = [
            'type' => $params['type'],
            'format' => $params['format'],
            'filters' => [
                'start_date' => $params['start_date'] ?? null,
                'end_date' => $params['end_date'] ?? null,
                'employee_id' => $params['employee_id'] ?? null,
                'department_id' => isset($params['department_id']) ? [$params['department_id']] : ($params['department_ids'] ?? []),
                'team_id' => isset($params['team_id']) ? [$params['team_id']] : ($params['team_ids'] ?? []),
                'work_position_id' => isset($params['work_position_id']) ? [$params['work_position_id']] : ($params['work_position_ids'] ?? []),
                'attendance_status_id' => isset($params['attendance_status_id']) ? [$params['attendance_status_id']] : ($params['attendance_status_ids'] ?? []),
            ]
        ];

        return $this->reportService->requestReport($payload);
    }
}
