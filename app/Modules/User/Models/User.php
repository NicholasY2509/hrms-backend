<?php

namespace App\Modules\User\Models;

use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Models\UserEmployee;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

#[Fillable(['email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
            $query->where(function ($query) use ($search) {
                $query->where('email', 'like', "%$search%");
            });
        });
    }
}
