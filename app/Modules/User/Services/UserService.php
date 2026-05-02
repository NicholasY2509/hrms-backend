<?php

namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use App\Modules\User\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        protected UserRepository $repository
    ) {}

    public function listUsers(int $perPage, array $filters)
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    public function createUser(array $data): User
    {
        $employeeId = $data['employee_id'] ?? null;
        unset($data['employee_id']);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user = $this->repository->create($data);

        if ($employeeId) {
            $user->user_employee()->updateOrCreate(
                ['user_id' => $user->id],
                ['employee_id' => $employeeId]
            );
        }

        return $user->refresh();
    }

    public function updateUser(User $user, array $data): User
    {
        $employeeId = null;
        $hasEmployeeId = array_key_exists('employee_id', $data);
        if ($hasEmployeeId) {
            $employeeId = $data['employee_id'];
            unset($data['employee_id']);
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $this->repository->update($user, $data);

        if ($hasEmployeeId) {
            if ($employeeId) {
                $user->user_employee()->updateOrCreate(
                    ['user_id' => $user->id],
                    ['employee_id' => $employeeId]
                );
            } else {
                $user->user_employee()->delete();
            }
        }

        return $user->refresh();
    }

    public function deleteUser(User $user): bool
    {
        return $this->repository->delete($user);
    }

    public function getUser(int $id): User
    {
        $user = $this->repository->findById($id);

        if (!$user) {
            throw new \Exception('User not found', 404);
        }

        return $user;
    }
}
