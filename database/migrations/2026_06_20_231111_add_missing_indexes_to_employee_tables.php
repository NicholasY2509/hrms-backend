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
        Schema::table('employees', function (Blueprint $table) {
            $table->index('department_id');
            $table->index('work_position_id');
            $table->index('work_location_id');
            $table->index('work_employee_status_id');
            $table->index('team_id');
            $table->index('supervisor_id');
            $table->index('employee_id_number');
            $table->index('first_name');
            $table->index('last_name');
        });

        Schema::table('user_employees', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('employee_id');
        });

        Schema::table('supervisors', function (Blueprint $table) {
            $table->index('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['department_id']);
            $table->dropIndex(['work_position_id']);
            $table->dropIndex(['work_location_id']);
            $table->dropIndex(['work_employee_status_id']);
            $table->dropIndex(['team_id']);
            $table->dropIndex(['supervisor_id']);
            $table->dropIndex(['employee_id_number']);
            $table->dropIndex(['first_name']);
            $table->dropIndex(['last_name']);
        });

        Schema::table('user_employees', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['employee_id']);
        });

        Schema::table('supervisors', function (Blueprint $table) {
            $table->dropIndex(['employee_id']);
        });
    }
};
