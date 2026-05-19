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
        // First, clean up existing duplicates before adding the unique index
        $duplicates = \Illuminate\Support\Facades\DB::table('zkteco_users')
            ->select('uid', 'zkteco_machine_id')
            ->groupBy('uid', 'zkteco_machine_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $latest = \Illuminate\Support\Facades\DB::table('zkteco_users')
                ->where('uid', $duplicate->uid)
                ->where('zkteco_machine_id', $duplicate->zkteco_machine_id)
                ->orderByDesc('updated_at')
                ->first();

            if ($latest) {
                // Delete all rows for this uid and machine combination
                \Illuminate\Support\Facades\DB::table('zkteco_users')
                    ->where('uid', $duplicate->uid)
                    ->where('zkteco_machine_id', $duplicate->zkteco_machine_id)
                    ->delete();
                
                // Re-insert only the latest one
                \Illuminate\Support\Facades\DB::table('zkteco_users')->insert((array) $latest);
            }
        }

        Schema::table('zkteco_users', function (Blueprint $table) {
            $table->unique(['uid', 'zkteco_machine_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zkteco_users', function (Blueprint $table) {
            $table->dropUnique(['uid', 'zkteco_machine_id']);
        });
    }
};
