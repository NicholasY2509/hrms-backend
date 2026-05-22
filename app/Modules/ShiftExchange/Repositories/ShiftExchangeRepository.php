<?php

namespace App\Modules\ShiftExchange\Repositories;

use App\Modules\ShiftExchange\Models\ShiftExchange;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ShiftExchangeRepository
{
    /**
     * Get paginated shift exchanges with filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return ShiftExchange::with(['employee', 'originalWorkingHour', 'requestedWorkingHour', 'exchangeWithEmployee', 'approvalRequest', 'approvalRequest.steps'])
            ->filter($filters)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get paginated shift exchanges for a specific employee.
     *
     * @param int $employeeId
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedByEmployeeId(int $employeeId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $filters['employee_id'] = $employeeId;
        return $this->getPaginated($filters, $perPage);
    }

    /**
     * Create a new shift exchange.
     *
     * @param array $data
     * @return ShiftExchange
     */
    public function create(array $data): ShiftExchange
    {
        $data['date'] = Carbon::parse($data['date'])->format('Y-m-d');

        return ShiftExchange::create($data);
    }

    /**
     * Find a shift exchange by ID.
     *
     * @param int $id
     * @return ShiftExchange
     */
    public function findOrFail(int $id): ShiftExchange
    {
        return ShiftExchange::with(['employee', 'originalWorkingHour', 'requestedWorkingHour', 'exchangeWithEmployee', 'approvalRequest', 'approvalRequest.steps'])->findOrFail($id);
    }

    /**
     * Find a shift exchange by ID for a specific employee.
     *
     * @param int $id
     * @param int $employeeId
     * @return ShiftExchange
     */
    public function findOrFailForEmployee(int $id, int $employeeId): ShiftExchange
    {
        return ShiftExchange::with(['employee', 'originalWorkingHour', 'requestedWorkingHour', 'exchangeWithEmployee', 'approvalRequest', 'approvalRequest.steps'])
            ->where(function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId)
                      ->orWhere('exchange_with_employee_id', $employeeId);
            })
            ->findOrFail($id);
    }
}
