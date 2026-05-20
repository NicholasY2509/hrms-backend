<?php

namespace App\Modules\Attendance\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceWorkingHour extends Model
{
    use SoftDeletes;

    protected $table = 'attendance_working_hours';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the attendance record for this working hour.
     */
    public function attendance(): HasOne
    {
        return $this->hasOne(Attendance::class, 'attendance_working_hour_id', 'id');
    }

    /**
     * Get the employee that owns the AttendanceWorkingHour.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get the working_hour associated with this attendance record.
     */
    public function working_hour(): BelongsTo
    {
        return $this->belongsTo(WorkingHour::class, 'working_hour_id', 'id');
    }

    /**
     * Scope a query to apply filters.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['employee_id'] ?? null, function ($q, $employeeId) {
            $q->where('employee_id', $employeeId);
        });

        $query->when($filters['start_date'] ?? null, function ($q, $startDate) {
            $q->whereDate('attendance_at', '>=', $startDate);
        });

        $query->when($filters['end_date'] ?? null, function ($q, $endDate) {
            $q->whereDate('attendance_at', '<=', $endDate);
        });

        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->whereHas('employee', function ($sq) use ($search) {
                $sq->filter(['search' => $search]);
            });
        });

        return $query;
    }
}

