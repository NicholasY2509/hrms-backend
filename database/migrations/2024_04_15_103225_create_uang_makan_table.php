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
        Schema::create('uang_makan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_id')->nullable();
            $table->integer('absent')->default(0)->nullable();
            $table->integer('late')->default(0)->nullable();
            $table->integer('sick')->default(0)->nullable();
            $table->integer('permit')->default(0)->nullable();
            $table->bigInteger('amount')->default(0)->nullable();
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
        Schema::dropIfExists('uang_makan');
    }
};
