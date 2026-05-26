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
        return Overtime::query()
            ->with([
                'employee.department',
                'employee.position',
                'employee.work_location',
                'employee.work_employee_status',
                'employee.user_employee.user',
                'employee.supervisor.employee',
                'overtime_type',
                'approvalRequest.steps.actor',
                'approvalRequest.steps.approver',
                'approvalRequest.steps.group.employees',
                'overtime_attachments'
            ])
            ->filter($filters)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($perPage);
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
        return Overtime::with([
            'employee.department',
            'employee.position',
            'employee.work_location',
            'employee.work_employee_status',
            'employee.user_employee.user',
            'employee.supervisor.employee',
            'overtime_type',
            'approvalRequest.steps.actor',
            'approvalRequest.steps.approver',
            'approvalRequest.steps.group.employees',
            'overtime_attachments'
        ])->find($id);
    }
}
