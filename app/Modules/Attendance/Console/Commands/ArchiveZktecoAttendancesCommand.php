<?php

namespace App\Modules\Attendance\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ArchiveZktecoAttendancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:archive-zkteco {--months=6 : Number of months to keep in the main table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archives old zkteco attendances and manages database partitions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $monthsToKeep = (int) $this->option('months');
        $cutoffDate = Carbon::now()->startOfMonth()->subMonths($monthsToKeep);
        
        $this->info("Starting Zkteco Attendances archiving process.");
        $this->info("Cutoff Date: " . $cutoffDate->format('Y-m-d'));

        try {
            $this->archiveOldPartitions($cutoffDate);
            $this->manageFuturePartitions();
            $this->info("Process completed successfully.");
        } catch (\Exception $e) {
            $this->error("Error during archiving: " . $e->getMessage());
            Log::error("ArchiveZktecoAttendancesCommand Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function archiveOldPartitions(Carbon $cutoffDate)
    {
        // Get all partitions
        $partitions = DB::select("
            SELECT PARTITION_NAME, PARTITION_DESCRIPTION 
            FROM information_schema.PARTITIONS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'zkteco_attendances' 
            AND PARTITION_NAME IS NOT NULL
        ");

        foreach ($partitions as $partition) {
            $partitionName = $partition->PARTITION_NAME;
            $description = trim($partition->PARTITION_DESCRIPTION, "'");

            if ($partitionName === 'p_future' || $partitionName === 'p_old') {
                continue;
            }

            // Extract the date from description (e.g. '2026-06-01')
            // This date is the exclusive upper bound of the partition.
            if ($description && strtotime($description)) {
                $partitionEndDate = Carbon::parse($description);
                
                // If the partition's end date is <= our cutoff date, it means all data in it is older than the cutoff
                if ($partitionEndDate->lte($cutoffDate)) {
                    $this->info("Archiving partition {$partitionName} (Data older than {$partitionEndDate->format('Y-m-d')})");

                    // 1. Copy data to archive
                    // We calculate the start date for the partition (usually 1 month prior to end date)
                    $startDate = $partitionEndDate->copy()->subMonth()->format('Y-m-d');
                    
                    $this->info("Copying data from {$startDate} to {$partitionEndDate->format('Y-m-d')}...");
                    $inserted = DB::insert("
                        INSERT INTO zkteco_attendances_archive (uid, timestamp, attendance_at, zkteco_machine_id, created_at, updated_at)
                        SELECT uid, timestamp, attendance_at, zkteco_machine_id, created_at, updated_at
                        FROM zkteco_attendances
                        WHERE attendance_at >= ? AND attendance_at < ?
                    ", [$startDate, $partitionEndDate->format('Y-m-d')]);

                    $this->info("Successfully copied records.");

                    // 2. Drop the partition from main table
                    $this->info("Dropping partition {$partitionName}...");
                    DB::statement("ALTER TABLE zkteco_attendances DROP PARTITION {$partitionName}");
                    $this->info("Partition {$partitionName} dropped.");
                }
            }
        }
    }

    protected function manageFuturePartitions()
    {
        $this->info("Checking future partitions...");
        
        // We want to ensure partitions exist up to 6 months into the future
        $targetDate = Carbon::now()->addMonths(6)->startOfMonth();
        
        $partitions = DB::select("
            SELECT PARTITION_NAME 
            FROM information_schema.PARTITIONS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'zkteco_attendances' 
            AND PARTITION_NAME IS NOT NULL
        ");
        
        $existingPartitions = collect($partitions)->pluck('PARTITION_NAME')->toArray();
        
        // Start from current month
        $current = Carbon::now()->startOfMonth();
        
        while ($current->lt($targetDate)) {
            $partitionName = 'p' . $current->format('Ym');
            $nextMonth = $current->copy()->addMonth()->format('Y-m-d');
            
            if (!in_array($partitionName, $existingPartitions)) {
                $this->info("Creating future partition: {$partitionName}");
                
                // Reorganize p_future into the new partition + p_future
                DB::statement("
                    ALTER TABLE zkteco_attendances REORGANIZE PARTITION p_future INTO (
                        PARTITION {$partitionName} VALUES LESS THAN ('{$nextMonth}'),
                        PARTITION p_future VALUES LESS THAN MAXVALUE
                    )
                ");
            }
            
            $current->addMonth();
        }
    }
}
