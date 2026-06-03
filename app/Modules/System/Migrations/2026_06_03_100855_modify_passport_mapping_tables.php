<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('passport_clients', function (Blueprint $table) {
            $table->dropColumn('is_global');
        });

        Schema::table('passport_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('passport_client_id')->after('passport_role_id')->comment('Client ID from Passport');
            $table->boolean('is_global')->default(false)->after('name')->comment('If true, all new employees get this role automatically');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('passport_roles', function (Blueprint $table) {
            $table->dropColumn(['passport_client_id', 'is_global']);
        });

        Schema::table('passport_clients', function (Blueprint $table) {
            $table->boolean('is_global')->default(false)->comment('If true, all new employees get this client assigned automatically');
        });
    }
};
