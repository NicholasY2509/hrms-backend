<?php

namespace App\Modules\Employee\Repositories;

use App\Modules\Employee\Models\Employee;
use Illuminate\Support\Facades\DB;

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
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $filters = [], int $page = 1)
    {
        $query = DB::table('employees as e')
            ->leftJoin('departments as d', 'e.department_id', '=', 'd.id')
            ->leftJoin('work_positions as wp', 'e.work_position_id', '=', 'wp.id')
            ->leftJoin('work_locations as wl', 'e.work_location_id', '=', 'wl.id')
            ->leftJoin('work_employee_statuses as wes', 'e.work_employee_status_id', '=', 'wes.id')
            ->leftJoin('user_employees as ue', 'e.id', '=', 'ue.employee_id')
            ->leftJoin('users as u', 'ue.user_id', '=', 'u.id')
            ->leftJoin('supervisors as s', 'e.supervisor_id', '=', 's.id')
            ->leftJoin('employees as se', 's.employee_id', '=', 'se.id')
            ->whereNull('e.deleted_at')
            ->select([
                'e.id',
                'e.employee_id_number',
                'e.id_card_number',
                'e.first_name',
                'e.last_name',
                'e.initial_name',
                'e.company_email',
                'e.avatar',
                'e.join_date',
                'e.resign_date',
                'e.phone_number',
                'e.handphone',
                'e.current_address',
                'e.place_birth',
                'e.date_birth',
                'e.annual_leave_2',
                'e.annual_leave_3',
                'd.id as department_id',
                'd.name as department_name',
                'wp.id as position_id',
                'wp.name as position_name',
                'wl.id as work_location_id',
                'wl.name as work_location_name',
                'wes.id as work_employee_status_id',
                'wes.name as work_employee_status_name',
                'u.email as user_email',
                'se.id as supervisor_employee_id',
                'se.employee_id_number as supervisor_nik',
                'se.first_name as supervisor_first_name',
                'se.last_name as supervisor_last_name',
            ]);

        // Replicating Employee::scopeFilter logic for RAW Query
        $query->when($filters['search'] ?? null, function ($q, $search) {
            $search = preg_replace('/\s+/', ' ', trim($search));
            $q->where(function ($subQ) use ($search) {
                $terms = explode(' ', $search);
                $subQ->where('e.employee_id_number', 'like', "%{$search}%");
                $subQ->orWhere(function ($nameQuery) use ($terms) {
                    foreach ($terms as $term) {
                        $nameQuery->where(function ($termQuery) use ($term) {
                            $termQuery->where('e.first_name', 'like', "%{$term}%")
                                      ->orWhere('e.last_name', 'like', "%{$term}%");
                        });
                    }
                });
            });
        });

        $query->when($filters['work_position_id'] ?? null, function ($q, $positionId) {
            $q->where('e.work_position_id', $positionId);
        });

        $query->when($filters['team_id'] ?? null, function ($q, $teamId) {
            $q->where('e.team_id', $teamId);
        });

        $query->when($filters['department_id'] ?? null, function ($q, $departmentId) {
            $q->where('e.department_id', $departmentId);
        });

        $query->when($filters['work_location_id'] ?? null, function ($q, $locationId) {
            $q->where('e.work_location_id', $locationId);
        });

        $query->when($filters['work_employee_status_id'] ?? null, function ($q, $statusId) {
            $q->where('e.work_employee_status_id', $statusId);
        });

        $query->when($filters['supervisor_employee_id'] ?? null, function ($q, $supervisorId) {
            $supervisor = Employee::find($supervisorId);
            if ($supervisor) {
                $matrixRules = \App\Modules\Organization\Models\PositionHierarchyMatrix::where('supervisor_work_position_id', $supervisor->work_position_id)
                    ->where(function($mq) use ($supervisor) {
                        $mq->whereNull('work_location_id')
                          ->orWhere('work_location_id', $supervisor->work_location_id);
                    })
                    ->get(['department_id', 'work_position_id', 'work_location_id']);

                if ($matrixRules->isNotEmpty()) {
                    $q->where(function ($sq) use ($matrixRules, $supervisor) {
                        foreach ($matrixRules as $rule) {
                            $sq->orWhere(function ($ssq) use ($rule, $supervisor) {
                                $ssq->where('e.department_id', $rule->department_id)
                                   ->where('e.work_position_id', $rule->work_position_id);
                                   
                                if ($rule->work_location_id !== null) {
                                    $ssq->where('e.work_location_id', $supervisor->work_location_id);
                                }
                            });
                        }
                    });

                    if ($supervisor->team_id) {
                        $q->where('e.team_id', $supervisor->team_id);
                    }
                } else {
                    $q->whereRaw('1 = 0');
                }
            }
        });

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Map standard objects to match exactly what EmployeeResource expects natively
        $paginator->getCollection()->transform(function ($item) {
            return (object) [
                'id' => $item->id,
                'nik' => $item->employee_id_number,
                'employee_id_number' => $item->employee_id_number,
                'id_card_number' => $item->id_card_number,
                'full_name' => trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? '')),
                'first_name' => $item->first_name,
                'last_name' => $item->last_name,
                'initial_name' => $item->initial_name,
                'department' => $item->department_id ? (object) [
                    'id' => $item->department_id,
                    'name' => $item->department_name,
                ] : null,
                'position' => $item->position_id ? (object) [
                    'id' => $item->position_id,
                    'name' => $item->position_name,
                ] : null,
                'work_location' => $item->work_location_id ? (object) [
                    'id' => $item->work_location_id,
                    'name' => $item->work_location_name,
                ] : null,
                'work_employee_status' => $item->work_employee_status_id ? (object) [
                    'id' => $item->work_employee_status_id,
                    'name' => $item->work_employee_status_name,
                ] : null,
                'user_employee' => (object) [
                    'user' => (object) [
                        'email' => $item->user_email,
                    ]
                ],
                'company_email' => $item->company_email,
                'profile_url' => \App\Services\StorageService::url($item->avatar),
                'join_date' => $item->join_date,
                'resign_date' => $item->resign_date,
                'phone_number' => $item->phone_number,
                'handphone' => $item->handphone,
                'current_address' => $item->current_address,
                'place_birth' => $item->place_birth,
                'date_birth' => $item->date_birth,
                'annual_leave_2' => $item->annual_leave_2,
                'annual_leave_3' => $item->annual_leave_3,
                'supervisor' => $item->supervisor_employee_id ? (object) [
                    'employee' => (object) [
                        'id' => $item->supervisor_employee_id,
                        'full_name' => trim(($item->supervisor_first_name ?? '') . ' ' . ($item->supervisor_last_name ?? '')),
                        'nik' => $item->supervisor_nik,
                    ]
                ] : null,
            ];
        });

        return $paginator;
    }

    /**
     * Get employee counts summary grouped by work status.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getSummary()
    {
        return DB::table('work_employee_statuses as wes')
            ->leftJoin('employees as e', function ($join) {
                $join->on('wes.id', '=', 'e.work_employee_status_id')
                     ->whereNull('e.deleted_at');
            })
            ->select('wes.id', 'wes.name', DB::raw('COUNT(e.id) as count'))
            ->groupBy('wes.id', 'wes.name')
            ->get()
            ->map(function ($status) {
                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'count' => (int) $status->count
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
            ->orderByDesc(DB::raw('CAST(employee_id_number AS UNSIGNED)'))
            ->first();
    }
}
