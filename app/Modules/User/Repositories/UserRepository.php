<?php

namespace App\Modules\User\Repositories;

use App\Modules\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
            ->with(['employee', 'user_employee'])
            ->filter($filters)
            ->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return User::query()->with(['employee', 'user_employee'])->find($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
