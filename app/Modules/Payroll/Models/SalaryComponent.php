<?php

namespace App\Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalaryComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'salary_components';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Categories: allowance, deduction, benefit
     * Types: fixed, calculated, one-time
     */
    
    public function employee_configurations()
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }
}
