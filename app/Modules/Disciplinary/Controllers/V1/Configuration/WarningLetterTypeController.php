<?php

namespace App\Modules\Disciplinary\Controllers\V1\Configuration;

use App\Http\Controllers\Controller;
use App\Modules\Disciplinary\Models\WarningLetterType;
use App\Modules\Disciplinary\Repositories\WarningLetterTypeRepository;
use App\Modules\Disciplinary\Requests\V1\WarningLetterTypeRequest;
use App\Modules\Disciplinary\Resources\V1\WarningLetterTypeResource;
use App\Modules\Disciplinary\Services\WarningLetterTypeService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Disciplinary
 * @subgroup Configuration
 * 
 * Endpoints for managing types of warning letters.
 */
class WarningLetterTypeController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected WarningLetterTypeRepository $repository,
        protected WarningLetterTypeService $service
    ) {}

    /**
     * List warning letter types.
     * 
     * @queryParam search string Search by name.
     * @queryParam per_page int Results per page.
     */
    public function index(WarningLetterTypeRequest $request): JsonResponse
    {
        $types = $this->repository->paginate(
            $request->only(['search']),
            $request->query('per_page', 15)
        );

        return $this->successResponse(
            WarningLetterTypeResource::collection($types)->response()->getData(true),
            'Warning letter types retrieved successfully.'
        );
    }

    /**
     * Store a new warning letter type.
     * 
     * @bodyParam name string required The name of the type. Example: SP 1
     * @bodyParam resigned boolean Is this related to resignation? Example: false
     */
    public function store(WarningLetterTypeRequest $request): JsonResponse
    {
        $type = $this->service->createType($request->validated());

        return $this->successResponse(
            new WarningLetterTypeResource($type),
            'Warning letter type created successfully.',
            201
        );
    }

    /**
     * Display a warning letter type.
     */
    public function show(WarningLetterType $warning_letter_type): JsonResponse
    {
        return $this->successResponse(
            new WarningLetterTypeResource($warning_letter_type),
            'Warning letter type retrieved successfully.'
        );
    }

    /**
     * Update a warning letter type.
     * 
     * @bodyParam name string required The name of the type. Example: SP 2
     * @bodyParam resigned boolean Is this related to resignation? Example: false
     */
    public function update(WarningLetterTypeRequest $request, WarningLetterType $warning_letter_type): JsonResponse
    {
        $updatedType = $this->service->updateType($warning_letter_type, $request->validated());

        return $this->successResponse(
            new WarningLetterTypeResource($updatedType),
            'Warning letter type updated successfully.'
        );
    }

    /**
     * Delete a warning letter type.
     */
    public function destroy(WarningLetterType $warning_letter_type): JsonResponse
    {
        $this->service->deleteType($warning_letter_type);

        return $this->successResponse(null, 'Warning letter type deleted successfully.');
    }
}
