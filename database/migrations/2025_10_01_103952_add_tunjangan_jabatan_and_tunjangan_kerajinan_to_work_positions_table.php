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
        Schema::table('work_positions', function (Blueprint $table) {
            //
            $table->bigInteger('tunjangan_jabatan')->after('potongan_uang_transport')->nullable();
            $table->bigInteger('tunjangan_kerajinan')->after('tunjangan_jabatan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_positions', function (Blueprint $table) {
            //
            $table->dropColumn('tunjangan_jabatan');
            $table->dropColumn('tunjangan_kerajinan');
        });
    }

};
