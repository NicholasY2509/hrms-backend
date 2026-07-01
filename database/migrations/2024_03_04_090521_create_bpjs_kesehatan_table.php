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
        Schema::create('bpjs_kesehatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_id')->nullable();
            $table->decimal('pemberi_kerja_percent', 5, 2)->nullable();
            $table->bigInteger('pemberi_kerja_amount')->nullable();
            $table->decimal('pekerja_percent', 5, 2)->nullable();
            $table->bigInteger('pekerja_amount')->nullable();
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
        Schema::dropIfExists('bpjs_kesehatan');
    }
};
