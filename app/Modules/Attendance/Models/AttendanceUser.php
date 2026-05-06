<?php

namespace App\Modules\Attendance\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceUser extends Model
{
    use SoftDeletes;

    protected $table = 'attendance_users';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the employee that owns the AttendanceUser.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Scope for filtering attendance users.
     */
    public function scopeFilter($query, array $filters)
    {
        $search = $filters['search'] ?? false;
        $employee_id = $filters['employee_id'] ?? false;

        $query->when($employee_id, function ($query, $employee_id) {
            $query->where('employee_id', $employee_id);
        });

        $query->when($search, function ($query, $search) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id_number', 'like', "%{$search}%");
            })->orWhere('uid', 'like', "%{$search}%");
        });
    }
}
