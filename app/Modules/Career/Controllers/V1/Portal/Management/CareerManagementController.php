<?php

namespace App\Modules\Career\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Career\Models\Career;
use App\Modules\Career\Requests\CareerIndexRequest;
use App\Modules\Career\Requests\CareerRequest;
use App\Modules\Career\Resources\CareerResource;
use App\Modules\Career\Services\CareerService;
use App\Modules\Career\Repositories\CareerRepository;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Career
 * @subgroup Management Portal
 */
class CareerManagementController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected CareerService $service,
        protected CareerRepository $repository
    ) {}

    /**
     * List careers.
     * 
     * Get a paginated list of career changes.
     */
    public function index(CareerIndexRequest $request): JsonResponse
    {
        $careers = $this->repository->getPaginated(
            $request->validated(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            CareerResource::collection($careers)->response()->getData(true),
            'Careers retrieved successfully'
        );
    }

    /**
     * Create career.
     * 
     * Store a new career change request.
     */
    public function store(CareerRequest $request): JsonResponse
    {
        $career = $this->service->createCareer($request->validated());

        return $this->successResponse(
            new CareerResource($career),
            'Career request created successfully',
            201
        );
    }

    /**
     * Get career.
     * 
     * Get detailed information about a specific career change.
     */
    public function show(Career $career): JsonResponse
    {
        return $this->successResponse(
            new CareerResource($career->load([
                'employee', 
                'careerType', 
                'beforeWorkPosition', 
                'afterWorkPosition',
                'beforeDepartment',
                'afterDepartment',
                'beforeTeam',
                'afterTeam'
            ])),
            'Career details retrieved'
        );
    }

    /**
     * Update career.
     * 
     * Update the details of an existing career change request.
     */
    public function update(CareerRequest $request, Career $career): JsonResponse
    {
        $updatedCareer = $this->service->updateCareer($career, $request->validated());

        return $this->successResponse(
            new CareerResource($updatedCareer),
            'Career request updated successfully'
        );
    }

    /**
     * Delete career.
     * 
     * Remove a career change request.
     */
    public function destroy(Career $career): JsonResponse
    {
        $this->service->deleteCareer($career);

        return $this->successResponse(null, 'Career request deleted successfully');
    }
}
