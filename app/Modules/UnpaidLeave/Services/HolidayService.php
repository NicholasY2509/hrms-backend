<?php

namespace App\Modules\UnpaidLeave\Services;

use App\Modules\UnpaidLeave\Models\Holiday;
use App\Modules\UnpaidLeave\Repositories\HolidayRepository;
use Illuminate\Database\Eloquent\Collection;

class HolidayService
{
    public function __construct(
        protected HolidayRepository $repository
    ) {}

    /**
     * Get holidays for a specific date range.
     */
    public function getHolidaysInRange(string $startDate, string $endDate): Collection
    {
        return $this->repository->getBetweenDates($startDate, $endDate);
    }

    /**
     * Get paginated holidays.
     */
    public function getPaginatedHolidays(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Holiday::query();

        if (isset($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['start_date'])) {
            $query->where('date', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('date', '<=', $filters['end_date']);
        }

        return $query->orderBy('date', 'desc')->paginate($perPage);
    }

    /**
     * Get all holidays.
     */
    public function getAllHolidays(): Collection
    {
        return $this->repository->all();
    }

    /**
     * Get holiday detail.
     */
    public function getHoliday(int $id): ?Holiday
    {
        return $this->repository->find($id);
    }

    /**
     * Create a holiday.
     */
    public function createHoliday(array $data): Holiday
    {
        return $this->repository->create($data);
    }

    /**
     * Update a holiday.
     */
    public function updateHoliday(int $id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Delete a holiday.
     */
    public function deleteHoliday(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Auto insert Sundays as "Hari Minggu" within a date range.
     */
    public function autoInsertSundays(string $startDate, string $endDate): int
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $insertedCount = 0;

        while ($start <= $end) {
            if ($start->isSunday()) {
                // Check if holiday already exists for this date
                $exists = Holiday::where('date', $start->toDateString())->exists();
                
                if (!$exists) {
                    $this->repository->create([
                        'name' => 'Hari Minggu',
                        'date' => $start->toDateString(),
                        'is_half_day' => false,
                    ]);
                    $insertedCount++;
                }
            }
            $start->addDay();
        }

        return $insertedCount;
    }
}
