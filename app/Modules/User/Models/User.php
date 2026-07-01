<?php

namespace App\Modules\User\Models;

use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Models\UserEmployee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
#[Fillable(['email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->logExcept(['password', 'remember_token']);
    }

    /**
     * Get the user_employee record.
     */
    public function user_employee()
    {
        return $this->hasOne(UserEmployee::class, 'user_id', 'id');
    }

    /**
     * Get the employee record through user_employee.
     */
    public function employee()
    {
        return $this->hasOneThrough(
            Employee::class,
            UserEmployee::class,
            'user_id',
            'id',
            'id',
            'employee_id'
        );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Scope for filtering.
     */
    public function scopeFilter($query, array $filters)
    {
        $search = $filters['search'] ?? false;

        $query->when($search, function ($query, $search) {
            $search = preg_replace('/\s+/', ' ', trim($search));
            $query->where(function ($query) use ($search) {
                $query->where('email', 'like', "%$search%");
            });
        });
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        // Extract roles from the user's employee work position if available
        $roles = [];
        $employee = $this->employee()->with('work_position.passportRoles')->first();
        if ($employee && $employee->work_position) {
            $roles = $employee->work_position->passportRoles->pluck('name')->toArray();
        }

        return [
            'roles' => $roles,
        ];
    }
}
