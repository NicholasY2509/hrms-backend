<?php

namespace App\Modules\Leave\Repositories;

use App\Modules\Leave\Models\AnnualLeave;

class AnnualLeaveRepository
{
    /**
     * Create a new annual leave record.
     */
    public function create(array $data): AnnualLeave
    {
        return AnnualLeave::create($data);
    }

    /**
     * Find an annual leave record by ID.
     */
    public function find(int $id): ?AnnualLeave
    {
        return AnnualLeave::find($id);
    }

    /**
     * Get paginated annual leaves.
     */
    public function getPaginated(array $filters, int $perPage = 15)
    {
        return AnnualLeave::query()
            ->with(['employee'])
            ->filter($filters)
            ->latest('annual_leave_at')
            ->paginate($perPage);
    }

    /**
     * Count existing automated absence deductions for an employee within a date range.
     */
    public function countAutomatedDeductionsInRange(int $employeeId, string $startDate, string $endDate): int
    {
        return AnnualLeave::where('employee_id', $employeeId)
            ->where('status', 'Potong')
            ->whereBetween('annual_leave_at', [$startDate, $endDate])
            ->where('keterangan', 'like', '%Tidak Absen%')
            ->count();
    }

    /**
     * Get existing automated absence deductions for an employee within a date range.
     */
    public function getAutomatedDeductionsInRange(int $employeeId, string $startDate, string $endDate)
    {
        return AnnualLeave::with(['employee'])
            ->where('employee_id', $employeeId)
            ->where('status', 'Potong')
            ->whereBetween('annual_leave_at', [$startDate, $endDate])
            ->where('keterangan', 'like', '%Tidak Absen%')
            ->get();
    }

    /**
     * Get paginated summary of annual leaves.
     */
    public function getSummaryPaginated(array $filters, int $perPage = 15)
    {
        $year = $filters['year'] ?? date('Y');

        $query = Employee::query()
            ->select('employees.*')
            ->selectRaw("
                (SELECT COALESCE(SUM(total), 0) FROM annual_leaves WHERE employee_id = employees.id AND status = 'Potong' AND YEAR(annual_leave_at) = ?) as total_potong,
                (SELECT COALESCE(SUM(total), 0) FROM annual_leaves WHERE employee_id = employees.id AND status = 'Tambah' AND YEAR(annual_leave_at) = ?) as total_tambah,
                (SELECT balance_before FROM annual_leaves WHERE employee_id = employees.id AND YEAR(annual_leave_at) = ? ORDER BY annual_leave_at ASC, id ASC LIMIT 1) as first_balance_before
            ", [$year, $year, $year]);

        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where(function ($sq) use ($search) {
                $sq->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('employee_id_number', 'like', '%' . $search . '%');
            });
        });

        // Add sorting by name
        $query->orderBy('full_name', 'asc');

        $paginator = $query->paginate($perPage);

        // Decode the JSON string in first_balance_before
        $paginator->getCollection()->transform(function ($employee) {
            if ($employee->first_balance_before) {
                $decoded = json_decode($employee->first_balance_before, true);
                $employee->first_balance_before = $decoded;
            }
            return $employee;
        });

        return $paginator;
    }

    /**
     * Get summary of annual leaves for a specific employee.
     */
    public function getEmployeeSummary(int $employeeId, int $year)
    {
        $employee = \App\Modules\Employee\Models\Employee::query()
            ->select('employees.*')
            ->selectRaw("
                (SELECT COALESCE(SUM(total), 0) FROM annual_leaves WHERE employee_id = employees.id AND status = 'Potong' AND YEAR(annual_leave_at) = ?) as total_potong,
                (SELECT COALESCE(SUM(total), 0) FROM annual_leaves WHERE employee_id = employees.id AND status = 'Tambah' AND YEAR(annual_leave_at) = ?) as total_tambah,
                (SELECT balance_before FROM annual_leaves WHERE employee_id = employees.id AND YEAR(annual_leave_at) = ? ORDER BY annual_leave_at ASC, id ASC LIMIT 1) as first_balance_before
            ", [$year, $year, $year])
            ->findOrFail($employeeId);

        if ($employee->first_balance_before) {
            $employee->first_balance_before = json_decode($employee->first_balance_before, true);
        }

        return $employee;
    }
}
