<?php

namespace App\Modules\Attendance\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;

class Attendance extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $table = 'attendances';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'all_scans' => 'array',
        'mobile_scans' => 'array',
        'is_manual_override' => 'boolean',
    ];

    /**
     * Get the attendance_working_hour that owns the Attendance.
     */
    public function attendance_working_hour(): BelongsTo
    {
        return $this->belongsTo(AttendanceWorkingHour::class, 'attendance_working_hour_id', 'id');
    }

    /**
     * Get the attendance_status that owns the Attendance.
     */
    public function attendance_status(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class, 'attendance_status_id', 'id');
    }

    /**
     * Get the incoming_location that owns the Attendance.
     */
    public function incoming_location(): BelongsTo
    {
        return $this->belongsTo(AttendanceLocation::class, 'incoming_location_id', 'id');
    }

    /**
     * Get the outgoing_location that owns the Attendance.
     */
    public function outgoing_location(): BelongsTo
    {
        return $this->belongsTo(AttendanceLocation::class, 'outgoing_location_id', 'id');
    }

    /**
     * Get the mobile scan records for this attendance.
     */
    public function mobile_scan_records(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AttendanceMobileScan::class, 'attendance_id', 'id');
    }

    /**
     * Scope a query to apply filters.
     */
    public function scopeFilter($query, array $filters)
    {
        $query->whereHas('attendance_working_hour', function ($q) use ($filters) {
            if (!empty($filters['start_date'])) {
                $q->where('attendance_at', '>=', $filters['start_date']);
            }
            if (!empty($filters['end_date'])) {
                $q->where('attendance_at', '<=', $filters['end_date']);
            }
            if (!empty($filters['employee_id'])) {
                $q->where('employee_id', $filters['employee_id']);
            }
            
            if (!empty($filters['department_id'])) {
                $q->whereHas('employee', function ($eq) use ($filters) {
                    $ids = is_array($filters['department_id']) ? $filters['department_id'] : explode(',', $filters['department_id']);
                    $eq->whereIn('department_id', $ids);
                });
            }

            if (!empty($filters['team_id'])) {
                $q->whereHas('employee', function ($eq) use ($filters) {
                    $ids = is_array($filters['team_id']) ? $filters['team_id'] : explode(',', $filters['team_id']);
                    $eq->whereIn('team_id', $ids);
                });
            }

            if (!empty($filters['work_position_id'])) {
                $q->whereHas('employee', function ($eq) use ($filters) {
                    $ids = is_array($filters['work_position_id']) ? $filters['work_position_id'] : explode(',', $filters['work_position_id']);
                    $eq->whereIn('work_position_id', $ids);
                });
            }

            if (!empty($filters['search'])) {
                $q->whereHas('employee', function ($eq) use ($filters) {
                    $eq->filter(['search' => $filters['search']]);
                });
            }
        });

        if (!empty($filters['attendance_status_id'])) {
            $ids = is_array($filters['attendance_status_id']) ? $filters['attendance_status_id'] : explode(',', $filters['attendance_status_id']);
            $query->whereIn('attendance_status_id', $ids);
        }

        return $query;
    }
}

