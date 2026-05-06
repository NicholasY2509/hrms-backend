<?php

namespace App\Modules\Attendance\Models;

use App\Modules\Organization\Models\WorkLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZktecoMachine extends Model
{
    use SoftDeletes;

    protected $table = 'zkteco_machines';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'online' => 'boolean',
    ];

    /**
     * Get the work location that owns the ZktecoMachine.
     */
    public function work_location(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'work_location_id', 'id');
    }

    /**
     * Get the attendance location that owns the ZktecoMachine.
     */
    public function attendance_location(): BelongsTo
    {
        return $this->belongsTo(AttendanceLocation::class, 'attendance_location_id', 'id');
    }

    /**
     * Scope for filtering Zkteco machines.
     */
    public function scopeFilter($query, array $filters)
    {
        $search = $filters['search'] ?? false;
        $work_location_id = $filters['work_location_id'] ?? false;
        $online = $filters['online'] ?? null;

        $query->when($work_location_id, function ($query, $work_location_id) {
            $query->where('work_location_id', $work_location_id);
        });

        $query->when($online !== null, function ($query) use ($online) {
            $query->where('online', (bool) $online);
        });

        $query->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        });
    }
}
