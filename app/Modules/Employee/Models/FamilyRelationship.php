<?php

namespace App\Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyRelationship extends Model
{
    protected $table = 'family_relationships';
    protected $guarded = ['id'];
    public $timestamps = false;
}
