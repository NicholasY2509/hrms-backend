<?php
 
namespace App\Modules\Organization\Models;
 
use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
 
class Team extends Model
{
    use SoftDeletes;
 
    protected $table = 'teams';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
 
    public function scopeFilter($query, array $filters)
    {
        $search = $filters['search'] ?? false;
 
        $query->when($search, function ($query, $search) {
            $query->where('name', 'like', "%$search%");
        });
    }
 
    public function head(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'team_head_id', 'id');
    }
 
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'team_id', 'id')->where('work_employee_status_id', 1);
    }
}
