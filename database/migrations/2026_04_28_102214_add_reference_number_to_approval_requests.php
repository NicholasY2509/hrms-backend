<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->string('reference_number')->nullable()->after('approval_rule_id');
            $table->index('reference_number');
        });
    }

    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropColumn('reference_number');
        });
    }
};
