<?php

namespace App\Modules\UnpaidLeave\Repositories;

use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use Illuminate\Database\Eloquent\Collection;

class UnpaidLeaveRepository
{
    /**
     * Get all unpaid leaves for a specific employee.
     *
     * @param int $employeeId
     * @return Collection
     */
    public function getByEmployeeId(int $employeeId, int $perPage = 15)
    {
        return UnpaidLeave::with(['unpaid_leave_type', 'unpaid_leave_approvals.employee'])
            ->where('employee_id', $employeeId)
            ->orderByDesc('start_date')
            ->paginate($perPage);
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
        return UnpaidLeave::with(['unpaid_leave_type', 'employee', 'unpaid_leave_approvals.employee'])
            ->find($id);
    }
}
