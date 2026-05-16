<?php

namespace App\Modules\ApprovalWorkflow\Controllers\V1\Portal\Management;

use App\Http\Controllers\Controller;
use App\Modules\ApprovalWorkflow\Requests\V1\Portal\Management\GetApprovalActionRequest;
use App\Modules\ApprovalWorkflow\Resources\V1\ApprovalActionCollection;
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
     */
    public function index(GetApprovalActionRequest $request): JsonResponse
    {
        $result = $this->service->getMyPendingApprovals($request->validated());

        return $this->successResponse(
            (new ApprovalActionCollection($result['data'], $result['counts'], 'pending'))->response()->getData(true),
            'Daftar persetujuan berhasil diambil.'
        );
    }

    /**
     * List all upcoming requests (future steps) for the authenticated manager.
     */
    public function upcoming(GetApprovalActionRequest $request): JsonResponse
    {
        $result = $this->service->getMyUpcomingApprovals($request->validated());

        return $this->successResponse(
            (new ApprovalActionCollection($result['data'], $result['counts'], 'upcoming'))->response()->getData(true),
            'Daftar pengajuan mendatang berhasil diambil.'
        );
    }

    /**
     * List all ongoing requests (any step) for the authenticated manager.
     */
    public function ongoing(GetApprovalActionRequest $request): JsonResponse
    {
        $result = $this->service->getMyOngoingApprovals($request->validated());

        return $this->successResponse(
            (new ApprovalActionCollection($result['data'], $result['counts'], 'ongoing'))->response()->getData(true),
            'Daftar pengajuan berjalan berhasil diambil.'
        );
    }

    /**
     * List history of finalized requests.
     */
    public function history(GetApprovalActionRequest $request): JsonResponse
    {
        $result = $this->service->getMyHistoryApprovals($request->validated());

        return $this->successResponse(
            (new ApprovalActionCollection($result['data'], $result['counts'], 'history'))->response()->getData(true),
            'Riwayat pengajuan berhasil diambil.'
        );
    }


    /**
     * Approve a request.
     * 
     * @bodyParam notes string optional Approval notes.
     * @bodyParam attachment file optional Evidence for approval.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'attachment' => 'nullable|file|max:5120',
        ]);

        try {
            $approvalRequest = $this->service->approve(
                $id, 
                $request->input('notes'),
                $request->file('attachment')
            );
            return $this->successResponse(new ApprovalRequestResource($approvalRequest), 'Pengajuan berhasil disetujui.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Reject a request.
     * 
     * @bodyParam notes string required Reason for rejection.
     * @bodyParam attachment file optional Evidence for rejection.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'notes' => 'required|string',
            'attachment' => 'nullable|file|max:5120',
        ]);

        try {
            $approvalRequest = $this->service->reject(
                $id, 
                $request->input('notes'),
                $request->file('attachment')
            );
            return $this->successResponse(new ApprovalRequestResource($approvalRequest), 'Pengajuan telah ditolak.');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
