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
        Schema::create('pph21_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pph21_category_id')->nullable();
            $table->bigInteger('minimal_amount')->nullable();
            $table->bigInteger('maximal_amount')->nullable();
            $table->decimal('rate', 5, 2)->nullable();
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
