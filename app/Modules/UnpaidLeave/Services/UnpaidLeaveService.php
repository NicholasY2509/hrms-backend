<?php

namespace App\Modules\UnpaidLeave\Services;

use App\Exceptions\ApplicationException;
use App\Modules\UnpaidLeave\Models\Holiday;
use App\Modules\UnpaidLeave\Models\UnpaidLeaveType;
use App\Modules\UnpaidLeave\Repositories\UnpaidLeaveRepository;
use Illuminate\Support\Facades\DB;
use App\Services\StorageService;
use Illuminate\Support\Carbon;
use Illuminate\Http\UploadedFile;
use App\Modules\Leave\Services\AnnualLeaveService;
use App\Modules\Leave\Repositories\AnnualLeaveRepository;
use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use App\Modules\UnpaidLeave\Services\HolidayService;

class UnpaidLeaveService
{
    public function __construct(
        protected UnpaidLeaveRepository $repository,
        protected AnnualLeaveService $annualLeaveService,
        protected AnnualLeaveRepository $annualLeaveRepository,
        protected UnpaidLeaveApprovalService $approvalService,
        protected HolidayService $holidayService
    ) {}

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
     * Get paginated unpaid leaves for management.
     */
    public function getPaginatedLeaves(array $filters = [], int $perPage = 15)
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * Get unpaid leave detail by ID.
     */
    public function getLeaveDetail(int $id)
    {
        return $this->repository->find($id);
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
            $type = UnpaidLeaveType::findOrFail($data['unpaid_leave_type_id']);
            $totalDays = $this->calculateTotalDaysExcludingHolidays($data['start_date'], $data['end_date']);

            if ($type->limit && $totalDays > $type->limit) {
                throw new ApplicationException('Jumlah Hari untuk Tipe Pengajuan Izin melebihi batas!', 400);
            }

            if ($attachment) {
                $data['attachment'] = StorageService::store($attachment, 'unpaid_leaves');
            }

            $data['total'] = $totalDays;
            $data['confirmed_at'] = Carbon::now()->toDateString();

            $leave = $this->repository->create($data);

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
        $holidaysCount = $this->holidayService->getHolidaysInRange($startDate, $endDate)
            ->filter(fn($h) => Carbon::parse($h->date)->dayOfWeek !== Carbon::SUNDAY)
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
        return \App\Modules\UnpaidLeave\Models\UnpaidLeave::with(['unpaid_leave_type', 'approvalRequest'])
            ->where('employee_id', $employeeId)
            ->whereNull('settled_at')
            ->whereHas('approvalRequest', function ($query) {
                $query->where('status', 'pending');
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
        return $this->holidayService->getAllHolidays()
            ->where('date', '>=', Carbon::now()->toDateString())
            ->sortBy('date')
            ->take($limit);
    }

    /**
     * Get calendar data (leaves and holidays).
     */
    public function getCalendarData(array $filters): array
    {
        return [
            'leaves' => $this->repository->getCalendarData($filters),
            'holidays' => $this->holidayService->getHolidaysInRange($filters['start_date'], $filters['end_date']),
        ];
    }

    /**
     * Settle an unpaid leave request.
     * Includes annual leave deduction logic if required by the leave type.
     *
     * @param UnpaidLeave $leave
     * @return UnpaidLeave
     */
    public function settle(UnpaidLeave $leave): UnpaidLeave
    {
        if ($leave->settled_at) {
            return $leave;
        }

        return DB::transaction(function () use ($leave) {
            $now = Carbon::now();

            if ($leave->unpaid_leave_type?->is_annual_leave_deduction) {
                $employee = $leave->employee;
                // Check for existing automated deductions (penalties) in the period to avoid double-deducting
                $existingDeductionsCount = $this->annualLeaveRepository->countAutomatedDeductionsInRange(
                    $employee->id,
                    $leave->start_date,
                    $leave->end_date
                );

                $daysToDeduct = max(0, (float) $leave->total - $existingDeductionsCount);

                if ($daysToDeduct > 0) {
                    $this->annualLeaveService->deduct(
                        $employee,
                        $daysToDeduct,
                        ($leave->note ?? 'Unpaid Leave') . " ({$leave->start_date} to {$leave->end_date})",
                        Carbon::parse($leave->start_date)
                    );
                }

                $leave->cutted_at = $leave->start_date;
            }

            $leave->settled_at = $now->format('Y-m-d');
            $leave->save();

            return $leave->fresh();
        });
    }
}
