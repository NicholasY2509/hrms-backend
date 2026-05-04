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
        Schema::table('approval_rules', function (Blueprint $table) {
            // Drop old unique index if it exists.
            // Note: In the previous migration it might have been partially renamed or removed.
            // Based on 'SHOW INDEX', it's called 'policy_type_pos_unique'.
            $table->dropUnique('policy_type_pos_unique');

            // Add new flexible unique constraint.
            // This allows one rule per Scheme + Position + Location combination.
            // Note: Since work_location_id and work_position_id can be NULL, 
            // MySQL allows multiple NULLs in unique constraints. 
            // We should ideally handle the "only one NULL location" rule in application logic 
            // or use a generated column/trigger if strict enforcement is needed.
            $table->unique(['approval_scheme_id', 'work_position_id', 'work_location_id'], 'rule_scheme_pos_loc_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_rules', function (Blueprint $table) {
            $table->dropUnique('rule_scheme_pos_loc_unique');
            $table->unique(['approval_scheme_id', 'work_position_id'], 'policy_type_pos_unique');
        });
    }
};
