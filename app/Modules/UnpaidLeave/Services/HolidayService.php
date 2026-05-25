<?php

namespace App\Modules\UnpaidLeave\Services;

use App\Modules\UnpaidLeave\Models\Holiday;
use App\Modules\UnpaidLeave\Repositories\HolidayRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

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
        return Holiday::query()
            ->filter($filters)
            ->orderBy('date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get all holidays.
     */
    public function getAllHolidays(): Collection
    {
        return Cache::rememberForever('all_holidays', function () {
            return $this->repository->all();
        });
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
        $holiday = $this->repository->create($data);
        Cache::forget('all_holidays');
        return $holiday;
    }

    /**
     * Update a holiday.
     */
    public function updateHoliday(int $id, array $data): bool
    {
        $updated = $this->repository->update($id, $data);
        if ($updated) {
            Cache::forget('all_holidays');
        }
        return $updated;
    }

    /**
     * Delete a holiday.
     */
    public function deleteHoliday(int $id): bool
    {
        $deleted = $this->repository->delete($id);
        if ($deleted) {
            Cache::forget('all_holidays');
        }
        return $deleted;
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

        if ($insertedCount > 0) {
            Cache::forget('all_holidays');
        }

        return $insertedCount;
    }
}
