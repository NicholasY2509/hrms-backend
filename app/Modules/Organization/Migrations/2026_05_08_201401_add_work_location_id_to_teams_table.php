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
        if (!Schema::hasColumn('teams', 'work_location_id')) {
            Schema::table('teams', function (Blueprint $table) {
                $table->bigInteger('work_location_id')->nullable()->after('id');
                $table->foreign('work_location_id')->references('id')->on('work_locations')->onDelete('set null');
            });
        } else {
            Schema::table('teams', function (Blueprint $table) {
                $table->bigInteger('work_location_id')->nullable()->change();
                $table->foreign('work_location_id')->references('id')->on('work_locations')->onDelete('set null');
            });
        }
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('work_location_id');
        });
    }
};
