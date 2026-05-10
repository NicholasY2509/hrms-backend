<?php

namespace App\Modules\Leave\Repositories;

use App\Modules\Leave\Models\AnnualLeave;

class AnnualLeaveRepository
{
    /**
     * Create a new annual leave record.
     */
    public function create(array $data): AnnualLeave
    {
        return AnnualLeave::create($data);
    }

    /**
     * Find an annual leave record by ID.
     */
    public function find(int $id): ?AnnualLeave
    {
        return AnnualLeave::find($id);
    }

    /**
     * Get paginated annual leaves.
     */
    public function getPaginated(array $filters, int $perPage = 15)
    {
        return AnnualLeave::query()
            ->with(['employee'])
            ->filter($filters)
            ->latest('annual_leave_at')
            ->paginate($perPage);
    }
}
