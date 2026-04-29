<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Modules\ApprovalWorkflow\Models\ApprovalScheme;
use App\Modules\ApprovalWorkflow\Models\ApprovalRule;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestStep;
use Carbon\Carbon;

class MigrateLegacyApprovals extends Command
{
    protected $signature = 'approvals:migrate-legacy {--force : Actually perform the migration}';
    protected $description = 'Migrate legacy approval records into the new unified approval system';

    protected $mappings = [
        'Career' => [
            'primary_table' => 'careers',
            'approval_table' => 'career_approvals',
            'fk' => 'career_id',
            'model' => 'App\Modules\Career\Models\Career',
        ],
        'UnpaidLeave' => [
            'primary_table' => 'unpaid_leaves',
            'approval_table' => 'unpaid_leave_approvals',
            'fk' => 'unpaid_leave_id',
            'model' => 'App\Modules\UnpaidLeave\Models\UnpaidLeave',
        ],
        'WarningLetter' => [
            'primary_table' => 'warning_letters',
            'approval_table' => 'warning_letter_approvals',
            'fk' => 'warning_letter_id',
            'model' => 'App\Modules\Disciplinary\Models\WarningLetter',
        ],
        'CertificateOfEmployment' => [
            'primary_table' => 'certificate_of_employments',
            'approval_table' => 'certificate_of_employment_approvals',
            'fk' => 'certificate_id',
            'model' => 'App\Modules\Employee\Models\CertificateOfEmployment',
        ],
        'PaidLeaveReversal' => [
            'primary_table' => 'paid_leave_reversals',
            'approval_table' => 'paid_leave_reversal_approvals',
            'fk' => 'paid_leave_reversal_id',
            'model' => 'App\Modules\Leave\Models\PaidLeaveReversal',
        ],
        'Overtime' => [
            'primary_table' => 'overtimes',
            'approval_table' => 'overtime_approvals',
            'fk' => 'overtime_id',
            'model' => 'App\Modules\Overtime\Models\Overtime',
        ],
    ];

    public function handle()
    {
        $isDryRun = !$this->option('force');

        if ($isDryRun) {
            $this->info("DRY RUN MODE: No changes will be made to the database.");
            $this->info("Run with --force to perform the actual migration.");
        }

        foreach ($this->mappings as $name => $config) {
            $this->line("--------------------------------------------------");
            $this->info("Processing Module: {$name}");

            // 1. Ensure Scheme exists
            $scheme = ApprovalScheme::firstOrCreate(
                ['model_class' => $config['model']],
                ['name' => $name, 'is_active' => true]
            );

            // 2. Ensure Default Rule exists for this scheme
            $rule = ApprovalRule::firstOrCreate(
                ['approval_scheme_id' => $scheme->id, 'is_default' => true],
                ['is_active' => true]
            );

            // 3. Get unique primary records that have approvals
            $primaryIds = DB::table($config['approval_table'])
                ->distinct()
                ->pluck($config['fk']);

            $this->info("Found " . $primaryIds->count() . " records with legacy approvals.");

            foreach ($primaryIds as $id) {

                // Get legacy steps
                $legacySteps = DB::table($config['approval_table'])
                    ->where($config['fk'], $id)
                    ->orderBy('id')
                    ->get();

                if ($isDryRun) {
                    $this->line("Would migrate Record ID: {$id} with " . $legacySteps->count() . " steps.");
                    continue;
                }

                // 4. Create Approval Request
                $finalStatus = 'pending';
                if ($legacySteps->contains('status', 'Rejected')) {
                    $finalStatus = 'rejected';
                } elseif ($legacySteps->every('status', 'Approved')) {
                    $finalStatus = 'approved';
                }

                // Determine current step sequence
                $currentStepSequence = 1;
                if ($finalStatus === 'approved') {
                    $currentStepSequence = $legacySteps->count() + 1;
                } elseif ($finalStatus === 'rejected') {
                    // Find the first rejected step
                    $rejectedStepIdx = $legacySteps->search(fn($s) => $s->status === 'Rejected');
                    $currentStepSequence = $rejectedStepIdx !== false ? $rejectedStepIdx + 1 : 1;
                } else {
                    // Pending - find the first pending step
                    $pendingStepIdx = $legacySteps->search(fn($s) => $s->status === 'Pending');
                    if ($pendingStepIdx !== false) {
                        $currentStepSequence = $pendingStepIdx + 1;
                    } else {
                        // All steps actioned but overall status is pending? Default to count+1 or 1
                        $currentStepSequence = $legacySteps->count() + 1;
                    }
                }

                // Get the document number from the primary table
                $primaryRecord = DB::table($config['primary_table'])
                    ->where('id', $id)
                    ->first();
                
                $referenceNumber = $primaryRecord->document_no ?? $primaryRecord->document_number ?? $id;

                $request = ApprovalRequest::updateOrCreate(
                    [
                        'approvable_type' => $config['model'],
                        'approvable_id' => $id,
                    ],
                    [
                        'approval_rule_id' => $rule->id,
                        'reference_number' => $referenceNumber,
                        'status' => strtolower($finalStatus),
                        'current_step_sequence' => $currentStepSequence,
                    ]
                );

                // 5. Create Steps
                foreach ($legacySteps as $index => $lStep) {
                    $approverType = 'user';
                    $approverId = $lStep->employee_id;

                    // Specific rule: Admin HRD
                    if (isset($lStep->role) && $lStep->role === 'Admin HRD') {
                        $approverType = 'group';
                        $approverId = 1;
                    }

                    $status = strtolower($lStep->status);

                    ApprovalRequestStep::updateOrCreate(
                        [
                            'approval_request_id' => $request->id,
                            'sequence' => $index + 1,
                        ],
                        [
                            'approver_type' => $approverType,
                            'approver_id' => $approverId,
                            'status' => $status,
                            'notes' => $lStep->note ?? null,
                            'actioned_by' => ($status !== 'pending') ? $lStep->employee_id : null,
                            'actioned_at' => ($status !== 'pending') ? $lStep->updated_at : null,
                            'created_at' => $lStep->created_at,
                            'updated_at' => $lStep->updated_at,
                        ]
                    );
                }

                $this->info("Successfully migrated Record ID: {$id}");
            }
        }

        $this->info("--------------------------------------------------");
        $this->info("Migration completed!");
    }
}
