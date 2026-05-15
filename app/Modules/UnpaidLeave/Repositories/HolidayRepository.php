<?php

namespace App\Modules\UnpaidLeave\Repositories;

use App\Modules\UnpaidLeave\Models\Holiday;
use Illuminate\Database\Eloquent\Collection;

class HolidayRepository
{
    /**
     * Get holidays within a date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getBetweenDates(string $startDate, string $endDate): Collection
    {
        return Holiday::betweenDates($startDate, $endDate)->get();
    }

    /**
     * Get all holidays.
     */
    public function all(): Collection
    {
        return Holiday::all();
    }

    /**
     * Find a holiday by ID.
     */
    public function find(int $id): ?Holiday
    {
        return Holiday::find($id);
    }

    /**
     * Create a new holiday.
     */
    public function create(array $data): Holiday
    {
        return Holiday::create($data);
    }

    /**
     * Update a holiday.
     */
    public function update(int $id, array $data): bool
    {
        $holiday = $this->find($id);
        if (!$holiday) {
            return false;
        }
        return $holiday->update($data);
    }

    /**
     * Delete a holiday.
     */
    public function delete(int $id): bool
    {
        $holiday = $this->find($id);
        if (!$holiday) {
            return false;
        }
        return $holiday->delete();
    }
}
