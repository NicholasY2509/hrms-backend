<?php

namespace App\Modules\UnpaidLeave\Services;

use App\Modules\User\Models\User;
use App\Modules\Employee\Models\Employee;
use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use App\Modules\UnpaidLeave\Repositories\UnpaidLeaveApprovalRepository;
use Illuminate\Support\Facades\Log;

class UnpaidLeaveApprovalService
{
    protected UnpaidLeaveApprovalRepository $repository;

    public function __construct(UnpaidLeaveApprovalRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Generate the initial approval workflow for an unpaid leave request.
     * 
     * @param UnpaidLeave $leave
     * @return void
     */
    public function generateInitialApprovals(UnpaidLeave $leave): void
    {
        // 1. Admin HRD Approval (unless submitter is Head)
        $this->addAdminHrdApproval($leave);

        // 2. Personal/Additional Approvers
        $this->addPersonalApprovals($leave);

        // 3. Supervisor Approval
        $this->addSupervisorApproval($leave);
    }

    /**
     * Add Admin HRD approval if the user is not a Head.
     */
    protected function addAdminHrdApproval(UnpaidLeave $leave): void
    {
        /** @var User $submitter */
        $submitter = auth()->user();

        if (!$this->userHasRole($submitter, 'Head')) {
            $this->repository->create([
                'unpaid_leave_id' => $leave->id,
                'employee_id' => null,
                'role' => 'Admin HRD',
            ]);
            
            // Notify Admin HRD users
            // FIXME: Currently we cannot query users by role because roles are managed remotely 
            // and not stored in the local database. Admin HRD users will still see the request 
            // in their approval list, but proactive notification is disabled until local role mapping is implemented.
            /*
            $adminHrdUsers = User::whereHas('remote_profile', function($query) {
            })->get();
            $adminHrdUsers = User::role('Admin HRD')->get(); 

            foreach ($adminHrdUsers as $admin) {
                $admin->notify(new \App\Modules\UnpaidLeave\Notifications\UnpaidLeaveApprovalNotification($leave));
            }
            */
        }
    }

    /**
     * Add approvals from employee_leave_approvals configuration.
     */
    protected function addPersonalApprovals(UnpaidLeave $leave): void
    {
        $employee = Employee::with('employee_leave_approvals')->find($leave->employee_id);

        if (!$employee) return;

        foreach ($employee->employee_leave_approvals as $config) {
            $this->repository->create([
                'unpaid_leave_id' => $leave->id,
                'employee_id' => $config->approval_id,
                'role' => null,
            ]);

            $approver = Employee::with('user')->find($config->approval_id);
            if ($approver && $approver->user) {
                $approver->user->notify(new \App\Modules\UnpaidLeave\Notifications\UnpaidLeaveApprovalNotification($leave));
            }
        }
    }

    /**
     * Add supervisor approval if configured.
     */
    protected function addSupervisorApproval(UnpaidLeave $leave): void
    {
        $employee = Employee::with('supervisor')->find($leave->employee_id);

        if ($employee && $employee->supervisor) {
            $this->repository->create([
                'unpaid_leave_id' => $leave->id,
                'employee_id' => $employee->supervisor->employee_id,
                'role' => null,
            ]);

            $supervisor = Employee::with('user')->find($employee->supervisor->employee_id);
            if ($supervisor && $supervisor->user) {
                $supervisor->user->notify(new \App\Modules\UnpaidLeave\Notifications\UnpaidLeaveApprovalNotification($leave));
            }
        }
    }

    /**
     * Helper to check if a user has a specific role using cached remote profile.
     */
    protected function userHasRole(User $user, string $roleName): bool
    {
        $remoteProfile = $user->getAttribute('remote_profile');
        
        if (!$remoteProfile || !isset($remoteProfile['data']['roles'])) {
            return false;
        }

        $roles = collect($remoteProfile['data']['roles']);
        
        return $roles->contains(function ($role) use ($roleName) {
            return $role['name'] === $roleName;
        });
    }
}
