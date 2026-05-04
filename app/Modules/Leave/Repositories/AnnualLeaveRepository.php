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
        $query = AnnualLeave::with(['employee']);

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('keterangan', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('employee', function ($q) use ($filters) {
                        $q->where('full_name', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('employee_id_number', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        return $query->latest('annual_leave_at')->paginate($perPage);
    }
}
