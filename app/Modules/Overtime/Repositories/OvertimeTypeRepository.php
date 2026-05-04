<?php

namespace App\Modules\Overtime\Repositories;

use App\Modules\Overtime\Models\OvertimeType;
use Illuminate\Database\Eloquent\Collection;

class OvertimeTypeRepository
{
    /**
     * Get all overtime types.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return OvertimeType::all();
    }

    /**
     * Find an overtime type by ID.
     *
     * @param int $id
     * @return OvertimeType|null
     */
    public function find(int $id): ?OvertimeType
    {
        return OvertimeType::find($id);
    }

    /**
     * Create a new overtime type.
     *
     * @param array $data
     * @return OvertimeType
     */
    public function create(array $data): OvertimeType
    {
        return OvertimeType::create($data);
    }

    /**
     * Update an existing overtime type.
     *
     * @param OvertimeType $type
     * @param array $data
     * @return OvertimeType
     */
    public function update(OvertimeType $type, array $data): OvertimeType
    {
        $type->update($data);
        return $type;
    }

    /**
     * Delete an overtime type.
     *
     * @param OvertimeType $type
     * @return bool|null
     */
    public function delete(OvertimeType $type): ?bool
    {
        return $type->delete();
    }
}
