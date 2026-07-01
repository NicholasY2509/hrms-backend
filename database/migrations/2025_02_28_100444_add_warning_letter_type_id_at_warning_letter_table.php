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
        Schema::table('warning_letters',

            function (Blueprint $table) {
                $table->foreignId('warning_letter_type_id')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warning_letters',

            function (Blueprint $table) {
                $table->dropColumn('warning_letter_type_id');
            });
    }

};
