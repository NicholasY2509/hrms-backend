<?php

namespace App\Modules\Audit\Services;

use App\Modules\Audit\Repositories\AuditLogRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Activitylog\Models\Activity;

class AuditLogService
{
    public function __construct(
        protected AuditLogRepository $auditLogRepository
    ) {}

    /**
     * Get paginated logs.
     */
    public function getLogs(array $filters): LengthAwarePaginator
    {
        return $this->auditLogRepository->paginate($filters, $filters['per_page'] ?? 15);
    }

    /**
     * Get log detail.
     */
    public function getLogDetail(int $id): ?Activity
    {
        return $this->auditLogRepository->find($id);
    }
}
