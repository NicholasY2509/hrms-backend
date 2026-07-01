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
        Schema::create('department_heads', function (Blueprint $table) {
            $table->id();
            // Match exact types: departments.id = INT, work_locations.id = BIGINT, employees.id = BIGINT
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('work_location_id');
            $table->unsignedBigInteger('employee_id');
            $table->timestamps();

            $table->unique(['department_id', 'work_location_id']);

            $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            $table->foreign('work_location_id')->references('id')->on('work_locations')->cascadeOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('dept_head_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedInteger('dept_head_id')->nullable()->after('name');
        });

        Schema::dropIfExists('department_heads');
    }
};
