<?php

namespace App\Modules\ApprovalWorkflow\Repositories;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequestType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ApprovalRequestTypeRepository
{
    public function all(): Collection
    {
        return ApprovalRequestType::where('is_active', true)->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return ApprovalRequestType::paginate($perPage);
    }

    public function create(array $data): ApprovalRequestType
    {
        return ApprovalRequestType::create($data);
    }

    public function find(int $id): ?ApprovalRequestType
    {
        return ApprovalRequestType::find($id);
    }

    public function update(ApprovalRequestType $type, array $data): bool
    {
        return $type->update($data);
    }

    public function delete(int $id): bool
    {
        return ApprovalRequestType::destroy($id) > 0;
    }
}
