<?php

namespace App\Modules\Organization\Repositories;

use App\Modules\Organization\Models\WorkLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WorkLocationRepository
{
    /**
     * Get paginated work locations.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = WorkLocation::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Find work location by ID.
     */
    public function findById(int $id): ?WorkLocation
    {
        return WorkLocation::find($id);
    }
}
