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
        Schema::rename('driver_licenses', 'employee_driver_licenses');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('employee_driver_licenses', 'driver_licenses');
    }
};
