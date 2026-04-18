<?php

namespace App\Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $connection = 'legacy';
    protected $table = 'employees';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

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
}
