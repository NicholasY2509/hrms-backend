<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new

    class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('certificate_of_employment_approvals',

            function (Blueprint $table) {
                $table->id(); // UUID for primary key
                $table->uuid('certificate_id')->nullable(); // Shortened column name
                $table->foreignId('employee_id')->nullable();
                $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending')->nullable();
                $table->text('note')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // Define foreign key with a custom name
                $table->foreign('certificate_id', 'fk_certificate_approval')
                    ->references('id')
                    ->on('certificate_of_employments')
                    ->onDelete('cascade'); // Adjust as needed
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_of_employment_approvals',

            function (Blueprint $table) {
                $table->dropForeign('fk_certificate_approval'); // Use the custom constraint name
            });

        Schema::dropIfExists('certificate_of_employment_approvals');
    }

};
