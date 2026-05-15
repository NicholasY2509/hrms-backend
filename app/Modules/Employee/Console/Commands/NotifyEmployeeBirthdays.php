<?php

namespace App\Modules\Employee\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Events\EmployeeBirthday;
use Carbon\Carbon;

class NotifyEmployeeBirthdays extends Command
{
    protected $signature = 'employee:notify-birthdays';
    protected $description = 'Check for employee birthdays and dispatch events';

    public function handle(): void
    {
        $today = Carbon::today()->format('m-d');
        
        $employees = Employee::whereRaw("DATE_FORMAT(date_birth, '%m-%d') = ?", [$today])->get();

        foreach ($employees as $employee) {
            event(new EmployeeBirthday($employee));
            $this->info("Birthday event dispatched for: {$employee->full_name}");
        }

        $this->info("Checked birthdays for {$employees->count()} employees.");
    }
}
