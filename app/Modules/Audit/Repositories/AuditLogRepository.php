<?php

namespace App\Modules\Audit\Repositories;

use Spatie\Activitylog\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogRepository
{
    public function getLogs(array $filters): LengthAwarePaginator
    {
        $query = Activity::with(['causer', 'subject'])->latest();

        $query->when($filters['log_name'] ?? null, function ($q, $logName) {
            $q->where('log_name', $logName);
        });

        $query->when($filters['event'] ?? null, function ($q, $event) {
            $q->where('event', $event);
        });

        $query->when($filters['causer_id'] ?? null, function ($q, $causerId) {
            $q->where('causer_id', $causerId);
        });

        $query->when($filters['subject_type'] ?? null, function ($q, $type) {
            $q->where('subject_type', 'like', "%{$type}%");
        });

        $query->when($filters['start_date'] ?? null, function ($q, $date) {
            $q->whereDate('created_at', '>=', $date);
        });

        $query->when($filters['end_date'] ?? null, function ($q, $date) {
            $q->whereDate('created_at', '<=', $date);
        });

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Activity
    {
        return Activity::with(['causer', 'subject'])->find($id);
    }
}
