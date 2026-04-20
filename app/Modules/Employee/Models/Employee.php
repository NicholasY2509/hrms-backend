<?php

namespace App\Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'employees';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['full_name'];

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    /**
     * Get the user_employee record.
     */
    public function user_employee(): HasOne
    {
        return $this->hasOne(UserEmployee::class, 'employee_id', 'id');
    }

    /**
     * Get all of the attendance_working_hours for the Employee.
     */
    public function attendance_working_hours(): HasMany
    {
        return $this->hasMany(AttendanceWorkingHour::class, 'employee_id', 'id');
    }

    /**
     * Get all of the leave approval configurations for the Employee.
     */
    public function employee_leave_approvals(): HasMany
    {
        return $this->hasMany(EmployeeLeaveApproval::class, 'employee_id', 'id');
    }

    /**
     * Get the supervisor associated with this Employee.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'supervisor_id', 'id');
    }
}
