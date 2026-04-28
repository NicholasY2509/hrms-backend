<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Modules\ApprovalWorkflow\Models\ApprovalScheme;
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

            // 2. Get unique primary records that have approvals
            $primaryIds = DB::table($config['approval_table'])
                ->distinct()
                ->pluck($config['fk']);

            $this->info("Found " . $primaryIds->count() . " records with legacy approvals.");

            foreach ($primaryIds as $id) {
                // Check if already migrated
                $exists = ApprovalRequest::where('approvable_type', $config['model'])
                    ->where('approvable_id', $id)
                    ->exists();

                if ($exists) {
                    $this->comment("Skipping Record ID: {$id} (Already migrated)");
                    continue;
                }

                // Get legacy steps
                $legacySteps = DB::table($config['approval_table'])
                    ->where($config['fk'], $id)
                    ->orderBy('id')
                    ->get();

                if ($isDryRun) {
                    $this->line("Would migrate Record ID: {$id} with " . $legacySteps->count() . " steps.");
                    continue;
                }

                // 3. Create Approval Request
                // We'll infer status based on the latest step or overall logic
                $finalStatus = 'pending';
                if ($legacySteps->contains('status', 'Rejected')) {
                    $finalStatus = 'rejected';
                } elseif ($legacySteps->every('status', 'Approved')) {
                    $finalStatus = 'approved';
                }

                $request = ApprovalRequest::create([
                    'approval_rule_id' => 1, // Default rule placeholder
                    'approvable_type' => $config['model'],
                    'approvable_id' => $id,
                    'status' => strtolower($finalStatus),
                    'current_step_sequence' => $legacySteps->count() + 1, // Mark as finished if all done
                ]);

                // 4. Create Steps
                foreach ($legacySteps as $index => $lStep) {
                    ApprovalRequestStep::create([
                        'approval_request_id' => $request->id,
                        'approver_type' => 'user',
                        'approver_id' => $lStep->employee_id,
                        'sequence' => $index + 1,
                        'status' => strtolower($lStep->status),
                        'notes' => $lStep->note ?? null,
                        'actioned_by' => $lStep->employee_id,
                        'actioned_at' => $lStep->updated_at,
                        'created_at' => $lStep->created_at,
                        'updated_at' => $lStep->updated_at,
                    ]);
                }

                $this->info("Successfully migrated Record ID: {$id}");
            }
        }

        $this->info("--------------------------------------------------");
        $this->info("Migration completed!");
    }
}
