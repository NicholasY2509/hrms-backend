<?php

namespace App\Modules\Career\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Career\Models\CareerType;
use App\Modules\Career\Repositories\CareerTypeRepository;
use App\Modules\Career\Requests\V1\CareerTypeRequest;
use App\Modules\Career\Resources\V1\CareerTypeResource;
use App\Modules\Career\Services\CareerTypeService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Career
 * @subgroup Configuration
 * 
 * Endpoints for managing career types (e.g., Promotion, Demotion, Rotation).
 */
class CareerTypeController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected CareerTypeRepository $repository,
        protected CareerTypeService $service
    ) {}

    /**
     * List career types.
     * 
     * @queryParam search string Search by name.
     * @queryParam per_page int Results per page.
     */
    public function index(CareerTypeRequest $request): JsonResponse
    {
        $types = $this->repository->paginate(
            $request->only(['search']),
            $request->query('per_page', 15)
        );

        return $this->successResponse(
            CareerTypeResource::collection($types)->response()->getData(true),
            'Career types retrieved successfully.'
        );
    }

    /**
     * Store a new career type.
     * 
     * @bodyParam name string required The name of the type. Example: Promotion
     */
    public function store(CareerTypeRequest $request): JsonResponse
    {
        $type = $this->service->createType($request->validated());

        return $this->successResponse(
            new CareerTypeResource($type),
            'Career type created successfully.',
            201
        );
    }

    /**
     * Display a career type.
     */
    public function show(CareerType $career_type): JsonResponse
    {
        return $this->successResponse(
            new CareerTypeResource($career_type),
            'Career type retrieved successfully.'
        );
    }

    /**
     * Update a career type.
     * 
     * @bodyParam name string required The name of the type. Example: Promotion (Updated)
     */
    public function update(CareerTypeRequest $request, CareerType $career_type): JsonResponse
    {
        $updatedType = $this->service->updateType($career_type, $request->validated());

        return $this->successResponse(
            new CareerTypeResource($updatedType),
            'Career type updated successfully.'
        );
    }

    /**
     * Delete a career type.
     */
    public function destroy(CareerType $career_type): JsonResponse
    {
        $this->service->deleteType($career_type);

        return $this->successResponse(null, 'Career type deleted successfully.');
    }
}
