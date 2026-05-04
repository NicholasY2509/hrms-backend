<?php

namespace App\Modules\UnpaidLeave\Repositories;

use App\Modules\UnpaidLeave\Models\UnpaidLeaveType;
use Illuminate\Database\Eloquent\Collection;

class UnpaidLeaveTypeRepository
{
    /**
     * Get all unpaid leave types.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return UnpaidLeaveType::all();
    }

    /**
     * Find an unpaid leave type by ID.
     *
     * @param int $id
     * @return UnpaidLeaveType|null
     */
    public function find(int $id): ?UnpaidLeaveType
    {
        return UnpaidLeaveType::find($id);
    }

    /**
     * Create a new unpaid leave type.
     *
     * @param array $data
     * @return UnpaidLeaveType
     */
    public function create(array $data): UnpaidLeaveType
    {
        return UnpaidLeaveType::create($data);
    }

    /**
     * Update an existing unpaid leave type.
     *
     * @param UnpaidLeaveType $type
     * @param array $data
     * @return UnpaidLeaveType
     */
    public function update(UnpaidLeaveType $type, array $data): UnpaidLeaveType
    {
        $type->update($data);
        return $type;
    }

    /**
     * Delete an unpaid leave type.
     *
     * @param UnpaidLeaveType $type
     * @return bool|null
     */
    public function delete(UnpaidLeaveType $type): ?bool
    {
        return $type->delete();
    }
}
