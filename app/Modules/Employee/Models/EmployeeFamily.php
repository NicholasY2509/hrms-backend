<?php

namespace App\Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFamily extends Model
{
    use SoftDeletes;

    protected $table = 'employee_families';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Get the employee that owns the family member.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the relationship type.
     */
    public function relationship(): BelongsTo
    {
        return $this->belongsTo(FamilyRelationship::class, 'family_relationship_id');
    }

    /**
     * Get the gender.
     */
    public function gender(): BelongsTo
    {
        return $this->belongsTo(Gender::class);
    }
}
