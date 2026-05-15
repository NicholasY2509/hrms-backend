<?php

namespace App\Modules\Employee\Repositories;

use App\Modules\Employee\Models\Resignation;
use Illuminate\Pagination\LengthAwarePaginator;

class ResignationRepository
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Resignation::query()->with(['employee', 'approvalRequest.steps']);

        if (isset($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        return $query->latest('effective_date')->paginate($perPage);
    }

    public function findById(int $id): ?Resignation
    {
        return Resignation::with(['employee', 'approvalRequest.steps'])->find($id);
    }

    public function create(array $data): Resignation
    {
        return Resignation::create($data);
    }

    public function update(Resignation $resignation, array $data): bool
    {
        return $resignation->update($data);
    }

    public function delete(Resignation $resignation): bool
    {
        return $resignation->delete();
    }
}
