<?php

namespace App\Modules\ApprovalWorkflow\Console\Commands;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\ApprovalWorkflow\Models\ApprovalRequestStep;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncSettledApprovalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval:sync-settled {--weeks=2 : Number of weeks ago to check (checks requests older than this)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync pending approval requests and steps to approved if the main model is already settled';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $weeks = (int) $this->option('weeks');
        $cutoffDate = Carbon::now()->subWeeks($weeks);

        $this->info("Checking for pending approvals where main model was created before: {$cutoffDate->toDateTimeString()}...");

        $requestCount = 0;
        $stepCount = 0;

        // Process Pending Approval Requests
        // We fetch all pending requests and then filter by the main model's creation date
        ApprovalRequest::where('status', 'pending')
            ->with(['approvable', 'steps'])
            ->chunk(100, function ($requests) use ($cutoffDate, &$requestCount, &$stepCount) {
                foreach ($requests as $request) {
                    try {
                        $model = $request->approvable;
                        
                        // Check if the model exists
                        if (!$model) {
                            continue;
                        }

                        // Rule 1: Check if main model creation date is within the threshold (below 2 weeks from now)
                        // Rule 2: Check if status is pending but main model is already settled
                        if ($model->created_at <= $cutoffDate && isset($model->settled_at) && $model->settled_at !== null) {
                            $this->syncRequest($request, $model->settled_at);
                            $requestCount++;
                        }
                    } catch (\Exception $e) {
                        $this->error("Failed to sync Request ID {$request->id}: " . $e->getMessage());
                        Log::error("SyncSettledApprovalsCommand: Failed for Request ID {$request->id}. Error: " . $e->getMessage());
                    }
                }
            });

        // Also check for orphaned pending steps where the request might already be approved
        // but some steps stayed in pending status.
        ApprovalRequestStep::where('status', 'pending')
            ->with(['request.approvable'])
            ->chunk(100, function ($steps) use ($cutoffDate, &$stepCount) {
                foreach ($steps as $step) {
                    try {
                        $request = $step->request;
                        if (!$request || !$request->approvable) continue;

                        $model = $request->approvable;
                        
                        if ($model->created_at <= $cutoffDate && isset($model->settled_at) && $model->settled_at !== null) {
                            DB::transaction(function () use ($step, $model) {
                                $step->update([
                                    'status' => 'approved',
                                    'notes' => "Auto-approved: Model already settled at {$model->settled_at} (Sync Command)",
                                    'actioned_at' => now(),
                                ]);
                            });
                            $stepCount++;
                        }
                    } catch (\Exception $e) {
                        $this->error("Failed to sync Step ID {$step->id}: " . $e->getMessage());
                    }
                }
            });

        $this->info("Sync completed.");
        $this->info("- Requests synced: {$requestCount}");
        $this->info("- Steps synced: {$stepCount}");
        
        Log::info("SyncSettledApprovalsCommand: Completed. Requests: {$requestCount}, Steps: {$stepCount}");

        return Command::SUCCESS;
    }

    /**
     * Sync the request and all its pending steps to approved.
     */
    protected function syncRequest(ApprovalRequest $request, $settledAt)
    {
        DB::transaction(function () use ($request, $settledAt) {
            // Update all pending steps
            $request->steps()->where('status', 'pending')->update([
                'status' => 'approved',
                'notes' => "Auto-approved: Model already settled at {$settledAt} (Sync Command)",
                'actioned_at' => now(),
            ]);

            // Update request
            $request->update(['status' => 'approved']);

            // Sync with parent model if necessary (though it's already settled)
            $model = $request->approvable;
            if ($model && method_exists($model, 'syncApprovalStatus')) {
                $model->syncApprovalStatus('approved');
            }

            $this->comment("Synced Request ID {$request->id} ({$request->approvable_type}) - Settled at: {$settledAt}");
            Log::info("SyncSettledApprovalsCommand: Synced Request ID {$request->id}. Settled at: {$settledAt}");
        });
    }
}
