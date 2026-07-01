<?php

use App\Models\AttendanceStatus;
use App\Models\Employee;
use App\Models\User;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daily_leave_attendances', function (Blueprint $table) {
            $table->id();
            $table->date('daily_leave_at');
            $table->foreignId('supervisor_id')->constrained();
            $table->foreignId('employee_id')->constrained();
            $table->foreignId('attendance_status_id')->constrained();
            $table->string('reason');
            $table->string('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_leave_attendances');
    }
};
