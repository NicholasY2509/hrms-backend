<?php

namespace App\Modules\Employee\Repositories;

use App\Modules\Employee\Models\Supervisor;
use Illuminate\Pagination\LengthAwarePaginator;

class SupervisorRepository
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Supervisor::query()
            ->with(['employee'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->filter(['search' => $search]);
                });
            })
            ->paginate($perPage);
    }

    public function findById(int $id): ?Supervisor
    {
        return Supervisor::query()->with(['employee'])->find($id);
    }

    public function create(array $data): Supervisor
    {
        return Supervisor::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $supervisor = Supervisor::find($id);
        if (!$supervisor) return false;
        return $supervisor->update($data);
    }

    public function delete(int $id): bool
    {
        $supervisor = Supervisor::find($id);
        if (!$supervisor) return false;
        return $supervisor->delete();
    }
}
