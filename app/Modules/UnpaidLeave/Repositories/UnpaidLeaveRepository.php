<?php

namespace App\Modules\UnpaidLeave\Repositories;

use App\Modules\UnpaidLeave\Models\Holiday;
use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use Illuminate\Database\Eloquent\Collection;

class UnpaidLeaveRepository
{
    /**
     * Get paginated unpaid leaves with optional filters.
     */
    public function paginate(array $filters = [], int $perPage = 15)
    {
        return UnpaidLeave::with(['unpaid_leave_type', 'employee.department', 'employee.position', 'approvalRequest.steps.actor', 'approvalRequest.steps.approver', 'approvalRequest.steps.group.employees'])
            ->filter($filters)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * Get all unpaid leaves for a specific employee.
     *
     * @param int $employeeId
     * @return Collection
     */
    public function getByEmployeeId(int $employeeId, int $perPage = 15)
    {
        return $this->paginate(['employee_id' => $employeeId], $perPage);
    }

    /**
     * Create a new unpaid leave record.
     *
     * @param array $data
     * @return UnpaidLeave
     */
    public function create(array $data): UnpaidLeave
    {
        return UnpaidLeave::create($data);
    }

    /**
     * Find an unpaid leave record by ID.
     *
     * @param int $id
     * @return UnpaidLeave|null
     */
    public function find(int $id): ?UnpaidLeave
    {
        return UnpaidLeave::with([
            'unpaid_leave_type', 
            'employee.department', 
            'employee.position',
            'employee.work_location',
            'employee.work_employee_status',
            'employee.user_employee.user',
            'employee.supervisor.employee',
            'approvalRequest.steps.actor', 
            'approvalRequest.steps.approver', 
            'approvalRequest.steps.group.employees'
        ])->find($id);
    }

    /**
     * Update an unpaid leave record.
     */
    public function update(int $id, array $data): bool
    {
        $leave = UnpaidLeave::find($id);
        if (!$leave) {
            return false;
        }
        return $leave->update($data);
    }

    /**
     * Get unpaid leaves for calendar view within a date range.
     */
    public function getCalendarData(array $filters)
    {
        return UnpaidLeave::with(['employee', 'unpaid_leave_type', 'approvalRequest'])
            ->where(function ($query) use ($filters) {
                $query->whereBetween('start_date', [$filters['start_date'], $filters['end_date']])
                    ->orWhereBetween('end_date', [$filters['start_date'], $filters['end_date']])
                    ->orWhere(function ($q) use ($filters) {
                        $q->where('start_date', '<=', $filters['start_date'])
                            ->where('end_date', '>=', $filters['end_date']);
                    });
            })
            ->when($filters['employee_id'] ?? null, fn($q, $id) => $q->where('employee_id', $id))
            ->when($filters['department_id'] ?? null, function ($q, $deptId) {
                $q->whereHas('employee', fn($sq) => $sq->where('department_id', $deptId));
            })
            ->when($filters['unpaid_leave_type_id'] ?? null, fn($q, $typeId) => $q->where('unpaid_leave_type_id', $typeId))
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->whereHas('approvalRequest', fn($sq) => $sq->where('status', $status));
            })
            ->get();
    }
}
