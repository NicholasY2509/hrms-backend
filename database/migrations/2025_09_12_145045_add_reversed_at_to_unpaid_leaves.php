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
        Schema::table('unpaid_leaves', function (Blueprint $table) {
            $table->dateTime('reversed_at')->nullable()->after('cutted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unpaid_leaves', function (Blueprint $table) {
            $table->dropColumn('reversed_at');
        });
    }
};
