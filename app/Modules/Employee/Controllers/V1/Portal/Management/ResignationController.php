<?php

namespace App\Modules\Employee\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Models\Resignation;
use App\Modules\Employee\Requests\ResignationIndexRequest;
use App\Modules\Employee\Requests\ResignationRequest;
use App\Modules\Employee\Resources\ResignationResource;
use App\Modules\Employee\Services\ResignationService;
use App\Modules\Employee\Repositories\ResignationRepository;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Employee Management
 * @subgroup Management Portal
 */
class ResignationController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ResignationService $service,
        protected ResignationRepository $repository
    ) {}

    /**
     * List resignations.
     * 
     * Get a paginated list of resignations.
     */
    public function index(ResignationIndexRequest $request): JsonResponse
    {
        $resignations = $this->repository->getPaginated(
            $request->validated(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            ResignationResource::collection($resignations)->response()->getData(true),
            'Resignations retrieved successfully'
        );
    }

    /**
     * Create resignation.
     * 
     * Store a new resignation request.
     */
    public function store(ResignationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $attachment = $request->file('attachment');
        
        $resignation = $this->service->createResignation($data, $attachment);

        return $this->successResponse(
            new ResignationResource($resignation),
            'Resignation created successfully',
            201
        );
    }

    /**
     * Get resignation.
     * 
     * Get detailed information about a specific resignation.
     */
    public function show(Resignation $resignation): JsonResponse
    {
        return $this->successResponse(
            new ResignationResource($resignation->load(['employee'])),
            'Resignation details retrieved'
        );
    }

    /**
     * Update resignation.
     * 
     * Update the details of an existing resignation.
     */
    public function update(ResignationRequest $request, Resignation $resignation): JsonResponse
    {
        $data = $request->validated();
        $attachment = $request->file('attachment');

        $updatedResignation = $this->service->updateResignation($resignation, $data, $attachment);

        return $this->successResponse(
            new ResignationResource($updatedResignation),
            'Resignation updated successfully'
        );
    }

    /**
     * Delete resignation.
     * 
     * Remove a resignation.
     */
    public function destroy(Resignation $resignation): JsonResponse
    {
        $this->service->deleteResignation($resignation);

        return $this->successResponse(null, 'Resignation deleted successfully');
    }
}
