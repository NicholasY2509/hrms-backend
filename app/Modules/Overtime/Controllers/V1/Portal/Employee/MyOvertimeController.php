<?php

namespace App\Modules\Overtime\Controllers\V1\Portal\Employee;

use App\Http\Controllers\Controller;
use App\Modules\Overtime\Models\Overtime;
use App\Modules\Overtime\Requests\StoreOvertimeRequest;
use App\Modules\Overtime\Resources\V1\OvertimeResource;
use App\Modules\Overtime\Services\OvertimeService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @group Overtime
 * @subgroup Employee Portal
 */
class MyOvertimeController extends Controller
{
    use ApiResponses;

    protected OvertimeService $service;

    public function __construct(OvertimeService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of overtime requests.
     * 
     * @bodyParam type string optional Overtime type (UMUM, DAC, NATIONAL).
     * @bodyParam start_date date optional Filter by start date.
     * @bodyParam end_date date optional Filter by end date.
     */
    /**
     * Display a listing of overtime requests for the current user.
     */
    public function index(Request $request): JsonResponse
    {
        $employeeId = auth()->user()->user_employee?->employee_id;

        if (!$employeeId) {
            return $this->errorResponse('Employee record not found for this user.', 404);
        }

        $perPage = $request->query('per_page', 10);
        $overtimes = $this->service->getUserOvertimes($employeeId, $perPage);

        return $this->successResponse(
            OvertimeResource::collection($overtimes)->response()->getData(true),
            'Data lembur berhasil diambil.'
        );
    }

    /**
     * Store a newly created overtime request.
     * 
     * @group Overtime Management
     * @bodyParam employee_id int required ID of the employee.
     * @bodyParam date date required Overtime date.
     * @bodyParam type string required Type (UMUM, DAC, NATIONAL).
     * @bodyParam start_time string required Format HH:mm.
     * @bodyParam finish_time string required Format HH:mm.
     * @bodyParam note string optional Note for the request.
     * @bodyParam estimated_cost numeric optional Estimated cost.
     * @bodyParam attachments file[] optional Array of attachment files (jpeg, png, pdf).
     */
    public function store(StoreOvertimeRequest $request): JsonResponse
    {
        $employeeId = auth()->user()->user_employee?->employee_id;

        if (!$employeeId) {
            return $this->errorResponse('Employee record not found for this user.', 404);
        }

        $data = $request->validated();
        $data['employee_id'] = $employeeId;

        $overtime = $this->service->storeOvertime($data);

        return $this->successResponse(new OvertimeResource($overtime), 'Pengajuan lembur berhasil dibuat.', 201);
    }

    /**
     * Display the specified overtime request.
     */
    public function show(int $id): JsonResponse
    {
        $overtime = Overtime::with(['employee', 'overtime_type', 'overtime_approvals', 'overtime_attachments'])->findOrFail($id);
        
        return $this->successResponse(new OvertimeResource($overtime), 'Detail lembur berhasil diambil.');
    }

    /**
     * Settle (Close) an overtime request.
     * 
     * @bodyParam realization_cost numeric required The actual cost to be paid.
     */
    public function settle(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'realization_cost' => 'required|numeric'
        ]);

        $overtime = Overtime::findOrFail($id);
        $settledOvertime = $this->service->settleOvertime($overtime, $request->realization_cost);

        return $this->successResponse(new OvertimeResource($settledOvertime), 'Data lembur berhasil ditutup.');
    }
}
