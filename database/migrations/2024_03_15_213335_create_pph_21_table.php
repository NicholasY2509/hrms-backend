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
        Schema::create('pph_21', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_id')->nullable();
            $table->foreignId('income_tax_id')->nullable();
            $table->bigInteger('amount')->default(0);
            $table->date('settled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pph_21');
    }
};
