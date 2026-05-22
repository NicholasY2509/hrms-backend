<?php

namespace App\Modules\ShiftExchange\Controllers\V1\Portal\Employee;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use App\Modules\ShiftExchange\Services\ShiftExchangeService;
use App\Modules\ShiftExchange\Requests\V1\MyShiftExchangeIndexRequest;
use App\Modules\ShiftExchange\Requests\V1\StoreShiftExchangeRequest;
use App\Modules\ShiftExchange\Resources\V1\ShiftExchangeResource;
use Illuminate\Http\JsonResponse;
use App\Modules\Attendance\Resources\AttendanceWorkingHourResource;
use Illuminate\Http\Request;

class MyShiftExchangeController extends Controller
{
    use ApiResponses;

    protected ShiftExchangeService $shiftExchangeService;

    public function __construct(ShiftExchangeService $shiftExchangeService)
    {
        $this->shiftExchangeService = $shiftExchangeService;
    }

    /**
     * Get list of my shift exchanges.
     *
     * @group Portal - Employee - Shift Exchange
     * @queryParam per_page int The number of items per page.
     * @queryParam start_date date Filter by start date.
     * @queryParam end_date date Filter by end date.
     * @queryParam is_settled boolean Filter by settled status.
     * 
     * @param MyShiftExchangeIndexRequest $request
     * @return JsonResponse
     */
    public function index(MyShiftExchangeIndexRequest $request): JsonResponse
    {
        $employeeId = $request->user()->employee->id;
        $filters = $request->validated();
        
        $shiftExchanges = $this->shiftExchangeService->getPaginatedForEmployee($employeeId, $filters, $request->input('per_page', 15));
        
        $resource = ShiftExchangeResource::collection($shiftExchanges);
        
        return $this->successResponse(
            $resource->response()->getData(true),
            'My shift exchanges retrieved successfully'
        );
    }

    /**
     * Store a new shift exchange.
     *
     * @group Portal - Employee - Shift Exchange
     * 
     * @param StoreShiftExchangeRequest $request
     * @return JsonResponse
     */
    public function store(StoreShiftExchangeRequest $request): JsonResponse
    {
        $employeeId = $request->user()->employee->id;
        
        $data = $request->validated();
        $data['employee_id'] = $employeeId;
        
        $shiftExchange = $this->shiftExchangeService->createShiftExchange($data);
        
        return $this->successResponse(
            new ShiftExchangeResource($shiftExchange),
            'Shift exchange requested successfully',
            201
        );
    }

    /**
     * Get another employee's working hour on a specific date.
     *
     * @group Portal - Employee - Shift Exchange
     * @queryParam employee_id int required The ID of the employee.
     * @queryParam date date required The date of the shift.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getEmployeeWorkingHour(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date'
        ]);

        $workingHour = $this->shiftExchangeService->getEmployeeWorkingHour(
            $request->input('employee_id'), 
            $request->input('date')
        );

        if (!$workingHour) {
            return $this->errorResponse('Working hour not found for the specified employee and date', 404);
        }

        return $this->successResponse(
            new AttendanceWorkingHourResource($workingHour),
            'Employee working hour retrieved successfully'
        );
    }
    /**
     * Show a specific shift exchange detail.
     *
     * @group Portal - Employee - Shift Exchange
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $employeeId = $request->user()->employee->id;
        
        $shiftExchange = $this->shiftExchangeService->findOrFailForEmployee($id, $employeeId);
        
        return $this->successResponse(
            new ShiftExchangeResource($shiftExchange),
            'Shift exchange detail retrieved successfully'
        );
    }
}
