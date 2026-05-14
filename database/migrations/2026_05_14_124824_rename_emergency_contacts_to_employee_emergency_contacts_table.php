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
        Schema::rename('emergency_contacts', 'employee_emergency_contacts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('employee_emergency_contacts', 'emergency_contacts');
    }
};
