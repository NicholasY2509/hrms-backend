<?php

namespace App\Modules\ApprovalWorkflow\Console\Commands;

use App\Modules\ApprovalWorkflow\Models\ApprovalRequest;
use App\Modules\Overtime\Models\Overtime;
use App\Modules\System\Models\SystemSetting;
use App\Modules\UnpaidLeave\Models\UnpaidLeave;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoRejectStaleRequestsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'approval:auto-reject-stale';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-reject pending approval requests older than X days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Checking for pending approval requests for auto-rejection...");
        Log::info("AutoRejectStaleRequestsCommand: Process started.");

        $defaultDays = (int) ($this->argument('days') ?: 3);
        
        // Define mapping between model class and setting key
        $typeMap = [
           Overtime::class => 'approval_overtime_auto_reject_days',
            UnpaidLeave::class => 'approval_unpaid_leave_auto_reject_days',
        ];

        // Fetch settings once
        $settings = SystemSetting::whereIn('key', array_values($typeMap))
            ->get()
            ->pluck('value', 'key');

        $pendingRequests = ApprovalRequest::with('approvable')->where('status', 'pending')->get();
        $count = 0;

        foreach ($pendingRequests as $request) {
            $settingKey = $typeMap[$request->approvable_type] ?? null;
            $limitDays = (int) ($settings->get($settingKey) ?? $defaultDays);
            
            $cutoffDate = Carbon::now()->subDays($limitDays);

            // Determine the reference date for comparison
            $referenceDate = $request->created_at;
            if ($request->approvable_type === Overtime::class) {
                $model = $request->approvable;
                if ($model && !empty($model->date)) {
                    $referenceDate = Carbon::parse($model->date);
                }
            }

            if ($referenceDate->lt($cutoffDate)) {
                try {
                    DB::transaction(function () use ($request, $limitDays) {
                        // 1. Update all pending steps to rejected
                        $request->steps()->where('status', 'pending')->update([
                            'status' => 'rejected',
                            'notes' => "Auto-rejected by system (Older than {$limitDays} days)",
                            'actioned_at' => now(),
                        ]);

                        // 2. Update the request status
                        $request->update(['status' => 'rejected']);

                        // 3. Sync with parent model
                        $model = $request->approvable;
                        if ($model && method_exists($model, 'syncApprovalStatus')) {
                            $model->syncApprovalStatus('rejected');
                        }
                    });
                    $count++;
                    $this->info("Rejected Request ID {$request->id} ({$request->approvable_type}) - Limit: {$limitDays} days");
                } catch (\Exception $e) {
                    $this->error("Failed to auto-reject Request ID {$request->id}: " . $e->getMessage());
                    Log::error("AutoRejectStaleRequestsCommand: Failed for Request ID {$request->id}. Error: " . $e->getMessage());
                }
            }
        }

        $this->info("Successfully auto-rejected {$count} requests.");
        Log::info("AutoRejectStaleRequestsCommand: Process completed. Total rejected: {$count}");

        return Command::SUCCESS;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['days', \Symfony\Component\Console\Input\InputArgument::OPTIONAL, 'Number of days before a request is considered stale'],
        ];
    }
}
