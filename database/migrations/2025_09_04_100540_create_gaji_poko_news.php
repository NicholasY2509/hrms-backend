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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('payroll_month');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('gaji_pokok', 15,2)->default(0);
            $table->decimal('final_gaji_pokok', 15, 2)->default(0);
            $table->timestamps();
            $table->unique(['employee_id', 'payroll_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
