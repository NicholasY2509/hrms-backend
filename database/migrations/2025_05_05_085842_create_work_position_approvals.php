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
        Schema::create('work_position_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_position_id')->constrained('work_positions');
            $table->unsignedBigInteger('interviewer_id')->constrained('employees');
            $table->unsignedBigInteger('interviewer_position_id')->constrained('work_positions');
            $table->integer('steps')->default(1);
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_position_approvals');
    }
};
