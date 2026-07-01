<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new

    class extends Migration {

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('unpaid_leave_types',

            function (Blueprint $table) {
                $table->string('background_color')->nullable()->after('name');
                $table->string('border_color')->nullable()->after('background_color');
                $table->string('text_color')->nullable()->after('border_color');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('unpaid_leave_types',

            function (Blueprint $table) {
                $table->dropColumn(['backgroundColor', 'borderColor', 'textColor']);
            });
    }

};
