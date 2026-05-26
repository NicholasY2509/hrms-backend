<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $partitions = "PARTITION p_old VALUES LESS THAN ('2026-05-01'),\n";
        
        $start = Carbon::create(2026, 5, 1);
        $end = Carbon::create(2028, 1, 1);
        
        while ($start->lt($end)) {
            $partitionName = 'p' . $start->format('Ym');
            $nextMonth = $start->copy()->addMonth()->format('Y-m-d');
            $partitions .= "    PARTITION {$partitionName} VALUES LESS THAN ('{$nextMonth}'),\n";
            $start->addMonth();
        }
        
        $partitions .= "    PARTITION p_future VALUES LESS THAN MAXVALUE";

        $sql = "
            ALTER TABLE zkteco_attendances
            PARTITION BY RANGE COLUMNS(attendance_at) (
                {$partitions}
            )
        ";

        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE zkteco_attendances REMOVE PARTITIONING');
    }
};
