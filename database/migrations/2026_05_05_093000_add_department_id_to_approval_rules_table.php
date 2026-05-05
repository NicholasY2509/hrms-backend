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
        // Handle partial state from prior failed runs
        if (!Schema::hasColumn('approval_rules', 'department_id')) {
            Schema::table('approval_rules', function (Blueprint $table) {
                $table->integer('department_id')->nullable()->after('work_location_id');
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
                $table->index('department_id');
            });
        }

        // Drop the FK that depends on the old unique index first,
        // then drop the old unique and create the new one, then re-add the FK.
        Schema::table('approval_rules', function (Blueprint $table) {
            $table->dropForeign('approval_rules_approval_scheme_id_foreign');
        });

        Schema::table('approval_rules', function (Blueprint $table) {
            $table->dropUnique('rule_scheme_pos_loc_unique');
            $table->unique(
                ['approval_scheme_id', 'work_position_id', 'work_location_id', 'department_id'],
                'rule_scheme_pos_loc_dept_unique'
            );
            $table->foreign('approval_scheme_id')->references('id')->on('approval_schemes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_rules', function (Blueprint $table) {
            $table->dropForeign('approval_rules_approval_scheme_id_foreign');
        });

        Schema::table('approval_rules', function (Blueprint $table) {
            $table->dropUnique('rule_scheme_pos_loc_dept_unique');
            $table->unique(
                ['approval_scheme_id', 'work_position_id', 'work_location_id'],
                'rule_scheme_pos_loc_unique'
            );
            $table->foreign('approval_scheme_id')->references('id')->on('approval_schemes')->onDelete('cascade');
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
