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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('pph21_amount',10,2)->default(0)->after('final_gaji_pokok');
            $table->decimal('after_pph21',10,2)->default(0)->after('pph21_amount');
            $table->decimal('bpjs_amount',10,2)->default(0)->after('after_pph21');
            $table->decimal('after_bpjs',10,2)->default(0)->after('bpjs_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('pph21_amount');
            $table->dropColumn('after_pph21');
            $table->dropColumn('bpjs_amount');
            $table->dropColumn('after_bpjs');
        });
    }
};
