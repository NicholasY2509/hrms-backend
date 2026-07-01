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
        Schema::create('position_hierarchy_matrix', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_location_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('work_position_id');
            $table->unsignedBigInteger('supervisor_work_position_id');
            $table->timestamps();
            
            $table->foreign('work_location_id')->references('id')->on('work_locations')->cascadeOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            $table->foreign('work_position_id')->references('id')->on('work_positions')->cascadeOnDelete();
            $table->foreign('supervisor_work_position_id')->references('id')->on('work_positions')->cascadeOnDelete();

            // Unique constraint to avoid duplicate rules for the same combination
            $table->unique(['work_location_id', 'department_id', 'work_position_id'], 'pos_hier_matrix_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_hierarchy_matrix');
    }
};
