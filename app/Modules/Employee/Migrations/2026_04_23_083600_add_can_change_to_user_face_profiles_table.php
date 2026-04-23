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
        Schema::table('user_face_profiles', function (Blueprint $table) {
            $table->boolean('can_change')->default(false)->after('registered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_face_profiles', function (Blueprint $table) {
            $table->dropColumn('can_change');
        });
    }
};
