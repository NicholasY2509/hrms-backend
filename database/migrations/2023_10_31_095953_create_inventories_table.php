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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_image')->nullable();
            $table->date('product_date')->nullable();
            $table->foreignId('inventory_status_id')->nullable();
            $table->foreignId('inventory_stock_id')->nullable();
            $table->foreignId('inventory_type_id')->nullable();
            $table->string('product_brand')->nullable();
            $table->text('product_specification')->nullable();
            $table->string('product_serial_number')->nullable();
            $table->string('product_details')->nullable();
            $table->foreignId('company_id')->nullable();
            $table->foreignId('department_id')->nullable();
            $table->foreignId('work_location_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
