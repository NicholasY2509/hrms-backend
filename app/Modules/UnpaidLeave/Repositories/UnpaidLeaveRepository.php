<?php

namespace App\Modules\UnpaidLeave\Repositories;

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
        return UnpaidLeave::with(['unpaid_leave_type', 'employee', 'approvalRequest.steps.actor', 'approvalRequest.steps.approver', 'approvalRequest.steps.group.employees'])
            ->find($id);
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
}
