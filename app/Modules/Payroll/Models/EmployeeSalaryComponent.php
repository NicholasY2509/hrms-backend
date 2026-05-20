<?php

namespace App\Modules\Payroll\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeSalaryComponent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_salary_components';
    protected $guarded = ['id'];
    protected $casts = [
        'amount' => 'decimal:2',
        'is_calculated' => 'boolean',
        'effective_date' => 'date',
        'end_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class, 'salary_component_id');
    }
}
