<?php

namespace App\Modules\UnpaidLeave\Services;

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
     * Get all holidays.
     */
    public function getAllHolidays(): Collection
    {
        return $this->repository->all();
    }
}
