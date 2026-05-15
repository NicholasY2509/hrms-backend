<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\ApprovalWorkflow\Events\ApprovalStepActionable;
use App\Modules\ApprovalWorkflow\Events\ApprovalRequestFinished;
use App\Modules\ApprovalWorkflow\Events\ApprovalRequestCreated;
use App\Modules\Notifications\Notifications\BaseNotification;
use App\Modules\Employee\Models\Employee;
use Illuminate\Support\Facades\Log;

class ApprovalNotificationListener
{
    /**
     * Handle Approval Step Actionable event.
     */
    public function handleStepActionable(ApprovalStepActionable $event): void
    {
        $step = $event->step;
        $request = $step->request;
        $approverIds = $step->getResolvedApproverIds();
        
        $employees = Employee::with('user')->whereIn('id', (array) $approverIds)->get();

        foreach ($employees as $employee) {
            if ($employee->user) {
                $employee->user->notify(new BaseNotification([
                    'title' => 'Persetujuan Diperlukan',
                    'message' => "Anda memiliki permintaan persetujuan baru dari {$request->approvable->employee->full_name}.",
                    'type' => 'approval_required',
                    'action_url' => $this->getActionUrl($request, true), // Management mode
                    'request_id' => $request->id
                ]));
            }
        }
    }

    /**
     * Handle Approval Request Finished event.
     */
    public function handleRequestFinished(ApprovalRequestFinished $event): void
    {
        $request = $event->request;
        $employee = $request->approvable->employee;

        if ($employee && $employee->user) {
            $statusLabel = $event->status === 'approved' ? 'Disetujui' : 'Ditolak';
            $message = "Pengajuan {$this->getRequestTypeName($request)} Anda telah {$statusLabel}.";
            
            if ($event->status === 'rejected' && $event->notes) {
                $message .= " Alasan: {$event->notes}";
            }

            $employee->user->notify(new BaseNotification([
                'title' => "Pengajuan {$statusLabel}",
                'message' => $message,
                'type' => "approval_{$event->status}",
                'action_url' => $this->getActionUrl($request, false), // Employee mode
                'request_id' => $request->id
            ]));
        }
    }

    /**
     * Handle Approval Request Created event.
     */
    public function handleRequestCreated(ApprovalRequestCreated $event): void
    {
        $request = $event->request;
        $employee = $request->approvable->employee;

        if ($employee && $employee->user) {
            $employee->user->notify(new BaseNotification([
                'title' => 'Pengajuan Berhasil',
                'message' => "Pengajuan {$this->getRequestTypeName($request)} Anda telah berhasil dikirim dan sedang dalam proses persetujuan.",
                'type' => 'approval_submitted',
                'action_url' => $this->getActionUrl($request, false), // Employee mode
                'request_id' => $request->id
            ]));
        }
    }

    protected function getActionUrl($request, bool $isManagement = false): string
    {
        $baseSlug = match(class_basename($request->approvable_type)) {
            'UnpaidLeave' => 'unpaid-leave',
            'Overtime' => 'overtime',
            default => strtolower(class_basename($request->approvable_type))
        };

        if ($isManagement) {
            return "/management/{$baseSlug}/{$request->approvable_id}";
        }

        return "/employee/{$baseSlug}";
    }

    protected function getRequestTypeName($request): string
    {
        return match(class_basename($request->approvable_type)) {
            'UnpaidLeave' => 'Izin/Cuti',
            'Overtime' => 'Lembur',
            default => 'Persetujuan'
        };
    }
}
