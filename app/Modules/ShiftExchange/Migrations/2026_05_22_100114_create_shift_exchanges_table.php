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
        Schema::create('shift_exchanges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('date');
            $table->unsignedBigInteger('original_working_hour_id');
            $table->unsignedBigInteger('requested_working_hour_id');
            $table->unsignedBigInteger('exchange_with_employee_id')->nullable();
            $table->text('reason');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('original_working_hour_id')->references('id')->on('working_hours')->cascadeOnDelete();
            $table->foreign('requested_working_hour_id')->references('id')->on('working_hours')->cascadeOnDelete();
            $table->foreign('exchange_with_employee_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_exchanges');
    }
};
