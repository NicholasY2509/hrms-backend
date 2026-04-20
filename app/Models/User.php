<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use \Laravel\Passport\HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'legacy';

    /**
     * Get the user_employee record.
     */
    public function user_employee()
    {
        return $this->hasOne(\App\Modules\Employee\Models\UserEmployee::class, 'user_id', 'id');
    }

    /**
     * Get the employee record through user_employee.
     */
    public function employee()
    {
        return $this->hasOneThrough(
            \App\Modules\Employee\Models\Employee::class,
            \App\Modules\Employee\Models\UserEmployee::class,
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
}
