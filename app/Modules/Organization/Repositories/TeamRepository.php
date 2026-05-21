<?php

namespace App\Modules\Organization\Repositories;

use App\Modules\Organization\Models\Team;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TeamRepository
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Team::query()
            ->with(['head', 'workLocation', 'employees', 'workLocation'])
            ->withCount(['employees'])
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function getAll(array $filters): Collection
    {
        return Team::query()
            ->filter($filters)
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?Team
    {
        return Team::query()->with(['head', 'workLocation', 'employees'])->find($id);
    }
}
