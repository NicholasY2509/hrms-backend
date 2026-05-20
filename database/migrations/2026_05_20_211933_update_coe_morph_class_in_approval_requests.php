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
        DB::table('approval_requests')
            ->where('approvable_type', 'App\Modules\Employee\Models\CertificateOfEmployment')
            ->update(['approvable_type' => 'App\Modules\CertificateOfEmployment\Models\CertificateOfEmployment']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('approval_requests')
            ->where('approvable_type', 'App\Modules\CertificateOfEmployment\Models\CertificateOfEmployment')
            ->update(['approvable_type' => 'App\Modules\Employee\Models\CertificateOfEmployment']);
    }
};
