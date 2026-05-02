<?php

namespace App\Modules\Employee\Models;

use App\Traits\Approvable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificateOfEmployment extends Model
{
    use SoftDeletes, Approvable, HasUuids;

    protected $table = 'certificate_of_employments';
    protected $guarded = ['id'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function workPosition(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Organization\Models\WorkPosition::class, 'work_position_id');
    }
}
