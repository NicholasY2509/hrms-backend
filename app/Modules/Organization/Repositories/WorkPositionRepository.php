<?php

namespace App\Modules\Organization\Repositories;

use App\Modules\Organization\Models\WorkPosition;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WorkPositionRepository
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return WorkPosition::query()
            ->withCount('employees')
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function getAll(array $filters): Collection
    {
        return WorkPosition::query()
            ->filter($filters)
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?WorkPosition
    {
        return WorkPosition::query()->with(['criteria', 'approvals'])->find($id);
    }
}
