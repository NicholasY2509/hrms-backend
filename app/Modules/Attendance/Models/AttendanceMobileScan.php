<?php

namespace App\Modules\Attendance\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceMobileScan extends Model
{
    use SoftDeletes;

    protected $table = 'attendance_mobile_scans';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the attendance record this mobile scan belongs to.
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class, 'attendance_id', 'id');
    }

    /**
     * Get the employee that performed the scan.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Get the location where the scan was performed.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(AttendanceLocation::class, 'location_id', 'id');
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
            $q->where('created_at', '>=', \Carbon\Carbon::parse($startDate)->startOfDay());
        });

        $query->when($filters['end_date'] ?? null, function ($q, $endDate) {
            $q->where('created_at', '<=', \Carbon\Carbon::parse($endDate)->endOfDay());
        });

        $query->when($filters['scan_type'] ?? null, function ($q, $scanType) {
            $q->where('scan_type', $scanType);
        });

        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->whereHas('employee', function ($sq) use ($search) {
                $sq->filter(['search' => $search]);
            });
        });

        return $query;
    }
}
