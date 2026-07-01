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
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable();
            $table->bigInteger('deduction_amount')->nullable();
            $table->bigInteger('gross_amount')->nullable();
            $table->bigInteger('nett_amount')->nullable();
            $table->text('note')->nullable();
            $table->date('salary_at')->nullable();
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
        Schema::dropIfExists('salaries');
    }
};
