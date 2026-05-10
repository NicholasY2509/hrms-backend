<?php

namespace App\Modules\Disciplinary\Repositories;

use App\Modules\Disciplinary\Models\WarningLetterType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WarningLetterTypeRepository
{
    /**
     * Get all warning letter types.
     *
     * @return Collection
     */
    public function all(): Collection
    {
        return WarningLetterType::orderBy('name')->get();
    }

    /**
     * Paginate warning letter types.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return WarningLetterType::query()
            ->filter($filters)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Find a warning letter type by ID.
     *
     * @param int $id
     * @return WarningLetterType|null
     */
    public function find(int $id): ?WarningLetterType
    {
        return WarningLetterType::find($id);
    }

    /**
     * Create a new warning letter type.
     *
     * @param array $data
     * @return WarningLetterType
     */
    public function create(array $data): WarningLetterType
    {
        return WarningLetterType::create($data);
    }

    /**
     * Update a warning letter type.
     *
     * @param WarningLetterType $type
     * @param array $data
     * @return WarningLetterType
     */
    public function update(WarningLetterType $type, array $data): WarningLetterType
    {
        $type->update($data);
        return $type;
    }

    /**
     * Delete a warning letter type.
     *
     * @param WarningLetterType $type
     * @return bool|null
     */
    public function delete(WarningLetterType $type): ?bool
    {
        return $type->delete();
    }
}
