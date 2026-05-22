<?php

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZktecoAttendance extends Model
{
    use \Awobaz\Compoships\Compoships;

    protected $table = 'zkteco_attendances';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = true;

    /**
     * Get the machine that generated this attendance log.
     */
    public function zkteco_machine(): BelongsTo
    {
        return $this->belongsTo(ZktecoMachine::class, 'zkteco_machine_id', 'id');
    }

    /**
     * Get the attendance user associated with this log's UID.
     */
    public function attendance_user(): BelongsTo
    {
        return $this->belongsTo(AttendanceUser::class, ['uid', 'zkteco_machine_id'], ['uid', 'zkteco_machine_id']);
    }

    /**
     * Scope for filtering Zkteco attendance logs.
     */
    public function scopeFilter($query, array $filters)
    {
        $uid = $filters['uid'] ?? false;
        $search = $filters['search'] ?? false;
        $zkteco_machine_id = $filters['zkteco_machine_id'] ?? false;
        $start_date = $filters['start_date'] ?? false;
        $end_date = $filters['end_date'] ?? false;

        $query->when($uid, function ($query, $uid) {
            $query->where('uid', $uid);
        });

        $query->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('uid', 'like', "%{$search}%")
                  ->orWhereHas('attendance_user', function ($sq) use ($search) {
                      $sq->filter(['search' => $search]);
                  });
            });
        });

        $query->when($zkteco_machine_id, function ($query, $zkteco_machine_id) {
            $query->where('zkteco_machine_id', $zkteco_machine_id);
        });

        $query->when($start_date, function ($query, $start_date) {
            $query->whereDate('timestamp', '>=', $start_date);
        });

        $query->when($end_date, function ($query, $end_date) {
            $query->whereDate('timestamp', '<=', $end_date);
        });
    }
}
