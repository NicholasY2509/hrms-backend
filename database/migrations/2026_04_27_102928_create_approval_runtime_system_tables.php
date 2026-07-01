<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_rule_id');
            $table->string('approvable_type');
            $table->unsignedBigInteger('approvable_id');
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
            $table->integer('current_step_sequence')->default(1);
            $table->text('metadata')->nullable();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id']);
        });

        Schema::create('approval_request_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_request_id');
            $table->string('approver_type'); // user, group, supervisor, dept_head
            $table->unsignedBigInteger('approver_id')->nullable(); // The resolved User ID or Group ID
            $table->integer('sequence');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('notes')->nullable();
            $table->unsignedInteger('actioned_by')->nullable(); // User ID who took action
            $table->timestamp('actioned_at')->nullable();
            $table->timestamps();

            $table->foreign('approval_request_id')->references('id')->on('approval_requests')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_request_steps');
        Schema::dropIfExists('approval_requests');
    }
};
