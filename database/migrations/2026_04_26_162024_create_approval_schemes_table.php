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
        Schema::create('approval_schemes', function (Blueprint $row) {
            $row->id();
            $row->string('name');
            $row->string('model_class')->unique();
            $row->text('description')->nullable();
            $row->boolean('is_active')->default(true);
            $row->timestamps();
        });

        // Migrate data from approval_request_types if it exists
        if (Schema::hasTable('approval_request_types')) {
            $types = DB::table('approval_request_types')->get();
            foreach ($types as $type) {
                DB::table('approval_schemes')->insert([
                    'name' => $type->name,
                    'model_class' => $type->model_class,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_schemes');
    }
};
