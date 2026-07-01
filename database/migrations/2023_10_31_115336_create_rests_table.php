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
        Schema::create('rests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable();
            $table->time('rest_out')->nullable();
            $table->string('rest_out_photo')->nullable();
            $table->string('rest_out_latitude')->nullable();
            $table->string('rest_out_longitude')->nullable();
            $table->foreignId('rest_out_location_id')->nullable();
            $table->time('rest_in')->nullable();
            $table->string('rest_in_photo')->nullable();
            $table->string('rest_in_latitude')->nullable();
            $table->string('rest_in_longitude')->nullable();
            $table->foreignId('rest_in_location_id')->nullable();
            $table->date('rest_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rests');
    }
};
