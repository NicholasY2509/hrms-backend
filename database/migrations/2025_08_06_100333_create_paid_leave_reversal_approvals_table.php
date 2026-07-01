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
        Schema::create('paid_leave_reversal_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paid_leave_reversal_id')->constrained('paid_leave_reversals');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('role')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paid_leave_reversal_approvals');
    }

};
