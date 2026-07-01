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
        Schema::table('attendance_users', function (Blueprint $table) {
            $table->unsignedBigInteger('zkteco_machine_id')->nullable()->after('uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_users', function (Blueprint $table) {
            $table->dropColumn('zkteco_machine_id');
        });
    }
};
