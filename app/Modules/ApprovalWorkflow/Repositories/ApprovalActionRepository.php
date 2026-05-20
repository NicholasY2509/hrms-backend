<?php

namespace App\Modules\ApprovalWorkflow\Repositories;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestStep;
use App\Modules\Career\Models\Career;
use App\Modules\CertificateOfEmployment\Models\CertificateOfEmployment;
use App\Modules\Disciplinary\Models\WarningLetter;
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
    public function getPendingForEmployee(int $employeeId, array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;
        $type = $params['type'] ?? null;
        $search = $params['search'] ?? null;

        $groupIds = $this->getGroupIds($employeeId);
        $workPositionId = $this->getWorkPositionId($employeeId);

        return ApprovalRequest::query()
            ->where('status', 'pending')
            ->whereHas('approvable')
            ->whereHas('steps', function ($query) use ($employeeId, $groupIds, $workPositionId) {
                $query->where('status', 'pending')
                    ->whereColumn('sequence', 'approval_requests.current_step_sequence')
                    ->where(function ($q) use ($employeeId, $groupIds, $workPositionId) {
                        $this->applyAuthorizerFilter($q, $employeeId, $groupIds, $workPositionId);
                    });
            })
            ->when($type, fn($q) => $this->applyTypeFilter($q, $type))
            ->when($search, fn($q) => $this->applySearchFilter($q, $search))
            ->with($this->getEagerLoads())
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get UPCOMING steps (future steps) for a specific employee.
     */
    public function getUpcomingForEmployee(int $employeeId, array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;
        $type = $params['type'] ?? null;
        $search = $params['search'] ?? null;

        $groupIds = $this->getGroupIds($employeeId);
        $workPositionId = $this->getWorkPositionId($employeeId);

        return ApprovalRequest::query()
            ->where('status', 'pending')
            ->whereHas('approvable')
            ->whereHas('steps', function ($query) use ($employeeId, $groupIds, $workPositionId) {
                $query->where('status', 'pending')
                    ->whereColumn('sequence', '>', 'approval_requests.current_step_sequence')
                    ->where(function ($q) use ($employeeId, $groupIds, $workPositionId) {
                        $this->applyAuthorizerFilter($q, $employeeId, $groupIds, $workPositionId);
                    });
            })
            ->when($type, fn($q) => $this->applyTypeFilter($q, $type))
            ->when($search, fn($q) => $this->applySearchFilter($q, $search))
            ->with($this->getEagerLoads())
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get ONGOING requests (any pending request where user is involved).
     */
    public function getOngoingForEmployee(int $employeeId, array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;
        $type = $params['type'] ?? null;
        $search = $params['search'] ?? null;

        $groupIds = $this->getGroupIds($employeeId);
        $workPositionId = $this->getWorkPositionId($employeeId);

        return ApprovalRequest::query()
            ->where('status', 'pending')
            ->whereHas('approvable')
            ->whereHas('steps', function ($query) use ($employeeId, $groupIds, $workPositionId) {
                $query->where(function ($q) use ($employeeId, $groupIds, $workPositionId) {
                    $this->applyAuthorizerFilter($q, $employeeId, $groupIds, $workPositionId);
                });
            })
            ->when($type, fn($q) => $this->applyTypeFilter($q, $type))
            ->when($search, fn($q) => $this->applySearchFilter($q, $search))
            ->with($this->getEagerLoads())
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get HISTORY (finalized requests) for a specific employee.
     */
    public function getHistoryForEmployee(int $employeeId, array $params = []): LengthAwarePaginator
    {
        $perPage = $params['per_page'] ?? 15;
        $type = $params['type'] ?? null;
        $search = $params['search'] ?? null;

        $groupIds = $this->getGroupIds($employeeId);
        $workPositionId = $this->getWorkPositionId($employeeId);

        return ApprovalRequest::query()
            ->whereIn('status', ['approved', 'rejected', 'cancelled'])
            ->whereHas('steps', function ($query) use ($employeeId, $groupIds, $workPositionId) {
                $query->where(function ($q) use ($employeeId, $groupIds, $workPositionId) {
                    $this->applyAuthorizerFilter($q, $employeeId, $groupIds, $workPositionId);
                });
            })
            ->whereHas('approvable')
            ->when($type, fn($q) => $this->applyTypeFilter($q, $type))
            ->when($search, fn($q) => $this->applySearchFilter($q, $search))
            ->with($this->getEagerLoads())
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get summary counts for all categories.
     */
    public function getCountsForEmployee(int $employeeId): array
    {
        $groupIds = $this->getGroupIds($employeeId);
        $workPositionId = $this->getWorkPositionId($employeeId);

        $pending = ApprovalRequest::where('status', 'pending')
            ->whereHas('approvable')
            ->whereHas('steps', function ($q) use ($employeeId, $groupIds, $workPositionId) {
                $q->where('status', 'pending')
                  ->whereColumn('sequence', 'approval_requests.current_step_sequence')
                  ->where(function ($inner) use ($employeeId, $groupIds, $workPositionId) {
                      $this->applyAuthorizerFilter($inner, $employeeId, $groupIds, $workPositionId);
                  });
            })->count();

        $upcoming = ApprovalRequest::where('status', 'pending')
            ->whereHas('approvable')
            ->whereHas('steps', function ($q) use ($employeeId, $groupIds, $workPositionId) {
                $q->where('status', 'pending')
                  ->whereColumn('sequence', '>', 'approval_requests.current_step_sequence')
                  ->where(function ($inner) use ($employeeId, $groupIds, $workPositionId) {
                      $this->applyAuthorizerFilter($inner, $employeeId, $groupIds, $workPositionId);
                  });
            })->count();

        $ongoing = ApprovalRequest::where('status', 'pending')
            ->whereHas('approvable')
            ->whereHas('steps', function ($q) use ($employeeId, $groupIds, $workPositionId) {
                $q->where(function ($inner) use ($employeeId, $groupIds, $workPositionId) {
                    $this->applyAuthorizerFilter($inner, $employeeId, $groupIds, $workPositionId);
                });
            })->count();

        $history = ApprovalRequest::whereIn('status', ['approved', 'rejected', 'cancelled'])
            ->whereHas('approvable')
            ->whereHas('steps', function ($q) use ($employeeId, $groupIds, $workPositionId) {
                $q->where(function ($inner) use ($employeeId, $groupIds, $workPositionId) {
                    $this->applyAuthorizerFilter($inner, $employeeId, $groupIds, $workPositionId);
                });
            })->count();

        return [
            'pending' => $pending,
            'upcoming' => $upcoming,
            'ongoing' => $ongoing,
            'history' => $history,
        ];
    }

    protected function getGroupIds(int $employeeId): array
    {
        return DB::table('approval_group_employees')
            ->where('employee_id', $employeeId)
            ->pluck('approval_group_id')
            ->toArray();
    }

    protected function getWorkPositionId(int $employeeId): ?int
    {
        return Employee::find($employeeId)?->work_position_id;
    }

    protected function applyTypeFilter($query, $type)
    {
        $types = is_array($type) ? $type : explode(',', $type);
        $query->where(function ($q) use ($types) {
            foreach ($types as $t) {
                $q->orWhere('approvable_type', 'like', "%{$t}%");
            }
        });
    }

    protected function applySearchFilter($query, $search)
    {
        $query->where(function ($q) use ($search) {
            $q->where('reference_number', 'like', "%{$search}%")
              ->orWhereHas('approvable', function ($inner) use ($search) {
                  // Basic search on approvable's employee name if exists
                  $inner->whereHas('employee', function ($e) use ($search) {
                      $e->where('full_name', 'like', "%{$search}%");
                  });
              });
        });
    }

    protected function getEagerLoads(): array
    {
        return [
            'approvable' => function (MorphTo $morphTo) {
                $morphTo->morphWith([
                    Overtime::class => ['employee.department', 'employee.position'],
                    UnpaidLeave::class => ['unpaid_leave_type', 'employee.department', 'employee.position'],
                    Career::class => ['employee.department', 'employee.position'],
                    WarningLetter::class => ['employee.department', 'employee.position'],
                    CertificateOfEmployment::class => ['employee.department', 'employee.position'],
                    PaidLeaveReversal::class => ['employee.department', 'employee.position'],
                ]);
            },
            'steps.actor', 
            'rule.scheme'
        ];
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
