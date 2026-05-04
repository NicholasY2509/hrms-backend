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
        // 1. Create Request Types table
        Schema::create('approval_request_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model_class')->unique(); // e.g., App\Modules\Leave\Models\UnpaidLeave
            $table->string('slug')->unique();        // e.g., unpaid-leave
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Modify Policies table
        Schema::table('approval_policies', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('work_position_id');
            $table->unsignedBigInteger('work_position_id')->nullable()->change();
            
            // Drop old unique constraint
            $table->dropUnique('pos_type_unique');
        });

        // 3. Add new flexible unique constraints
        // We can't have two defaults for the same type, and we can't have two policies for the same type+position.
        // Note: Raw SQL might be needed for conditional unique constraints in some DBs, 
        // but for now we'll handle uniqueness in logic or separate indexes.
        Schema::table('approval_policies', function (Blueprint $table) {
            $table->unique(['approvable_type', 'work_position_id'], 'policy_type_pos_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_policies', function (Blueprint $table) {
            $table->dropUnique('policy_type_pos_unique');
            $table->unique(['approvable_type', 'work_position_id'], 'pos_type_unique');
            $table->dropColumn('is_default');
        });

        Schema::dropIfExists('approval_request_types');
    }
};
