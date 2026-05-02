<?php

namespace App\Modules\Employee\Models;

use App\Modules\Attendance\Models\AttendanceWorkingHour;
use App\Modules\User\Models\User;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Team;
use App\Modules\Organization\Models\WorkLocation;
use App\Modules\Organization\Models\WorkPosition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'employees';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $appends = ['full_name', 'nik', 'profile_url'];

    /**
     * Get the employee's full name.
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    /**
     * Get the employee's NIK.
     */
    public function getNikAttribute(): ?string
    {
        return $this->employee_id_number;
    }

    /**
     * Get the employee's profile URL.
     */
    public function getProfileUrlAttribute(): ?string
    {
        return \App\Services\StorageService::url($this->avatar);
    }

    /**
     * Get the user_employee record.
     */
    public function user_employee(): HasOne
    {
        return $this->hasOne(UserEmployee::class, 'employee_id', 'id');
    }

    /**
     * Get the user associated with the Employee through user_employee.
     */
    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            UserEmployee::class,
            'employee_id',
            'id',
            'id',
            'user_id'
        );
    }

    /**
     * Get all of the attendance_working_hours for the Employee.
     */
    public function attendance_working_hours(): HasMany
    {
        return $this->hasMany(AttendanceWorkingHour::class, 'employee_id', 'id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
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

    /**
     * Get the department associated with this Employee.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }

    /**
     * Get the position associated with this Employee.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(WorkPosition::class, 'work_position_id', 'id');
    }

    /**
     * Get the work location associated with this Employee.
     */
    public function work_location(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'work_location_id', 'id');
    }

    /**
     * Scope a query to apply filters.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id_number', 'like', "%{$search}%")
                  ->orWhere(\DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
            });
        });

        $query->when($filters['work_position_id'] ?? null, function ($query, $positionId) {
            $query->where('work_position_id', $positionId);
        });

        $query->when($filters['team_id'] ?? null, function ($query, $teamId) {
            $query->where('team_id', $teamId);
        });

        $query->when($filters['department_id'] ?? null, function ($query, $departmentId) {
            $query->where('department_id', $departmentId);
        });

        $query->when($filters['work_location_id'] ?? null, function ($query, $locationId) {
            $query->where('work_location_id', $locationId);
        });

        $query->when($filters['work_employee_status_id'] ?? null, function ($query, $statusId) {
            $query->where('work_employee_status_id', $statusId);
        });
    }
}
