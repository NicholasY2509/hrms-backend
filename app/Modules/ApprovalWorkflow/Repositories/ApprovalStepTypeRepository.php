<?php

namespace App\Modules\ApprovalWorkflow\Repositories;

use App\Modules\ApprovalWorkflow\Models\ApprovalStepType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApprovalStepTypeRepository
{
    /**
     * Get all types without pagination.
     */
    public function all(): Collection
    {
        return ApprovalStepType::all();
    }

    /**
     * Get paginated step types.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return ApprovalStepType::paginate($perPage);
    }

    public function create(array $data): ApprovalStepType
    {
        return ApprovalStepType::create($data);
    }

    public function find(int $id): ?ApprovalStepType
    {
        return ApprovalStepType::find($id);
    }

    public function update(ApprovalStepType $type, array $data): bool
    {
        return $type->update($data);
    }

    public function delete(int $id): bool
    {
        return ApprovalStepType::destroy($id) > 0;
    }
}
