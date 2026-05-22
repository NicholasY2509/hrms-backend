<?php

namespace App\Modules\ShiftExchange\Services;

use App\Modules\ShiftExchange\Models\ShiftExchange;
use App\Modules\ShiftExchange\Repositories\ShiftExchangeRepository;
use App\Modules\Attendance\Models\AttendanceWorkingHour;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Exception;

class ShiftExchangeService
{
    protected ShiftExchangeRepository $repository;

    public function __construct(ShiftExchangeRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get paginated shift exchanges for management.
     */
    public function getPaginatedForManagement(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    /**
     * Get paginated shift exchanges for an employee.
     */
    public function getPaginatedForEmployee(int $employeeId, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginatedByEmployeeId($employeeId, $filters, $perPage);
    }

    /**
     * Find a shift exchange by ID.
     */
    public function findOrFail(int $id): ShiftExchange
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * Find a shift exchange by ID for a specific employee.
     */
    public function findOrFailForEmployee(int $id, int $employeeId): ShiftExchange
    {
        return $this->repository->findOrFailForEmployee($id, $employeeId);
    }

    /**
     * Create a new shift exchange request.
     */
    public function createShiftExchange(array $data): ShiftExchange
    {
        return DB::transaction(function () use ($data) {
            $shiftExchange = $this->repository->create($data);

            return $shiftExchange;
        });
    }

    /**
     * Settle a shift exchange (after approval).
     */
    public function settle(int $id): ShiftExchange
    {
        return DB::transaction(function () use ($id) {
            $shiftExchange = $this->repository->findOrFail($id);

            if ($shiftExchange->settled_at) {
                throw new Exception("Pertukaran Shift Sudah ter-settle.");
            }

            // Find requester's working hour on that date
            $requesterWorkingHour = AttendanceWorkingHour::where('employee_id', $shiftExchange->employee_id)
                ->whereDate('attendance_at', $shiftExchange->date)
                ->first();

            if (!$requesterWorkingHour) {
                throw new Exception("Data Jam Kerja Karyawan tidak ditemukan pada tanggal yang diminta.");
            }

            $swapPartnerWorkingHour = null;
            if ($shiftExchange->exchange_with_employee_id) {
                $swapPartnerWorkingHour = AttendanceWorkingHour::where('employee_id', $shiftExchange->exchange_with_employee_id)
                    ->whereDate('attendance_at', $shiftExchange->date)
                    ->first();

                if (!$swapPartnerWorkingHour) {
                    throw new Exception("Data Jam Kerja Karyawan yang mau ditukar tidak ditemukan pada tanggal yang diminta.");
                }
            }

            // Swap working hours
            if ($swapPartnerWorkingHour) {
                // Partner gets requester's original shift
                $swapPartnerWorkingHour->update([
                    'working_hour_id' => $shiftExchange->original_working_hour_id
                ]);
            }

            // Requester gets the new shift
            $requesterWorkingHour->update([
                'working_hour_id' => $shiftExchange->requested_working_hour_id
            ]);

            // Mark as settled
            $shiftExchange->update(['settled_at' => now()]);

            return $shiftExchange->refresh();
        });
    }

    /**
     * Get an employee's working hour for a specific date.
     */
    public function getEmployeeWorkingHour(int $employeeId, string $date): ?AttendanceWorkingHour
    {
        return AttendanceWorkingHour::with('working_hour')
            ->where('employee_id', $employeeId)
            ->whereDate('attendance_at', $date)
            ->first();
    }
}
