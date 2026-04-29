<?php

namespace App\Modules\Overtime\Repositories;

use App\Modules\Overtime\Models\Overtime;

class OvertimeRepository
{
    /**
     * Get paginated overtime requests with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 15)
    {
        $query = Overtime::with(['employee', 'overtime_type', 'approvalRequest.steps.actor', 'approvalRequest.steps.approver', 'approvalRequest.steps.group.employees', 'overtime_attachments'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('date', [$filters['start_date'], $filters['end_date']]);
        }

        if (isset($filters['is_settled'])) {
            $filters['is_settled'] ? $query->whereNotNull('settled_at') : $query->whereNull('settled_at');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get pending overtime requests for an employee.
     *
     * @param int $employeeId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingByEmployee(int $employeeId, int $limit = 5)
    {
        return Overtime::with(['overtime_type', 'overtime_approvals.employee'])
            ->where('employee_id', $employeeId)
            ->whereNull('settled_at')
            ->whereDoesntHave('overtime_approvals', function ($query) {
                $query->where('status', 'Rejected');
            })
            ->orderByDesc('date')
            ->limit($limit)
            ->get();
    }

    /**
     * Find an overtime record by ID.
     *
     * @param int $id
     * @return Overtime|null
     */
    public function find(int $id)
    {
        return Overtime::with(['employee', 'overtime_type', 'approvalRequest.steps.actor', 'approvalRequest.steps.approver', 'approvalRequest.steps.group.employees', 'overtime_attachments'])
            ->find($id);
    }
}
