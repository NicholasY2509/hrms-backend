<?php

namespace App\Modules\Organization\Repositories;

use App\Modules\Organization\Models\Department;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DepartmentRepository
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Department::query()
            ->with(['head'])
            ->withCount(['employees'])
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function getAll(array $filters): Collection
    {
        return Department::query()
            ->filter($filters)
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?Department
    {
        return Department::query()->with(['head', 'employees'])->find($id);
    }
}
