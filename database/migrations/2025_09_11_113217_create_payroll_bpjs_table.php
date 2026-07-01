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
        Schema::create('payroll_bpjs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payroll_id');
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });

        Schema::table('bpjs_kesehatan', function (Blueprint $table) {
            $table->renameColumn('salary_id','payroll_id');
        });

        Schema::table('bpjs_ketenagakerjaan', function (Blueprint $table) {
            $table->renameColumn('salary_id','payroll_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_bpjs');
        Schema::table('bpjs_kesehatan', function (Blueprint $table) {
            $table->renameColumn('payroll_id','salary_id');
        });

        Schema::table('bpjs_ketenagakerjaan', function (Blueprint $table) {
            $table->renameColumn('payroll_id','salary_id');
        });
    }
};
