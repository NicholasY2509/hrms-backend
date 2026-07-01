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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_working_hour_id')->unique();
            $table->time('incoming_scan')->nullable();
            $table->foreignId('incoming_location_id')->nullable();
            $table->string('incoming_photo')->nullable();
            $table->string('incoming_latitude')->nullable();
            $table->string('incoming_longitude')->nullable();
            $table->time('outgoing_scan')->nullable();
            $table->foreignId('outgoing_location_id')->nullable();
            $table->string('outgoing_photo')->nullable();
            $table->string('outgoing_latitude')->nullable();
            $table->string('outgoing_longitude')->nullable();
            $table->time('late_time')->nullable();
            $table->time('early_time')->nullable();
            $table->foreignId('attendance_status_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
