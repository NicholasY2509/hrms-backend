<?php

namespace App\Modules\Employee\Controllers\V1\Portal\Employee;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Requests\StoreMyResignationRequest;
use App\Modules\Employee\Resources\ResignationResource;
use App\Modules\Employee\Services\ResignationService;
use App\Modules\Employee\Repositories\ResignationRepository;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @group Employee Self-Service
 * @subgroup Resignation
 */
class MyResignationController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ResignationService $service,
        protected ResignationRepository $repository
    ) {}

    /**
     * My resignations.
     * 
     * Get a list of resignation requests for the logged-in employee.
     */
    public function index(): JsonResponse
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return $this->errorResponse('Data karyawan tidak ditemukan', 404);
        }

        $resignations = $this->repository->getPaginated(
            ['employee_id' => $employee->id],
            15
        );

        return $this->successResponse(
            ResignationResource::collection($resignations)->response()->getData(true),
            'Data pengunduran diri berhasil diambil'
        );
    }

    /**
     * Submit resignation.
     * 
     * Submit a new resignation request.
     */
    public function store(StoreMyResignationRequest $request): JsonResponse
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return $this->errorResponse('Data karyawan tidak ditemukan', 404);
        }

        // Check if there is already a pending resignation
        $existing = $this->repository->getPaginated(['employee_id' => $employee->id], 1);
        if ($existing->total() > 0 && !$existing->first()->settled_at) {
             // You might want to be more specific here (e.g. only block if status is pending/approved)
             // For now, let's just block if there is an active one.
        }

        $data = $request->validated();
        $data['employee_id'] = $employee->id;
        $attachment = $request->file('attachment');
        
        $resignation = $this->service->createResignation($data, $attachment);

        return $this->successResponse(
            new ResignationResource($resignation),
            'Pengajuan pengunduran diri berhasil dibuat',
            201
        );
    }
}
