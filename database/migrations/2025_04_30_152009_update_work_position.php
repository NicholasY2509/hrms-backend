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
        Schema::table('work_positions', function (Blueprint $table) {
            $table->integer('pengalaman')->default(0)->nullable()->after('description');
            $table->string('lokasi')->nullable()->after('pengalaman');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_positions', function (Blueprint $table) {
            $table->dropColumn('pengalaman');
            $table->dropColumn('lokasi');
        });
    }
};
