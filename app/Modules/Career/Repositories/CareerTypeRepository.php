<?php

namespace App\Modules\Career\Repositories;

use App\Modules\Career\Models\CareerType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CareerTypeRepository
{
    /**
     * Get all career types.
     */
    public function all(): Collection
    {
        return CareerType::orderBy('name')->get();
    }

    /**
     * Paginate career types.
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CareerType::query()
            ->filter($filters)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a career type by ID.
     */
    public function find(int $id): ?CareerType
    {
        return CareerType::find($id);
    }

    /**
     * Create a new career type.
     */
    public function create(array $data): CareerType
    {
        return CareerType::create($data);
    }

    /**
     * Update a career type.
     */
    public function update(CareerType $type, array $data): CareerType
    {
        $type->update($data);
        return $type;
    }

    /**
     * Delete a career type.
     */
    public function delete(CareerType $type): ?bool
    {
        return $type->delete();
    }
}
