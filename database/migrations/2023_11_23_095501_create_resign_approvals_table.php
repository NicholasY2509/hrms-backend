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
        Schema::create('resign_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resign_id')->nullable();
            $table->foreignId('employee_id')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending')->nullable();
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
        Schema::dropIfExists('resign_approvals');
    }
};
