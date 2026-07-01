<?php

use App\Models\WorkLocation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new

    class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('general_affairs',

            function (Blueprint $table) {
                //
                $table->foreignId('work_location_id')->after('department_id')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_affairs',

            function (Blueprint $table) {
                //
                $table->dropColumn('work_location_id');
            });
    }

};
