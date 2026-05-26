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
        Schema::create('zkteco_attendances_archive', function (Blueprint $table) {
            $table->integer('uid');
            $table->time('timestamp');
            $table->date('attendance_at')->index();
            $table->bigInteger('zkteco_machine_id');
            $table->timestamps();

            $table->unique(['uid', 'timestamp', 'attendance_at', 'zkteco_machine_id'], 'zk_archive_log_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zkteco_attendances_archive');
    }
};
