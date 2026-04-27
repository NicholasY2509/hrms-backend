<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename and modify policies -> rules
        Schema::rename('approval_policies', 'approval_rules');
        
        Schema::table('approval_rules', function (Blueprint $table) {
            $table->foreignId('approval_scheme_id')->after('id')->nullable()->constrained('approval_schemes')->onDelete('cascade');
        });

        // 2. Data Migration: Map approvable_type (string) to approval_scheme_id (FK)
        $rules = DB::table('approval_rules')->get();
        foreach ($rules as $rule) {
            $scheme = DB::table('approval_schemes')->where('model_class', $rule->approvable_type)->first();
            if ($scheme) {
                DB::table('approval_rules')->where('id', $rule->id)->update([
                    'approval_scheme_id' => $scheme->id
                ]);
            }
        }

        Schema::table('approval_rules', function (Blueprint $table) {
            $table->dropColumn('approvable_type');
            $table->foreignId('approval_scheme_id')->nullable(false)->change();
        });

        // 3. Rename and modify policy_steps -> rule_steps
        Schema::rename('approval_policy_steps', 'approval_rule_steps');
        
        Schema::table('approval_rule_steps', function (Blueprint $table) {
            $table->renameColumn('approval_policy_id', 'approval_rule_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_rule_steps', function (Blueprint $table) {
            $table->renameColumn('approval_rule_id', 'approval_policy_id');
        });

        Schema::rename('approval_rule_steps', 'approval_policy_steps');

        Schema::table('approval_rules', function (Blueprint $table) {
            $table->string('approvable_type')->after('approval_scheme_id')->nullable();
        });

        $rules = DB::table('approval_rules')->get();
        foreach ($rules as $rule) {
            $scheme = DB::table('approval_schemes')->find($rule->approval_scheme_id);
            if ($scheme) {
                DB::table('approval_rules')->where('id', $rule->id)->update([
                    'approvable_type' => $scheme->model_class
                ]);
            }
        }

        Schema::table('approval_rules', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approval_scheme_id');
        });

        Schema::rename('approval_rules', 'approval_policies');
    }
};
