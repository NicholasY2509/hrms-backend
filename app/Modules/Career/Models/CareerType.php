<?php

namespace App\Modules\Career\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareerType extends Model
{
    use SoftDeletes;

    protected $table = 'career_types';
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
