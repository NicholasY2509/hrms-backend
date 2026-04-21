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
            // Check if old column exists before renaming
            if (Schema::hasColumn('user_face_profiles', 'face_embedding') && !Schema::hasColumn('user_face_profiles', 'embedding')) {
                $table->renameColumn('face_embedding', 'embedding');
            }
        });

        Schema::table('user_face_profiles', function (Blueprint $table) {
            // Change embedding to longText
            if (Schema::hasColumn('user_face_profiles', 'embedding')) {
                $table->longText('embedding')->change();
            }
            
            // Add registered_at
            if (!Schema::hasColumn('user_face_profiles', 'registered_at')) {
                $table->timestamp('registered_at')->nullable()->after('embedding');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_face_profiles', function (Blueprint $table) {
            $table->dropColumn('registered_at');
            $table->renameColumn('embedding', 'face_embedding');
        });

        Schema::table('user_face_profiles', function (Blueprint $table) {
            $table->text('face_embedding')->change();
        });
    }
};
