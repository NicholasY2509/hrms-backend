<?php

namespace App\Modules\Notifications\Listeners;

use App\Modules\Employee\Events\EmployeeBirthday;
use App\Modules\Notifications\Notifications\BaseNotification;
use App\Modules\Employee\Models\Employee;

class SocialNotificationListener
{
    /**
     * Handle Employee Birthday event.
     */
    public function handleBirthday(EmployeeBirthday $event): void
    {
        $employee = $event->employee;

        // Notify the birthday person
        if ($employee->user) {
            $employee->user->notify(new BaseNotification([
                'title' => 'Selamat Ulang Tahun!',
                'message' => "Selamat ulang tahun, {$employee->first_name}! Semoga hari Anda menyenangkan.",
                'type' => 'social_birthday_self',
                'icon' => 'birthday',
                'action_url' => '/portal/profile'
            ]));
        }

        // Notify team members (Optional: can be noisy, so maybe just a global broadcast or specific channel)
    }
}
