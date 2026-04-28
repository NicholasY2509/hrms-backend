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
        $query = UnpaidLeave::with(['unpaid_leave_type', 'employee', 'unpaid_leave_approvals.employee'])
            ->orderByDesc('start_date')
            ->orderByDesc('id');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['unpaid_leave_type_id'])) {
            $query->where('unpaid_leave_type_id', $filters['unpaid_leave_type_id']);
        }

        return $query->paginate($perPage);
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
        return UnpaidLeave::with(['unpaid_leave_type', 'employee', 'unpaid_leave_approvals.employee'])
            ->find($id);
    }
}
