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
        Schema::dropIfExists('employee_tax_profiles');
        Schema::dropIfExists('tax_ptkp_settings');
        Schema::dropIfExists('tax_ter_categories');
        Schema::dropIfExists('employee_salary_components');
        Schema::dropIfExists('employee_salaries');
        Schema::dropIfExists('salary_components');

        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('category', ['allowance', 'deduction', 'benefit'])->default('allowance');
            $table->enum('type', ['fixed', 'calculated', 'one-time'])->default('fixed');
            $table->decimal('default_amount', 15, 2)->default(0);
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tax_ter_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 1)->unique(); // A, B, C
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('tax_ptkp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // TK/0, K/1, etc.
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->foreignId('ter_category_id')->constrained('tax_ter_categories');
            $table->timestamps();
        });

        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->decimal('bpjs_base_amount', 15, 2)->comment('Formerly amount - for BPJS base');
            $table->decimal('actual_base_amount', 15, 2)->comment('Formerly real_amount - for OT & Tax');
            $table->date('effective_date');
            $table->string('reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->bigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });

        Schema::create('employee_salary_components', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->foreignId('salary_component_id')->constrained('salary_components')->onDelete('cascade');
            $table->decimal('amount', 15, 2)->nullable()->comment('Specific override amount');
            $table->string('formula')->nullable()->comment('Optional logic/multiplier');
            $table->boolean('is_calculated')->default(false);
            $table->date('effective_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->unique(['employee_id', 'salary_component_id'], 'emp_comp_unique');
        });

        Schema::create('employee_tax_profiles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->string('npwp_number')->nullable();
            $table->foreignId('ptkp_setting_id')->constrained('tax_ptkp_settings');
            $table->enum('tax_method', ['gross', 'gross_up', 'net'])->default('gross');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_tax_profiles');
        Schema::dropIfExists('tax_ptkp_settings');
        Schema::dropIfExists('tax_ter_categories');
        Schema::dropIfExists('employee_salary_components');
        Schema::dropIfExists('employee_salaries');
        Schema::dropIfExists('salary_components');
    }
};
