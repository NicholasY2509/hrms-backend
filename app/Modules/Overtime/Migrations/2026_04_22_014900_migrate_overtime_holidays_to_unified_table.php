<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure the target table can handle missing time data from holiday records
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable()->change();
            $table->dateTime('finish_time')->nullable()->change();
            $table->string('total_time')->nullable()->change();
        });

        // 2. Data Migration Logic
        if (Schema::hasTable('overtime_holidays')) {
            DB::table('overtime_holidays')->orderBy('id')->chunk(100, function ($holidays) {
                foreach ($holidays as $holiday) {
                    // Map legacy holiday record to unified overtime record
                    $newOvertimeId = DB::table('overtimes')->insertGetId([
                        'date' => $holiday->date,
                        'document_no' => $holiday->document_no,
                        'type' => 'NATIONAL',
                        'employee_id' => $holiday->employee_id,
                        'department_id' => $holiday->department_id,
                        'work_position_id' => $holiday->work_position_id,
                        'estimated_overtime_price' => $holiday->estimated_overtime_price,
                        'real_overtime_price' => $holiday->overtime_price,
                        'start_time' => $holiday->date . ' 00:00:00', // Default as requested
                        'finish_time' => $holiday->date . ' 00:00:00', // Default as requested
                        'total_time' => '0',
                        'note' => $holiday->note,
                        'settled_at' => $holiday->settled_at,
                        'attachment' => $holiday->attachment,
                        'created_at' => $holiday->created_at,
                        'updated_at' => $holiday->updated_at,
                        'deleted_at' => $holiday->deleted_at,
                    ]);

                    // Migrate Approvals
                    if (Schema::hasTable('overtime_holiday_approvals')) {
                        $approvals = DB::table('overtime_holiday_approvals')
                            ->where('overtime_holiday_id', $holiday->id)
                            ->get();

                        foreach ($approvals as $approval) {
                            DB::table('overtime_approvals')->insert([
                                'overtime_id' => $newOvertimeId,
                                'employee_id' => $approval->employee_id,
                                'status' => $approval->status,
                                'note' => $approval->note,
                                'created_at' => $approval->created_at,
                                'updated_at' => $approval->updated_at,
                                'deleted_at' => $approval->deleted_at,
                            ]);
                        }
                    }

                    // Migrate Attachments from table
                    if (Schema::hasTable('overtime_holiday_attachments')) {
                        $attachments = DB::table('overtime_holiday_attachments')
                            ->where('overtime_holiday_id', $holiday->id)
                            ->get();

                        foreach ($attachments as $attachment) {
                            DB::table('overtime_attachments')->insert([
                                'overtime_id' => $newOvertimeId,
                                'path' => $attachment->path,
                                'created_at' => $attachment->created_at,
                                'updated_at' => $attachment->updated_at,
                                'deleted_at' => $attachment->deleted_at,
                            ]);
                        }
                    }

                    // Also migrate the single legacy attachment if it exists
                    if (!empty($holiday->attachment)) {
                        DB::table('overtime_attachments')->insert([
                            'overtime_id' => $newOvertimeId,
                            'path' => $holiday->attachment,
                            'created_at' => $holiday->created_at,
                            'updated_at' => $holiday->updated_at,
                            'deleted_at' => $holiday->deleted_at,
                        ]);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Remove migrated data
        DB::table('overtimes')->where('type', 'NATIONAL')->delete();

        // 2. Revert schema changes
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dateTime('start_time')->nullable(false)->change();
            $table->dateTime('finish_time')->nullable(false)->change();
            $table->string('total_time')->nullable(false)->change();
        });
    }
};
