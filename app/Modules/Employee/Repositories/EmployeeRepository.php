<?php

namespace App\Modules\Employee\Repositories;

use App\Modules\Employee\Models\Employee;

class EmployeeRepository
{
    /**
     * Find employee by user ID.
     *
     * @param int $userId
     * @return Employee|null
     */
    public function findByUserId(int $userId): ?Employee
    {
        return Employee::query()
            ->whereHas('user_employee', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['user_employee.user', 'supervisor.employee', 'department', 'position'])
            ->first();
    }

    /**
     * Paginate employees with filters.
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = [])
    {
        return Employee::query()
            ->with(['position', 'department', 'work_location', 'user_employee.user'])
            ->filter($filters)
            ->paginate($perPage);
    }

    /**
     * Get employee counts summary grouped by work status.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSummary()
    {
        $counts = Employee::query()
            ->select('work_employee_status_id', \DB::raw('count(*) as total'))
            ->groupBy('work_employee_status_id')
            ->get();

        $statuses = \DB::table('work_employee_statuses')->get();

        return $statuses->map(function ($status) use ($counts) {
            $count = $counts->where('work_employee_status_id', $status->id)->first();
            return [
                'id' => $status->id,
                'name' => $status->name,
                'count' => $count ? $count->total : 0
            ];
        });
    }

    /**
     * Find employee by ID.
     *
     * @param int $id
     * @return Employee
     */
    public function findById(int $id): Employee
    {
        return Employee::query()
            ->with(['user_employee.user', 'supervisor.employee', 'department', 'position'])
            ->findOrFail($id);
    }

    /**
     * Create a new employee.
     *
     * @param array $data
     * @return Employee
     */
    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    /**
     * Update an existing employee.
     *
     * @param int $id
     * @param array $data
     * @return Employee
     */
    public function update(int $id, array $data): Employee
    {
        $employee = $this->findById($id);
        $employee->update($data);
        return $employee->fresh();
    }

    /**
     * Delete an employee.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $employee = $this->findById($id);
        return $employee->delete();
    }
    /**
     * Get the last employee for a specific work position.
     *
     * @param int $workPositionId
     * @return Employee|null
     */
    public function getLastEmployeeByWorkPosition(int $workPositionId): ?Employee
    {
        return Employee::query()
            ->where('work_position_id', $workPositionId)
            ->orderByDesc('employee_id_number')
            ->first();
    }

    /**
     * Get the last employee excluding specific work position IDs.
     *
     * @param array $excludeWorkPositionIds
     * @return Employee|null
     */
    public function getLastEmployeeExcludingWorkPositions(array $excludeWorkPositionIds): ?Employee
    {
        return Employee::query()
            ->whereNotIn('work_position_id', $excludeWorkPositionIds)
            ->orderByDesc(\DB::raw('CAST(employee_id_number AS UNSIGNED)'))
            ->first();
    }
}
