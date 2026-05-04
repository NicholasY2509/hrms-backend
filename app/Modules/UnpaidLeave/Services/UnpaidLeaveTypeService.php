<?php

namespace App\Modules\UnpaidLeave\Services;

use App\Modules\UnpaidLeave\Repositories\UnpaidLeaveTypeRepository;
use Illuminate\Database\Eloquent\Collection;

class UnpaidLeaveTypeService
{
    private UnpaidLeaveTypeRepository $repository;

    public function __construct(UnpaidLeaveTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all unpaid leave types.
     *
     * @return Collection
     */
    public function getAllTypes(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Get an unpaid leave type by ID.
     *
     * @param int $id
     * @return \App\Modules\UnpaidLeave\Models\UnpaidLeaveType|null
     */
    public function getTypeById(int $id)
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new unpaid leave type.
     *
     * @param array $data
     * @return \App\Modules\UnpaidLeave\Models\UnpaidLeaveType
     */
    public function createType(array $data)
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing unpaid leave type.
     *
     * @param int $id
     * @param array $data
     * @return \App\Modules\UnpaidLeave\Models\UnpaidLeaveType
     */
    public function updateType(int $id, array $data)
    {
        $type = $this->repository->find($id);
        if (!$type) {
            throw new \Exception('Unpaid leave type not found.');
        }

        return $this->repository->update($type, $data);
    }

    /**
     * Delete an unpaid leave type.
     *
     * @param int $id
     * @return bool|null
     */
    public function deleteType(int $id)
    {
        $type = $this->repository->find($id);
        if (!$type) {
            throw new \Exception('Unpaid leave type not found.');
        }

        return $this->repository->delete($type);
    }
}
