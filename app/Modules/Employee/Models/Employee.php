<?php

namespace App\Modules\Employee\Models;

use App\Modules\Attendance\Models\AttendanceWorkingHour;
use App\Modules\Attendance\Models\AttendanceUser;
use App\Modules\Disciplinary\Models\WarningLetter;
use App\Modules\Payroll\Models\EmployeeTaxProfile;
use App\Modules\User\Models\User;
use App\Modules\Organization\Models\Department;
use App\Modules\Organization\Models\Team;
use App\Modules\Organization\Models\WorkLocation;
use App\Modules\Organization\Models\WorkPosition;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

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

    /**
     * Get the attendance machine UID records for the Employee.
     */
    public function attendance_users(): HasMany
    {
        return $this->hasMany(AttendanceUser::class, 'employee_id', 'id');
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
     * Get the education history for the employee.
     */
    public function educations(): HasMany
    {
        return $this->hasMany(EmployeeEducation::class);
    }

    /**
     * Get the work experience history for the employee.
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(EmployeeExperience::class);
    }

    /**
     * Get the family members for the employee.
     */
    public function families(): HasMany
    {
        return $this->hasMany(EmployeeFamily::class);
    }

    /**
     * Get the bank accounts for the employee.
     */
    public function banks(): HasMany
    {
        return $this->hasMany(EmployeeBank::class);
    }

    /**
     * Get the warning letters for the employee.
     */
    public function warnings(): HasMany
    {
        return $this->hasMany(EmployeeWarning::class);
    }

    /**
     * Get the disciplinary warning letters for the employee.
     */
    public function warning_letters(): HasMany
    {
        return $this->hasMany(WarningLetter::class);
    }

    /**
     * Get the contract history for the employee.
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(EmployeeContract::class);
    }

    /**
     * Get the emergency contacts for the employee.
     */
    public function emergency_contacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /**
     * Get the driver licenses for the employee.
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(EmployeeLicense::class);
    }

    /**
     * Get the owned vehicles for the employee.
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(EmployeeVehicle::class);
    }

    /**
     * Get the attachments for the employee.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(EmployeeAttachment::class);
    }

    /**
     * Get the insurances for the employee.
     */
    public function insurances(): HasMany
    {
        return $this->hasMany(EmployeeInsurance::class);
    }

    /**
     * Get the training history for the employee.
     */
    public function trainings(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class);
    }

    /**
     * Get the performance records for the employee.
     */
    public function performances(): HasMany
    {
        return $this->hasMany(EmployeePerformance::class);
    }

    /**
     * Get the inventory handovers for the employee.
     */
    public function inventories(): HasMany
    {
        return $this->hasMany(EmployeeInventory::class);
    }

    /**
     * Get the BPJS records for the employee.
     */
    public function employee_bpjs(): HasMany
    {
        return $this->hasMany(EmployeeBpjs::class);
    }

    /**
     * Get the resignation records for the employee.
     */
    public function resignations(): HasMany
    {
        return $this->hasMany(Resignation::class);
    }

    /**
     * Get the tax profile associated with the employee.
     */
    public function tax_profile(): HasOne
    {
        return $this->hasOne(EmployeeTaxProfile::class);
    }

    /**
     * Get the latest approved resignation for the employee.
     */
    public function latestResignation(): HasOne
    {
        return $this->hasOne(Resignation::class)->latestOfMany();
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
     * Get the work employee status associated with this Employee.
     */
    public function work_employee_status(): BelongsTo
    {
        return $this->belongsTo(WorkEmployeeStatus::class, 'work_employee_status_id', 'id');
    }

    /**
     * Get the employee status associated with this Employee.
     */
    public function employee_status(): BelongsTo
    {
        return $this->belongsTo(EmployeeStatus::class, 'employee_status_id', 'id');
    }

    /**
     * Get the gender of the employee.
     */
    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }

    /**
     * Get the religion of the employee.
     */
    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class);
    }

    /**
     * Get the marital status of the employee.
     */
    public function marital_status(): BelongsTo
    {
        return $this->belongsTo(MaritalStatus::class);
    }

    /**
     * Get the blood group of the employee.
     */
    public function blood_group(): BelongsTo
    {
        return $this->belongsTo(BloodGroup::class);
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
            $search = preg_replace('/\s+/', ' ', trim($search)); // Normalize spaces
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id_number', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(last_name, ' ', first_name)"), 'like', "%{$search}%");
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

        $query->when($filters['supervisor_employee_id'] ?? null, function ($query, $supervisorId) {
            $supervisor = Employee::find($supervisorId);
            if ($supervisor) {
                $matrixRules = \App\Modules\Organization\Models\PositionHierarchyMatrix::where('supervisor_work_position_id', $supervisor->work_position_id)
                    ->where(function($q) use ($supervisor) {
                        $q->whereNull('work_location_id')
                          ->orWhere('work_location_id', $supervisor->work_location_id);
                    })
                    ->get(['department_id', 'work_position_id']);

                if ($matrixRules->isNotEmpty()) {
                    $query->where('work_location_id', $supervisor->work_location_id)
                          ->where(function ($q) use ($matrixRules) {
                              foreach ($matrixRules as $rule) {
                                  $q->orWhere(function ($sq) use ($rule) {
                                      $sq->where('department_id', $rule->department_id)
                                         ->where('work_position_id', $rule->work_position_id);
                                  });
                              }
                          });

                    // Only link employees with the same team_id if the supervisor belongs to a team
                    if ($supervisor->team_id) {
                        $query->where('team_id', $supervisor->team_id);
                    }
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        });
    }
}
