<?php

namespace App\Modules\Overtime\Services;

use App\Modules\Overtime\Repositories\OvertimeTypeRepository;
use Illuminate\Database\Eloquent\Collection;

class OvertimeTypeService
{
    private OvertimeTypeRepository $repository;

    public function __construct(OvertimeTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get all overtime types.
     *
     * @return Collection
     */
    public function getAllTypes(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * Get an overtime type by ID.
     *
     * @param int $id
     * @return \App\Modules\Overtime\Models\OvertimeType|null
     */
    public function getTypeById(int $id)
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new overtime type.
     *
     * @param array $data
     * @return \App\Modules\Overtime\Models\OvertimeType
     */
    public function createType(array $data)
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing overtime type.
     *
     * @param int $id
     * @param array $data
     * @return \App\Modules\Overtime\Models\OvertimeType
     */
    public function updateType(int $id, array $data)
    {
        $type = $this->repository->find($id);
        if (!$type) {
            throw new \Exception('Overtime type not found.');
        }

        return $this->repository->update($type, $data);
    }

    /**
     * Delete an overtime type.
     *
     * @param int $id
     * @return bool|null
     */
    public function deleteType(int $id)
    {
        $type = $this->repository->find($id);
        if (!$type) {
            throw new \Exception('Overtime type not found.');
        }

        return $this->repository->delete($type);
    }
}
