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
        Schema::rename('owned_vehicles', 'employee_vehicles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('employee_vehicles', 'owned_vehicles');
    }
};
