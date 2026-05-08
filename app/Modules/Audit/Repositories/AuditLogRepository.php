<?php

namespace App\Modules\Audit\Repositories;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogRepository
{
    /**
     * Get paginated activity logs with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Activity::with(['causer', 'subject'])
            ->latest();

        if (!empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (!empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }

        if (!empty($filters['subject_type'])) {
            $query->where('subject_type', $filters['subject_type']);
        }

        if (!empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get a single activity log.
     *
     * @param int $id
     * @return Activity|null
     */
    public function find(int $id): ?Activity
    {
        return Activity::with(['causer', 'subject'])->find($id);
    }
}
