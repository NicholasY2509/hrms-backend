<?php

namespace App\Modules\Attendance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZktecoUser extends Model
{
    protected $table = 'zkteco_users';
    protected $guarded = [];
    public $incrementing = false;
    public $timestamps = true;

    /**
     * Get the machine that this user belongs to.
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(ZktecoMachine::class, 'zkteco_machine_id', 'id');
    }

    /**
     * Get the local attendance mapping for this machine user.
     */
    public function attendance_user(): BelongsTo
    {
        return $this->belongsTo(AttendanceUser::class, 'uid', 'uid')->where('zkteco_machine_id', 'zkteco_machine_id');
    }

    /**
     * Scope for filtering Zkteco machine users.
     */
    public function scopeFilter($query, array $filters)
    {
        $search = $filters['search'] ?? false;
        $zkteco_machine_id = $filters['zkteco_machine_id'] ?? false;

        $query->when($zkteco_machine_id, function ($query, $zkteco_machine_id) {
            $query->where('zkteco_machine_id', $zkteco_machine_id);
        });

        $query->when($search, function ($query, $search) {
            $search = preg_replace('/\s+/', ' ', trim($search));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('uid', 'like', "%{$search}%");
            });
        });
    }
}
