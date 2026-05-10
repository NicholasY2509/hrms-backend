<?php

namespace App\Modules\Attendance\Repositories;

use App\Modules\Attendance\Models\AttendanceUser;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceUserRepository
{
    /**
     * Get paginated attendance user mappings.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return AttendanceUser::with(['employee', 'zkteco_machine'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new attendance user mapping.
     *
     * @param array $data
     * @return AttendanceUser
     */
    public function create(array $data): AttendanceUser
    {
        return AttendanceUser::create($data);
    }

    /**
     * Update an attendance user mapping.
     *
     * @param AttendanceUser $attendanceUser
     * @param array $data
     * @return bool
     */
    public function update(AttendanceUser $attendanceUser, array $data): bool
    {
        return $attendanceUser->update($data);
    }

    /**
     * Delete an attendance user mapping.
     *
     * @param AttendanceUser $attendanceUser
     * @return bool|null
     */
    public function delete(AttendanceUser $attendanceUser): ?bool
    {
        return $attendanceUser->delete();
    }

    /**
     * Find an attendance user mapping by its ID.
     *
     * @param int $id
     * @return AttendanceUser|null
     */
    public function find(int $id): ?AttendanceUser
    {
        return AttendanceUser::with(['employee', 'zktecoMachine'])->find($id);
    }
}
