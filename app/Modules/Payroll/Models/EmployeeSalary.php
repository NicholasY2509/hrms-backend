<?php

namespace App\Modules\Payroll\Models;

use App\Modules\Employee\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeSalary extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_salaries';
    protected $guarded = ['id'];
    protected $casts = [
        'bpjs_base_amount' => 'decimal:2',
        'actual_base_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get the currently active salary for an employee.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
