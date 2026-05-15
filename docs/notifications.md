# HRMS Notification System Documentation

This document explains the architecture, implementation, and usage of the **Event-Driven Notification System** in the HRMS application.

---

## 1. Architectural Paradigm: Event-Driven
To maintain a modular monolith (as per `architecture.md`), notifications are decoupled from business logic. Instead of calling `$user->notify()` directly inside a Service, we use **Laravel Events and Listeners**.

### Benefits:
- **Decoupling**: Services only care about "what happened" (Events), not "who to notify" (Listeners).
- **Scalability**: Multiple listeners can be added to a single event (e.g., Database notification + Slack alert + Email).
- **Maintainability**: All notification logic is centralized in the `Notifications` module.

---

## 2. Core Components

### 2.1. Base Notification
Located at: `app/Modules/Notifications/Notifications/BaseNotification.php`

This class provides a standard structure for all notifications, ensuring compatibility with the frontend UI. It automatically handles **Database storage** and **Real-time broadcasting** via Reverb.

**Standard Payload Structure:**
```php
[
    'title' => 'Persetujuan Diperlukan',
    'message' => 'Anda memiliki permintaan baru...',
    'type' => 'approval_required',
    'action_url' => '/portal/unpaid-leave/123',
    'request_id' => 123
]
```

### 2.2. NotificationsServiceProvider
Located at: `app/Modules/Notifications/Providers/NotificationsServiceProvider.php`

This is where Events are mapped to Listeners. Every new notification trigger must be registered here.

---

## 3. Implementation Guide

### Step 1: Create an Event
Create events in the specific module where the action happens (e.g., `Leave`, `Attendance`).

```php
namespace App\Modules\Attendance\Events;

class EmployeeLateArrival {
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public function __construct(public Attendance $attendance) {}
}
```

### Step 2: Create/Update a Listener
Listeners live in `app/Modules/Notifications/Listeners`. They determine *who* gets notified and *what* the message is.

```php
public function handleLateArrival(EmployeeLateArrival $event): void {
    $employee = $event->attendance->employee;
    $employee->user->notify(new BaseNotification([
        'title' => 'Terlambat Datang',
        'message' => "Anda terlambat {$event->attendance->late_time}.",
        'type' => 'attendance_late',
        'action_url' => '/portal/attendance'
    ]));
}
```

### Step 3: Register the Event
Add the mapping to `NotificationsServiceProvider`:

```php
Event::listen(
    EmployeeLateArrival::class,
    [AttendanceNotificationListener::class, 'handleLateArrival']
);
```

---

## 4. Specific Workflows

### 4.1. Approval Notifications
The `Approvable` trait and `ApprovalActionService` are already wired to dispatch events:
- `ApprovalRequestCreated`: Notifies the employee that their request was submitted.
- `ApprovalStepActionable`: Notifies the **current** approver in the sequence.
- `ApprovalRequestFinished`: Notifies the employee of the final Approved/Rejected result.

### 4.2. Scheduled Notifications (Artisan Commands)
For notifications that aren't triggered by an action (like Birthdays or Reminders), we use Artisan Commands.

**Available Commands:**
- `php artisan employee:notify-birthdays`: Runs daily at 08:00 AM.
- `php artisan attendance:notify-missing-logs`: Runs daily at 09:00 AM.

**How to add a new command:**
1. Create command in `app/Modules/[Module]/Console/Commands`.
2. Ensure the class is discoverable via `bootstrap/app.php` (already configured to scan modules).
3. Register the schedule in `bootstrap/app.php` under `withSchedule`.

---

## 5. Frontend Integration

### 5.1. Real-time Notifications
The system uses **Laravel Reverb**. The frontend listens to the `private-App.Modules.User.Models.User.{id}` channel.

### 5.2. Visual Mapping
The frontend maps the `type` field to icons and colors in `components/notifications-menu.tsx`.
- `approval_submitted` -> Blue / Check
- `approval_required` -> Amber / Mail
- `attendance_late` -> Red / Clock
- `social_birthday_self` -> Pink / Gift

---

## 6. Troubleshooting

1. **Commands not found?** Ensure the module has a `Console/Commands` directory.
2. **Notification not received?** 
   - Check if the employee has a linked `user_id`.
   - Run `php artisan queue:work` (notifications use `ShouldQueue` for performance).
3. **SQL Error?** Check column names. (e.g., Use `date_birth` instead of `birth_date` for employees).
