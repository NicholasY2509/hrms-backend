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
        Schema::table("pph_21", function (Blueprint $table) {
            $table->renameColumn("salary_id", "payroll_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("pph_21", function (Blueprint $table) {
            $table->renameColumn("payroll_id", "salary_id");
        });
    }
};
