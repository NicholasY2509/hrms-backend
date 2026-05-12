<?php

namespace App\Modules\ApprovalWorkflow\Repositories;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestStep;
use App\Modules\Career\Models\Career;
use App\Modules\Disciplinary\Models\WarningLetter;
use App\Modules\Employee\Models\CertificateOfEmployment;
use App\Modules\Employee\Models\Employee;
use App\Modules\Leave\Models\PaidLeaveReversal;
use App\Modules\Overtime\Models\Overtime;
use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalActionRepository
{
    /**
     * Get pending approvals for a specific employee.
     */
    public function getPendingForEmployee(int $employeeId, int $perPage = 15, ?string $type = null): LengthAwarePaginator
    {
        $groupIds = DB::table('approval_group_employees')
            ->where('employee_id', $employeeId)
            ->pluck('approval_group_id')
            ->toArray();

        $employee = Employee::find($employeeId);
        $workPositionId = $employee->work_position_id ?? null;

        return ApprovalRequest::query()
            ->where('status', 'pending')
            ->whereHas('approvable')
            ->whereHas('steps', function ($query) use ($employeeId, $groupIds, $workPositionId) {
                $query->where('status', 'pending')
                    ->whereColumn('sequence', 'approval_requests.current_step_sequence')
                    ->where(function ($q) use ($employeeId, $groupIds, $workPositionId) {
                        $q->where(function ($inner) use ($employeeId) {
                            $inner->whereIn('approver_type', ['user', 'employee', 'supervisor', 'dept_head'])
                                ->where('approver_id', $employeeId);
                        })
                        ->orWhere(function ($inner) use ($groupIds) {
                            $inner->where('approver_type', 'group')
                                ->whereIn('approver_id', $groupIds);
                        })
                        ->when($workPositionId, function ($inner) use ($workPositionId) {
                            $inner->orWhere(function ($q) use ($workPositionId) {
                                $q->where('approver_type', 'work_position')
                                    ->where('approver_id', $workPositionId);
                            });
                        });
                    });
            })
            ->when($type, function ($query) use ($type) {
                $query->where('approvable_type', 'like', "%{$type}%");
            })
            ->with([
                'approvable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Overtime::class => ['employee'],
                        UnpaidLeave::class => ['unpaid_leave_type', 'employee'],
                        Career::class => ['employee'],
                        WarningLetter::class => ['employee'],
                        CertificateOfEmployment::class => ['employee'],
                        PaidLeaveReversal::class => ['employee'],
                    ]);
                },
                'steps.actor', 
                'rule.scheme'
            ])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get ALL pending approvals (for IT/Admin).
     */
    public function getAllPending(int $perPage = 15, ?string $type = null): LengthAwarePaginator
    {
        return ApprovalRequest::query()
            ->where('status', 'pending')
            ->whereHas('approvable')
            ->when($type, function ($query) use ($type) {
                $query->where('approvable_type', 'like', "%{$type}%");
            })
            ->with([
                'approvable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Overtime::class => ['employee'],
                        UnpaidLeave::class => ['unpaid_leave_type', 'employee'],
                        Career::class => ['employee'],
                        WarningLetter::class => ['employee'],
                        CertificateOfEmployment::class => ['employee'],
                        PaidLeaveReversal::class => ['employee'],
                    ]);
                },
                'steps.actor', 
                'rule.scheme'
            ])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Helper to check if employee is authorized for a specific step.
     */
    protected function isAuthorized(ApprovalRequestStep $step, int $employeeId, array $groupIds, ?int $workPositionId = null): bool
    {
        if (in_array($step->approver_type, ['user', 'employee', 'supervisor', 'dept_head'])) {
            return $step->approver_id == $employeeId;
        }

        if ($step->approver_type === 'group') {
            return in_array($step->approver_id, $groupIds);
        }

        if ($step->approver_type === 'work_position') {
            return $step->approver_id == $workPositionId;
        }

        return false;
    }

    /**
     * Helper to apply authorizer filter to a query.
     */
    protected function applyAuthorizerFilter($query, int $employeeId, array $groupIds, ?int $workPositionId = null)
    {
        $query->where(function ($q) use ($employeeId) {
            $q->whereIn('approver_type', ['user', 'employee', 'supervisor', 'dept_head'])
              ->where('approver_id', $employeeId);
        })
        ->orWhere(function ($q) use ($groupIds) {
            $q->where('approver_type', 'group')
              ->whereIn('approver_id', $groupIds);
        })
        ->when($workPositionId, function ($q) use ($workPositionId) {
            $q->orWhere(function ($inner) use ($workPositionId) {
                $inner->where('approver_type', 'work_position')
                      ->where('approver_id', $workPositionId);
            });
        });
    }
}
