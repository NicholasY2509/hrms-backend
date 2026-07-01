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
        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->text('jaminan_pinjaman')->nullable();
            $table->integer('tenor')->nullable(); // Number of installments
            $table->decimal('installment', 15, 2)->nullable(); // Monthly installment amount
            $table->text('reason_loan')->nullable();
            $table->date('start_date')->nullable(); // When the loan starts
            $table->date('confirmed_at')->nullable();
            $table->date('approved_at')->nullable(); // When fully approved
            $table->date('settled_at')->nullable(); // When fully paid
            $table->date('cancelled_at')->nullable(); // When cancelled
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_loans');
    }
};