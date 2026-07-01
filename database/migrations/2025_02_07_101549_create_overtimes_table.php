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
        Schema::create('overtimes',

            function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->string('document_no');
                $table->string('type');
                $table->foreignId('employee_id')->nullable();
                $table->foreignId('department_id')->nullable();
                $table->foreignId('work_position_id')->nullable();
                $table->foreignId('overtime_type_id')->nullable();
                $table->bigInteger('estimated_overtime_price')->nullable();
                $table->bigInteger('real_overtime_price')->nullable();
                $table->datetime('start_time');
                $table->datetime('finish_time');
                $table->string('total_time');
                $table->string('note')->nullable();
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
        Schema::dropIfExists('overtimes');
    }

};
