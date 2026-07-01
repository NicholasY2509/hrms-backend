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
        Schema::create('bpjs_ketenagakerjaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_id')->nullable();
            $table->decimal('jkk_percent', 5, 2)->nullable();
            $table->bigInteger('jkk_amount')->nullable();
            $table->decimal('jht1_percent', 5, 2)->nullable();
            $table->bigInteger('jht1_amount')->nullable();
            $table->decimal('jht2_percent', 5, 2)->nullable();
            $table->bigInteger('jht2_amount')->nullable();
            $table->decimal('jkm_percent', 5, 2)->nullable();
            $table->bigInteger('jkm_amount')->nullable();
            $table->decimal('jp1_percent', 5, 2)->nullable();
            $table->bigInteger('jp1_amount')->nullable();
            $table->decimal('jp2_percent', 5, 2)->nullable();
            $table->bigInteger('jp2_amount')->nullable();
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
        Schema::dropIfExists('bpjs_ketenagakerjaan');
    }
};
