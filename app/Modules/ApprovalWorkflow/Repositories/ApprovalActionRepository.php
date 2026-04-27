<?php

namespace App\Modules\ApprovalWorkflow\Repositories;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestStep;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ApprovalActionRepository
{
    /**
     * Get pending approvals for a specific employee.
     */
    public function getPendingForEmployee(int $employeeId, int $perPage = 15): LengthAwarePaginator
    {
        // 1. Find groups this employee belongs to
        $groupIds = DB::table('approval_group_employees')
            ->where('employee_id', $employeeId)
            ->pluck('approval_group_id')
            ->toArray();

        // 2. Query requests where the user is an approver (Directly or via Group)
        return ApprovalRequest::query()
            ->where('status', 'pending')
            ->whereHas('steps', function ($query) use ($employeeId, $groupIds) {
                $query->where(function ($q) use ($employeeId, $groupIds) {
                    // Direct User Match
                    $q->where('approver_type', 'user')
                      ->where('approver_id', $employeeId);
                })
                ->orWhere(function ($q) use ($employeeId) {
                    // Role-based matches (Supervisor/DeptHead) resolved to this employee ID
                    $q->whereIn('approver_type', ['supervisor', 'dept_head'])
                      ->where('approver_id', $employeeId);
                })
                ->orWhere(function ($q) use ($groupIds) {
                    // Group Match
                    $q->where('approver_type', 'group')
                      ->whereIn('approver_id', $groupIds);
                });
            })
            ->with(['approvable', 'steps.actor', 'rule.scheme'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a specific step by request and employee.
     */
    public function findStepForAction(int $requestId, int $employeeId): ?ApprovalRequestStep
    {
        $groupIds = DB::table('approval_group_employees')
            ->where('employee_id', $employeeId)
            ->pluck('approval_group_id')
            ->toArray();

        return ApprovalRequestStep::where('approval_request_id', $requestId)
            ->where('status', 'pending')
            ->where(function ($query) use ($employeeId, $groupIds) {
                $query->where(function ($q) use ($employeeId) {
                    $q->whereIn('approver_type', ['user', 'supervisor', 'dept_head'])
                      ->where('approver_id', $employeeId);
                })
                ->orWhere(function ($q) use ($groupIds) {
                    $q->where('approver_type', 'group')
                      ->whereIn('approver_id', $groupIds);
                });
            })
            ->first();
    }
}
