<?php

namespace App\Modules\Disciplinary\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarningLetterType extends Model
{
    use SoftDeletes;

    protected $table = 'warning_letter_types';
    protected $guarded = ['id'];

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($q, $search) {
            $search = preg_replace('/\s+/', ' ', trim($search));
            $q->where('name', 'like', '%' . $search . '%');
        });

        return $query;
    }
}
