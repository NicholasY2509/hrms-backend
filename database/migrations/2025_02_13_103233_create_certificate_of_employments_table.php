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
        Schema::create('certificate_of_employments',

            function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('document_no');
                $table->foreignId('employee_id');
                $table->foreignId('work_position_id');
                $table->string('note')->nullable();
                $table->date('request_date');
                $table->date('issued_date')->nullable();
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
        Schema::dropIfExists('certificate_of_employments');
    }

};
