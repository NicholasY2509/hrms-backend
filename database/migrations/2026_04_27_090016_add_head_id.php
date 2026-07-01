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
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('dept_head_id')->nullable()->after('name');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->unsignedBigInteger('team_head_id')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('dept_head_id');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn('team_head_id');
        });
    }
};
