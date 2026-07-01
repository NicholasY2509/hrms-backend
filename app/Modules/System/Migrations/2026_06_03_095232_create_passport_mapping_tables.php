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
        Schema::create('passport_clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('passport_client_id')->unique()->comment('ID from the Passport system');
            $table->string('name');
            $table->boolean('is_global')->default(false)->comment('If true, all new employees get this client assigned automatically');
            $table->timestamps();
        });

        Schema::create('passport_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('passport_role_id')->unique()->comment('ID from the Passport system');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('work_position_passport_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_position_id');
            $table->unsignedBigInteger('passport_role_id');
            $table->timestamps();

            $table->foreign('work_position_id')->references('id')->on('work_positions')->onDelete('cascade');
            $table->foreign('passport_role_id')->references('id')->on('passport_roles')->onDelete('cascade');
            
            $table->unique(['work_position_id', 'passport_role_id'], 'wp_pr_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('work_position_passport_role');
        Schema::dropIfExists('passport_roles');
        Schema::dropIfExists('passport_clients');
    }
};
