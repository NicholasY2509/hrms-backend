<?php

namespace App\Modules\Payroll\Models;

use App\Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeTaxProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_tax_profiles';
    protected $fillable = [
        'employee_id',
        'npwp_number',
        'ptkp_setting_id',
        'tax_method'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function ptkp_setting(): BelongsTo
    {
        return $this->belongsTo(TaxPtkpSetting::class, 'ptkp_setting_id');
    }
}
