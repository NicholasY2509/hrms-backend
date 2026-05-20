<?php

namespace App\Modules\Attendance\Services;

use App\Modules\Attendance\Models\AttendanceUser;
use App\Modules\Attendance\Repositories\AttendanceUserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceUserService
{
    protected $repository;

    public function __construct(AttendanceUserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated attendance users.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * Create a new attendance user mapping.
     *
     * @param array $data
     * @return AttendanceUser
     */
    public function createMapping(array $data): AttendanceUser
    {
        return $this->repository->create($data);
    }

    /**
     * Update an attendance user mapping.
     *
     * @param AttendanceUser $attendanceUser
     * @param array $data
     * @return AttendanceUser
     */
    public function updateMapping(AttendanceUser $attendanceUser, array $data): AttendanceUser
    {
        $this->repository->update($attendanceUser, $data);
        return $attendanceUser->load(['employee', 'zktecoMachine']);
    }

    /**
     * Delete an attendance user mapping.
     *
     * @param AttendanceUser $attendanceUser
     * @return bool
     */
    public function deleteMapping(AttendanceUser $attendanceUser): bool
    {
        return $this->repository->delete($attendanceUser);
    }
}
