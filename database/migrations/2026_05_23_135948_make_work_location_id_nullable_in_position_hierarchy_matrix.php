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
        Schema::table('position_hierarchy_matrix', function (Blueprint $table) {
            $table->dropForeign('position_hierarchy_matrix_work_location_id_foreign');
            $table->dropUnique('pos_hier_matrix_unique');
            $table->unsignedBigInteger('work_location_id')->nullable()->change();
            
            $table->unique(['work_location_id', 'department_id', 'work_position_id'], 'pos_hier_matrix_unique');
            $table->foreign('work_location_id')->references('id')->on('work_locations')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('position_hierarchy_matrix', function (Blueprint $table) {
            $table->dropForeign('position_hierarchy_matrix_work_location_id_foreign');
            $table->dropUnique('pos_hier_matrix_unique');
            $table->unsignedBigInteger('work_location_id')->nullable(false)->change();
            
            $table->unique(['work_location_id', 'department_id', 'work_position_id'], 'pos_hier_matrix_unique');
            $table->foreign('work_location_id')->references('id')->on('work_locations')->cascadeOnDelete();
        });
    }
};
