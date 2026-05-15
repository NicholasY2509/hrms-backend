<?php

namespace App\Modules\UnpaidLeave\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\UnpaidLeave\Requests\V1\AutoInsertSundaysRequest;
use App\Modules\UnpaidLeave\Requests\V1\GetHolidayRequest;
use App\Modules\UnpaidLeave\Requests\V1\StoreHolidayRequest;
use App\Modules\UnpaidLeave\Requests\V1\UpdateHolidayRequest;
use App\Modules\UnpaidLeave\Resources\V1\HolidayResource;
use App\Modules\UnpaidLeave\Services\HolidayService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Unpaid Leave
 * @subgroup Management Portal
 * 
 * Endpoints for HR/Management to manage public holidays.
 */
class HolidayManagementController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected HolidayService $holidayService
    ) {}

    /**
     * List all holidays with pagination and filtering.
     */
    public function index(GetHolidayRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $perPage = $request->query('per_page', 15);

        $holidays = $this->holidayService->getPaginatedHolidays($filters, $perPage);

        return $this->successResponse(
            HolidayResource::collection($holidays)->response()->getData(true),
            'Holidays retrieved successfully.'
        );
    }

    /**
     * Store a new holiday.
     * 
     * @bodyParam name string required The name of the holiday. Example: New Year
     * @bodyParam date string required The date of the holiday. Example: 2024-01-01
     * @bodyParam description string Optional description.
     * @bodyParam is_half_day boolean Whether it's a half-day holiday.
     */
    public function store(StoreHolidayRequest $request): JsonResponse
    {
        $holiday = $this->holidayService->createHoliday($request->validated());
        return $this->successResponse(
            new HolidayResource($holiday),
            'Holiday created successfully.',
            201
        );
    }

    /**
     * Show holiday detail.
     */
    public function show(int $id): JsonResponse
    {
        $holiday = $this->holidayService->getHoliday($id);
        if (!$holiday) {
            return $this->errorResponse('Holiday not found.', 404);
        }
        return $this->successResponse(
            new HolidayResource($holiday),
            'Holiday detail retrieved.'
        );
    }

    /**
     * Update a holiday.
     */
    public function update(UpdateHolidayRequest $request, int $id): JsonResponse
    {
        $updated = $this->holidayService->updateHoliday($id, $request->validated());
        if (!$updated) {
            return $this->errorResponse('Holiday not found or update failed.', 404);
        }
        
        $holiday = $this->holidayService->getHoliday($id);
        return $this->successResponse(
            new HolidayResource($holiday),
            'Holiday updated successfully.'
        );
    }

    /**
     * Delete a holiday.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->holidayService->deleteHoliday($id);
        if (!$deleted) {
            return $this->errorResponse('Holiday not found.', 404);
        }
        return $this->successResponse(null, 'Holiday deleted successfully.');
    }

    /**
     * Auto insert Sundays as "Hari Minggu" within a date range.
     * 
     * @bodyParam start_date string required Start date (YYYY-MM-DD).
     * @bodyParam end_date string required End date (YYYY-MM-DD).
     */
    public function autoInsertSundays(AutoInsertSundaysRequest $request): JsonResponse
    {
        $count = $this->holidayService->autoInsertSundays(
            $request->start_date,
            $request->end_date
        );

        return $this->successResponse(
            ['inserted_count' => $count],
            "Successfully inserted {$count} Sundays as 'Hari Minggu'."
        );
    }
}
