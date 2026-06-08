<?php

namespace App\Modules\Leave\Models;

use App\Modules\Employee\Models\Employee;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnualLeave extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $table = 'annual_leaves';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'deduction_details' => 'array',
        'balance_before' => 'array',
        'balance_after' => 'array',
        'annual_leave_at' => 'date',
    ];

    /**
     * Get the employee that owns the AnnualLeave.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Scope a query to apply filters.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['employee_id'] ?? null, function ($q, $employeeId) {
            $q->where('employee_id', $employeeId);
        });

        $query->when($filters['status'] ?? null, function ($q, $status) {
            $q->where('status', $status);
        });

        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where(function ($sq) use ($search) {
                $employeeIds = Employee::filter(['search' => $search])->pluck('id');
                $sq->where('keterangan', 'like', '%' . $search . '%')
                   ->orWhereIn('employee_id', $employeeIds);
            });
        });

        $query->when($filters['start_date'] ?? null, function ($q, $startDate) {
            $q->whereDate('annual_leave_at', '>=', $startDate);
        });

        $query->when($filters['end_date'] ?? null, function ($q, $endDate) {
            $q->whereDate('annual_leave_at', '<=', $endDate);
        });

        return $query;
    }
}
