<?php

namespace App\Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Employee\Models\Employee;

class EmployeeSalary extends Model
{
    use SoftDeletes;

    protected $table = 'employee_salaries';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the employee that owns the salary.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
