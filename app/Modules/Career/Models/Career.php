<?php

namespace App\Modules\Career\Models;

use App\Modules\Career\Services\CareerService;
use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Models\EmployeeStatus;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Team;
use App\Modules\Organization\Models\WorkLocation;
use App\Modules\Organization\Models\WorkPosition;
use App\Traits\Approvable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Career extends Model
{
    use SoftDeletes, Approvable;

    protected $table = 'careers';
    protected $guarded = ['id'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function careerType(): BelongsTo
    {
        return $this->belongsTo(CareerType::class);
    }

    public function beforeEmployeeStatus(): BelongsTo
    {
        return $this->belongsTo(EmployeeStatus::class, 'before_employee_status_id');
    }

    public function afterEmployeeStatus(): BelongsTo
    {
        return $this->belongsTo(EmployeeStatus::class, 'after_employee_status_id');
    }

    public function beforeWorkPosition(): BelongsTo
    {
        return $this->belongsTo(WorkPosition::class, 'before_work_position_id');
    }

    public function afterWorkPosition(): BelongsTo
    {
        return $this->belongsTo(WorkPosition::class, 'after_work_position_id');
    }

    public function beforeWorkLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'before_work_location_id');
    }

    public function afterWorkLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'after_work_location_id');
    }

    public function beforeDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'before_department_id');
    }

    public function afterDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'after_department_id');
    }

    public function beforeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'before_team_id');
    }

    public function afterTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'after_team_id');
    }

    /**
     * Get the dynamic status of the career change.
     */
    public function getStatusAttribute()
    {
        if ($this->settled_at) {
            return 'Settled';
        }

        $request = $this->approvalRequest;

        if (!$request) {
            return $this->confirmed_at ? 'Pending' : 'Draft';
        }

        if ($request->status === 'pending') {
            $currentStep = $request->currentStep();
            $approverNames = $currentStep ? $currentStep->getResolvedApproverNames() : null;
            
            return $approverNames ? "Pending by {$approverNames}" : 'Pending';
        }

        return match ($request->status) {
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => 'Pending',
        };
    }

    /**
     * Sync the approval status with the model's native fields.
     */
    public function syncApprovalStatus(string $status): void
    {
        if ($status === 'approved') {
            $this->update(['confirmed_at' => now()]);
        }
    }
}
