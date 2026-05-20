<?php

namespace App\Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxPtkpSetting extends Model
{
    protected $table = 'tax_ptkp_settings';

    protected $fillable = ['code', 'name', 'amount', 'ter_category_id'];

    public function ter_category(): BelongsTo
    {
        return $this->belongsTo(TaxTerCategory::class, 'ter_category_id');
    }
}
