<?php

namespace App\Modules\UnpaidLeave\Services;

use App\Modules\UnpaidLeave\Models\Holiday;
use App\Modules\UnpaidLeave\Repositories\UnpaidLeaveRepository;
use App\Modules\UnpaidLeave\Services\UnpaidLeaveApprovalService;
use Illuminate\Support\Facades\DB;
use App\Services\StorageService;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

class UnpaidLeaveService
{
    protected UnpaidLeaveRepository $repository;
    protected UnpaidLeaveApprovalService $approvalService;

    public function __construct(
        UnpaidLeaveRepository $repository,
        UnpaidLeaveApprovalService $approvalService
    ) {
        $this->repository = $repository;
        $this->approvalService = $approvalService;
    }

    /**
     * Get list of unpaid leaves for an employee.
     *
     * @param int $employeeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserUnpaidLeaves(int $employeeId, int $perPage = 15)
    {
        return $this->repository->getByEmployeeId($employeeId, $perPage);
    }

    /**
     * Create a new unpaid leave request.
     *
     * @param array $data
     * @param UploadedFile|null $attachment
     * @return \App\Modules\UnpaidLeave\Models\UnpaidLeave
     */
    public function createUnpaidLeave(array $data, ?UploadedFile $attachment = null)
    {
        return DB::transaction(function () use ($data, $attachment) {
            $totalDays = $this->calculateTotalDaysExcludingHolidays($data['start_date'], $data['end_date']);

            if ($attachment) {
                $data['attachment'] = StorageService::store($attachment, 'unpaid_leaves');
            }

            $data['total'] = $totalDays;
            $data['confirmed_at'] = Carbon::now()->toDateString();

            $leave = $this->repository->create($data);

            // Generate automated approvals matching legacy logic
            $this->approvalService->generateInitialApprovals($leave);

            return $leave;
        });
    }

    /**
     * Calculate total days between start and end date excluding Sundays and Holidays.
     *
     * @param string $startDate
     * @param string $endDate
     * @return int
     */
    public function calculateTotalDaysExcludingHolidays(string $startDate, string $endDate): int
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        if ($start->gt($end)) {
            return 0;
        }

        $count = 0;
        $tempDate = $start->copy();

        while ($tempDate->lte($end)) {
            if (!$tempDate->isSunday()) {
                $count++;
            }
            $tempDate->addDay();
        }

        // Subtract holidays that are not Sundays
        $holidaysCount = Holiday::betweenDates($startDate, $endDate)
            ->whereRaw('DAYOFWEEK(date) != 1')
            ->count();

        return max(0, $count - $holidaysCount);
    }

    /**
     * Find an unpaid leave by ID and check if it belongs to the employee.
     *
     * @param int $id
     * @param int $employeeId
     * @return \App\Modules\UnpaidLeave\Models\UnpaidLeave|null
     */
    public function getUnpaidLeaveDetail(int $id, int $employeeId)
    {
        $unpaidLeave = $this->repository->find($id);

        if ($unpaidLeave && $unpaidLeave->employee_id === $employeeId) {
            return $unpaidLeave;
        }

        return null;
    }

    /**
     * Get pending unpaid leave requests for dashboard.
     *
     * @param int $employeeId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingRequests(int $employeeId, int $limit = 5)
    {
        return \App\Modules\UnpaidLeave\Models\UnpaidLeave::with(['unpaid_leave_type', 'unpaid_leave_approvals.employee'])
            ->where('employee_id', $employeeId)
            ->whereNull('settled_at')
            ->whereDoesntHave('unpaid_leave_approvals', function ($query) {
                $query->where('status', 'Rejected');
            })
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get upcoming company holidays.
     */
    public function getUpcomingHolidays(int $limit = 2)
    {
        return Holiday::where('date', '>=', Carbon::now()->toDateString())
            ->orderBy('date', 'asc')
            ->limit($limit)
            ->get();
    }
}
