<?php

namespace App\Modules\Attendance\Models;

use App\Modules\Employee\Models\WorkLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttendanceLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'legacy';
    protected $table = 'attendance_locations';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Scope for filtering attendance locations.
     */
    public function scopeFilter($query, array $filters)
    {
        $search = $filters['search'] ?? false;
        $work_location_id = $filters['work_location_id'] ?? false;

        $query->when($work_location_id, function ($query, $work_location_id) {
            $query->where('work_location_id', $work_location_id);
        });

        $query->when($search, function ($query, $search) {
            $query->where('name', 'like', "%$search%");
        });
    }

    /**
     * Get the work_location that owns the AttendanceLocation.
     */
    public function work_location(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class, 'work_location_id', 'id');
    }
}
