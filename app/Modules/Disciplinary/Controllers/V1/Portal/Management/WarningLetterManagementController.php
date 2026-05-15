<?php

namespace App\Modules\Disciplinary\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\Disciplinary\Models\WarningLetter;
use App\Modules\Disciplinary\Requests\WarningLetterIndexRequest;
use App\Modules\Disciplinary\Requests\WarningLetterRequest;
use App\Modules\Disciplinary\Resources\WarningLetterResource;
use App\Modules\Disciplinary\Services\WarningLetterService;
use App\Modules\Disciplinary\Repositories\WarningLetterRepository;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;

/**
 * @group Disciplinary
 * @subgroup Management Portal
 */
class WarningLetterManagementController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected WarningLetterService $service,
        protected WarningLetterRepository $repository
    ) {}

    /**
     * List warning letters.
     * 
     * Get a paginated list of warning letters.
     */
    public function index(WarningLetterIndexRequest $request): JsonResponse
    {
        $warningLetters = $this->repository->getPaginated(
            $request->validated(),
            $request->input('per_page', 15)
        );

        return $this->successResponse(
            WarningLetterResource::collection($warningLetters)->response()->getData(true),
            'Warning letters retrieved successfully'
        );
    }

    /**
     * Create warning letter.
     * 
     * Store a new warning letter request.
     */
    public function store(WarningLetterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $attachment = $request->file('attachment');
        
        $warningLetter = $this->service->createWarningLetter($data, $attachment);

        return $this->successResponse(
            new WarningLetterResource($warningLetter),
            'Warning letter created successfully',
            201
        );
    }

    /**
     * Get warning letter.
     * 
     * Get detailed information about a specific warning letter.
     */
    public function show(WarningLetter $warningLetter): JsonResponse
    {
        return $this->successResponse(
            new WarningLetterResource($warningLetter->load([
                'employee', 
                'warning_letter_type',
                'approvalRequest.steps'
            ])),
            'Warning letter details retrieved'
        );
    }

    /**
     * Update warning letter.
     * 
     * Update the details of an existing warning letter.
     */
    public function update(WarningLetterRequest $request, WarningLetter $warningLetter): JsonResponse
    {
        $data = $request->validated();
        $attachment = $request->file('attachment');

        $updatedWarningLetter = $this->service->updateWarningLetter($warningLetter, $data, $attachment);

        return $this->successResponse(
            new WarningLetterResource($updatedWarningLetter),
            'Warning letter updated successfully'
        );
    }

    /**
     * Delete warning letter.
     * 
     * Remove a warning letter.
     */
    public function destroy(WarningLetter $warningLetter): JsonResponse
    {
        $this->service->deleteWarningLetter($warningLetter);

        return $this->successResponse(null, 'Warning letter deleted successfully');
    }

    /**
     * Settle warning letter.
     * 
     * Finalize the warning letter.
     */
    public function settle(WarningLetter $warningLetter): JsonResponse
    {
        $settledWarningLetter = $this->service->settle($warningLetter);

        return $this->successResponse(
            new WarningLetterResource($settledWarningLetter),
            'Warning letter finalized successfully'
        );
    }
}
