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
        Schema::create('zkteco_attendances', function (Blueprint $table) {
            $table->integer('uid')->nullable();
            $table->time('timestamp')->nullable();
            $table->date('attendance_at')->nullable();
            $table->foreignId('zkteco_machine_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zkteco_attendances');
    }
};
