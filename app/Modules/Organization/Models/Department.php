<?php

namespace App\Modules\Organization\Models;

use App\Modules\Employee\Models\Employee;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Support\LogOptions;

class Department extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    protected $table = 'departments';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function scopeFilter($query, array $filters)
    {
        $search = $filters['search'] ?? false;

        $query->when($search, function ($query, $search) {
            $search = preg_replace('/\s+/', ' ', trim($search));
            $query->where('name', 'like', "%$search%");
        });
    }

    /**
     * Get all department head assignments (per work location).
     */
    public function heads(): HasMany
    {
        return $this->hasMany(DepartmentHead::class, 'department_id', 'id');
    }

    /**
     * Get the department head for a specific work location.
     */
    public function headAt(int $workLocationId): ?DepartmentHead
    {
        return $this->heads()->where('work_location_id', $workLocationId)->first()
            ?? $this->heads()->whereNull('work_location_id')->first();
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'department_id', 'id')->where('work_employee_status_id', 1);
    }
}
