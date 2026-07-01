<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Cleanup attendance_working_hours (Standard method for few duplicates)
        $this->cleanupSchedules();

        // 2. Cleanup zkteco_attendances (Table-Swap method for millions of rows)
        $this->cleanupMachineLogs();

        // 3. Apply Hardening and Indexing
        Schema::table('attendance_working_hours', function (Blueprint $table) {
            $table->unique(['employee_id', 'attendance_at'], 'awh_employee_date_unique');
            $table->index('attendance_at');
        });

        // The unique index for zkteco_attendances is already added during cleanupMachineLogs
        Schema::table('zkteco_attendances', function (Blueprint $table) {
            $table->index('attendance_at');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('attendance_status_id');
        });
    }

    /**
     * Safely cleanup duplicate schedules and relink attendances
     */
    private function cleanupSchedules(): void
    {
        $duplicates = DB::table('attendance_working_hours')
            ->select('employee_id', 'attendance_at')
            ->groupBy('employee_id', 'attendance_at')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $ids = DB::table('attendance_working_hours')
                ->where('employee_id', $dup->employee_id)
                ->where('attendance_at', $dup->attendance_at)
                ->orderBy('id', 'asc')
                ->pluck('id');
            
            // 1. Check for Attendance record collisions
            $existingAttendances = DB::table('attendances')
                ->whereIn('attendance_working_hour_id', $ids)
                ->orderBy('id', 'asc')
                ->get();

            if ($existingAttendances->count() > 1) {
                // We have multiple attendance records for the same day! 
                // We need to merge them into the first one and delete the rest.
                $mainAttendance = $existingAttendances->first();
                $others = $existingAttendances->slice(1);

                foreach ($others as $other) {
                    // Merge logic: Keep the earliest incoming and latest outgoing
                    $updateData = [];
                    if (!$mainAttendance->incoming_scan && $other->incoming_scan) {
                        $updateData['incoming_scan'] = $other->incoming_scan;
                    }
                    if (!$mainAttendance->outgoing_scan && $other->outgoing_scan) {
                        $updateData['outgoing_scan'] = $other->outgoing_scan;
                    }
                    
                    if (!empty($updateData)) {
                        DB::table('attendances')->where('id', $mainAttendance->id)->update($updateData);
                    }

                    // Delete the redundant attendance record
                    DB::table('attendances')->where('id', $other->id)->delete();
                }
                $keepId = $mainAttendance->attendance_working_hour_id;
            } else {
                $keepId = $existingAttendances->first()?->attendance_working_hour_id ?: $ids->first();
            }
            
            // 2. RELINK: Update any remaining attendances to point to the keepId
            DB::table('attendances')
                ->whereIn('attendance_working_hour_id', $ids)
                ->update(['attendance_working_hour_id' => $keepId]);

            // 3. DELETE: Now safe to remove the other duplicate schedules
            DB::table('attendance_working_hours')
                ->where('employee_id', $dup->employee_id)
                ->where('attendance_at', $dup->attendance_at)
                ->where('id', '!=', $keepId)
                ->delete();
        }
    }

    /**
     * High-performance cleanup for machine logs using table-swap
     */
    private function cleanupMachineLogs(): void
    {
        // Increase timeout for production volume
        DB::statement("SET innodb_lock_wait_timeout = 600");

        // Create a new clean table
        DB::statement("DROP TABLE IF EXISTS zkteco_attendances_new");
        DB::statement("CREATE TABLE zkteco_attendances_new LIKE zkteco_attendances");
        
        // Add unique key to filter during insertion
        DB::statement("ALTER TABLE zkteco_attendances_new ADD UNIQUE KEY zk_log_unique (uid, timestamp, attendance_at, zkteco_machine_id)");

        // Insert unique records only
        DB::statement("INSERT IGNORE INTO zkteco_attendances_new SELECT * FROM zkteco_attendances");

        // Swap tables
        DB::statement("DROP TABLE zkteco_attendances");
        DB::statement("RENAME TABLE zkteco_attendances_new TO zkteco_attendances");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_working_hours', function (Blueprint $table) {
            $table->dropUnique('awh_employee_date_unique');
            $table->dropIndex(['attendance_at']);
        });

        Schema::table('zkteco_attendances', function (Blueprint $table) {
            $table->dropUnique('zk_log_unique');
            $table->dropIndex(['attendance_at']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['attendance_status_id']);
        });
    }
};
