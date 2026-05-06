<?php

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes;

    protected $table = 'attendances';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [
        'all_scans' => 'array',
        'mobile_scans' => 'array'
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
}

