<?php

namespace App\Modules\Disciplinary\Services;

use App\Modules\Disciplinary\Models\WarningLetterType;
use App\Modules\Disciplinary\Repositories\WarningLetterTypeRepository;
use Illuminate\Support\Facades\DB;

class WarningLetterTypeService
{
    public function __construct(
        protected WarningLetterTypeRepository $repository
    ) {}

    /**
     * Create a new warning letter type.
     */
    public function createType(array $data): WarningLetterType
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    /**
     * Update an existing warning letter type.
     */
    public function updateType(WarningLetterType $type, array $data): WarningLetterType
    {
        return DB::transaction(function () use ($type, $data) {
            return $this->repository->update($type, $data);
        });
    }

    /**
     * Delete a warning letter type.
     */
    public function deleteType(WarningLetterType $type): bool
    {
        return DB::transaction(function () use ($type) {
            return $this->repository->delete($type);
        });
    }
}
