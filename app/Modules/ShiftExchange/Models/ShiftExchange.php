<?php

namespace App\Modules\ShiftExchange\Models;

use App\Modules\Employee\Models\Employee;
use App\Modules\Attendance\Models\WorkingHour;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Approvable;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftExchange extends Model
{
    use Approvable, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $table = 'shift_exchanges';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the employee that owns the Shift Exchange request.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get the original working hour.
     */
    public function originalWorkingHour(): BelongsTo
    {
        return $this->belongsTo(WorkingHour::class, 'original_working_hour_id', 'id');
    }

    /**
     * Get the requested working hour.
     */
    public function requestedWorkingHour(): BelongsTo
    {
        return $this->belongsTo(WorkingHour::class, 'requested_working_hour_id', 'id');
    }

    /**
     * Get the optional employee to exchange with.
     */
    public function exchangeWithEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'exchange_with_employee_id', 'id');
    }

    /**
     * Get the dynamic status of the shift exchange request.
     */
    public function getStatusAttribute()
    {
        if ($this->settled_at) {
            return 'Settled';
        }

        $request = $this->approvalRequest;
        
        if (!$request) {
            return 'Pending';
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
     * Scope a query to apply filters.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['employee_id'] ?? null, function ($q, $employeeId) {
            $q->where('employee_id', $employeeId);
        });

        $query->when($filters['start_date'] ?? null, function ($q, $startDate) use ($filters) {
            if (!empty($filters['end_date'])) {
                $q->whereBetween('date', [$startDate, $filters['end_date']]);
            } else {
                $q->whereDate('date', '>=', $startDate);
            }
        });

        $query->when($filters['end_date'] ?? null, function ($q, $endDate) use ($filters) {
            if (empty($filters['start_date'])) {
                $q->whereDate('date', '<=', $endDate);
            }
        });

        if (isset($filters['is_settled'])) {
            $filters['is_settled'] ? $query->whereNotNull('settled_at') : $query->whereNull('settled_at');
        }

        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->whereHas('employee', function ($sq) use ($search) {
                $sq->filter(['search' => $search]); // Assumes Employee model has a filter scope for 'search'
            });
        });

        return $query;
    }
}
