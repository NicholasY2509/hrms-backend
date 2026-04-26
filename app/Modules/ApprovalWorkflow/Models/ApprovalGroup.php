<?php

namespace App\Modules\ApprovalWorkflow\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ApprovalGroup extends Model
{
    protected $table = 'approval_groups';
    protected $guarded = ['id'];

    /**
     * Employees belonging to this group.
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'approval_group_employees', 'approval_group_id', 'employee_id');
    }
}
