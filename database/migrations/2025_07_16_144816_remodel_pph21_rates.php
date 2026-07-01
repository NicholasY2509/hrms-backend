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
        Schema::dropIfExists('pph21_rates');

        Schema::create('pph21_rates', function (Blueprint $table) {
            $table->id();
            $table->string('constant_type', 20);
            $table->string('key_1')->nullable();
            $table->bigInteger('key_2')->nullable();
            $table->bigInteger('value_1')->nullable();
            $table->decimal('value_2', 15, 5); 
            $table->date('effective_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pph21_rates');
    }
};
