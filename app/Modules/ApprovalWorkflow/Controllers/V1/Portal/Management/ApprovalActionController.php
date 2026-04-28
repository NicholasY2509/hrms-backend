<?php

namespace App\Modules\ApprovalWorkflow\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalRequestResource;
use App\Modules\ApprovalWorkflow\Services\ApprovalActionService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Approval Workflow
 * 
 * Endpoints for managers to action (approve/reject) pending requests.
 */
class ApprovalActionController extends Controller
{
    use ApiResponses;

    protected ApprovalActionService $service;

    public function __construct(ApprovalActionService $service)
    {
        $this->service = $service;
    }

    /**
     * List all pending requests for the authenticated manager.
     * 
     * Returns a unified list of leaves, overtimes, etc.
     */
    public function index(Request $request): JsonResponse
    {
        $approvals = $this->service->getMyPendingApprovals(
            $request->input('per_page', 15)
        );

        $resource = ApprovalRequestResource::collection($approvals);

        return $this->successResponse($resource->response()->getData(true), 'Daftar persetujuan berhasil diambil.');
    }

    /**
     * Approve a request.
     * 
     * @bodyParam notes string optional Approval notes.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $approvalRequest = $this->service->approve($id, $request->input('notes'));
            return $this->successResponse($approvalRequest, 'Pengajuan berhasil disetujui.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Reject a request.
     * 
     * @bodyParam notes string required Reason for rejection.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'notes' => 'required|string'
        ]);

        try {
            $approvalRequest = $this->service->reject($id, $request->input('notes'));
            return $this->successResponse($approvalRequest, 'Pengajuan telah ditolak.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
