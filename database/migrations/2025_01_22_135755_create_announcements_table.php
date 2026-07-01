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
        Schema::create('announcements',

            function (Blueprint $table) {
                $table->id();
                $table->foreignId('department_id')->nullable();
                $table->foreignId('religion_id')->nullable();
                $table->foreignId('work_position_id')->nullable();
                $table->foreignId('work_location_id')->nullable();
                $table->string('title');
                $table->string('description');
                $table->date('start_date');
                $table->date('end_date');
                $table->timestamps();
                $table->softDeletes();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }

};
