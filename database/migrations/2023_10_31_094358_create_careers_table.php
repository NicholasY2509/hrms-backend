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
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable();
            $table->foreignId('career_type_id')->nullable();
            $table->foreignId('before_employee_status_id')->nullable();
            $table->foreignId('before_work_position_id')->nullable();
            $table->foreignId('before_department_id')->nullable();
            $table->foreignId('before_work_location_id')->nullable();
            $table->foreignId('before_team_id')->nullable();
            $table->foreignId('before_supervisor_id')->nullable();
            $table->foreignId('after_employee_status_id')->nullable();
            $table->foreignId('after_work_position_id')->nullable();
            $table->foreignId('after_department_id')->nullable();
            $table->foreignId('after_work_location_id')->nullable();
            $table->foreignId('after_team_id')->nullable();
            $table->foreignId('after_supervisor_id')->nullable();
            $table->date('career_at')->nullable();
            $table->text('note')->nullable();
            $table->date('confirmed_at')->nullable();
            $table->date('settled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};
