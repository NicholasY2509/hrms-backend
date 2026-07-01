<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id_number')->nullable();
            $table->string('id_card_number')->nullable();
            $table->string('full_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('avatar')->nullable();
            $table->foreignId('work_position_id')->nullable();
            $table->foreignId('work_location_id')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->foreignId('employee_status_id')->nullable();
            $table->foreignId('work_employee_status_id')->nullable();
            $table->foreignId('team_id')->nullable();
            $table->string('initial_name')->nullable();
            $table->string('place_birth')->nullable();
            $table->date('date_birth')->nullable();
            $table->foreignId('marital_status_id')->nullable();
            $table->foreignId('religion_id')->nullable();
            $table->foreignId('blood_group_id')->nullable();
            $table->foreignId('gender_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('handphone')->nullable();
            $table->string('current_address')->nullable();
            $table->string('residence_address')->nullable();
            $table->integer('annual_leave_1')->default(0)->nullable();
            $table->integer('annual_leave_2')->default(0)->nullable();
            $table->integer('annual_leave_3')->default(0)->nullable();
            $table->text('note')->nullable();
            $table->date('join_date')->nullable();
            $table->date('resign_date')->nullable();
            $table->foreignId('supervisor_id')->nullable();
            $table->boolean('is_bpjs_kesehatan')->default(0)->nullable();
            $table->boolean('is_bpjs_ketenagakerjaan')->default(0)->nullable();
            $table->boolean('is_pph21')->default(0)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
