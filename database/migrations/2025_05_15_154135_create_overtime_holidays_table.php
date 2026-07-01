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
        Schema::create('overtime_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('document_no');
            $table->string('type');
            $table->foreignId('employee_id');
            $table->foreignId('department_id');
            $table->foreignId('work_position_id');
            $table->decimal('estimated_overtime_price',10,2)->nullable();
            $table->decimal('overtime_price',10,2)->nullable();
            $table->text('note')->nullable();
            $table->date('settled_at')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_holidays');
    }
};
