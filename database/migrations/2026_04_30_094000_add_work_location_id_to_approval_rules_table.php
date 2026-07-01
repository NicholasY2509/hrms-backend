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
        Schema::table('approval_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('work_location_id')->nullable()->after('work_position_id');
            $table->foreign('work_location_id')->references('id')->on('work_locations')->onDelete('set null');
            $table->index('work_location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_rules', function (Blueprint $table) {
            $table->dropForeign(['work_location_id']);
            $table->dropColumn('work_location_id');
        });
    }
};
