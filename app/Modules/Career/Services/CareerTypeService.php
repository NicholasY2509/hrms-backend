<?php

namespace App\Modules\Career\Services;

use App\Modules\Career\Models\CareerType;
use App\Modules\Career\Repositories\CareerTypeRepository;
use Illuminate\Support\Facades\DB;

class CareerTypeService
{
    public function __construct(
        protected CareerTypeRepository $repository
    ) {}

    /**
     * Create a new career type.
     */
    public function createType(array $data): CareerType
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    /**
     * Update an existing career type.
     */
    public function updateType(CareerType $type, array $data): CareerType
    {
        return DB::transaction(function () use ($type, $data) {
            return $this->repository->update($type, $data);
        });
    }

    /**
     * Delete a career type.
     */
    public function deleteType(CareerType $type): bool
    {
        return DB::transaction(function () use ($type) {
            return $this->repository->delete($type);
        });
    }
}
