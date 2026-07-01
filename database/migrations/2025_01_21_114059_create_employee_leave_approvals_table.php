<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new

    class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_leave_approvals',

            function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id'); // Explicitly define as unsigned
                $table->unsignedBigInteger('approval_id'); // Explicitly define as unsigned
                $table->foreign('employee_id')->references('id')->on('employees');
                $table->foreign('approval_id')->references('id')->on('employees');
                $table->timestamps();
                $table->softDeletes();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_leave_approvals');
    }

};
