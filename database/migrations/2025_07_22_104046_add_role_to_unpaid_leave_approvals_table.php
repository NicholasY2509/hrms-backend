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
        Schema::table('unpaid_leave_approvals', function (Blueprint $table) {
            $table->string('role')->nullable()->after('employee_id');
            $table->unsignedBigInteger('employee_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unpaid_leave_approvals', function (Blueprint $table) {
            $table->dropColumn('role');
            $table->unsignedBigInteger('employee_id')->nullable(false)->change();
        });
    }
};
