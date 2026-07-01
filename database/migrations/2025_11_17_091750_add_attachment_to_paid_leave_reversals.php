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
        Schema::table('paid_leave_reversals', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after(
                'note'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paid_leave_reversals', function (Blueprint $table) {
            $table->dropColumn('attachment');
        });
    }
};
