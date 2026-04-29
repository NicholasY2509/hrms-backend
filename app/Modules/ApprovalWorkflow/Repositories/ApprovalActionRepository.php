<?php

namespace App\Modules\ApprovalWorkflow\Repositories;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestStep;
use App\Modules\Career\Models\Career;
use App\Modules\Disciplinary\Models\WarningLetter;
use App\Modules\Employee\Models\CertificateOfEmployment;
use App\Modules\Leave\Models\PaidLeaveReversal;
use App\Modules\Overtime\Models\Overtime;
use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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

        return ApprovalRequest::query()
            ->where('status', 'pending')
            ->whereHas('approvable')
            ->whereHas('steps', function ($query) use ($employeeId, $groupIds) {
                $query->where('status', 'pending')
                    ->where(function ($q) use ($employeeId, $groupIds) {
                        $q->where(function ($inner) use ($employeeId) {
                            $inner->whereIn('approver_type', ['user', 'supervisor', 'dept_head'])
                                ->where('approver_id', $employeeId);
                        })
                        ->orWhere(function ($inner) use ($groupIds) {
                            $inner->where('approver_type', 'group')
                                ->whereIn('approver_id', $groupIds);
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
