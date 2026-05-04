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
        // 1. Master for Step Types
        Schema::create('approval_step_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->boolean('needs_target')->default(false);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 2. Groups for pooled approvals
        Schema::create('approval_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 3. Group Membership
        Schema::create('approval_group_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id');
            $table->timestamps();
            
            $table->unique(['approval_group_id', 'employee_id']);
        });

        // 4. Policies mapped to Work Positions
        Schema::create('approval_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('approvable_type');
            $table->unsignedBigInteger('work_position_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['approvable_type', 'work_position_id'], 'pos_type_unique');
        });

        // 5. Policy Steps
        Schema::create('approval_policy_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_policy_id')->constrained()->cascadeOnDelete();
            $table->string('type_slug');
            $table->unsignedBigInteger('target_id')->nullable();
            $table->integer('sequence')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable FK checks to ensure clean cleanup of old versions
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('approval_policy_steps');
        Schema::dropIfExists('approval_policies');
        Schema::dropIfExists('approval_group_employees');
        Schema::dropIfExists('approval_group_users'); // Cleanup legacy version if exists
        Schema::dropIfExists('approval_groups');
        Schema::dropIfExists('approval_step_types');

        Schema::enableForeignKeyConstraints();
    }
};
