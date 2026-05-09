# HRMS Notification System Documentation

This document explains the architecture, implementation, and usage of the Notification System in the HRMS application.

## 1. Overview
The system provides real-time and persistent notifications to users via the web interface. It is built using Laravel's native Notification system on the backend and a reactive TanStack Query-based frontend.

- **Storage**: Notifications are stored in the `notifications` database table.
- **Delivery**: Currently delivered via the `database` channel for UI display.
- **Real-time**: The frontend polls or listens for updates to show unread counts.

---

## 2. Backend Implementation (Laravel)

### 2.1. Notification Classes
All notifications should be stored in the `app/Modules/[Feature]/Notifications` directory.

**Requirements for UI Compatibility**:
To ensure a notification displays correctly in the frontend "Notification Center", the `toArray($notifiable)` method must return a specific JSON structure.

```php
namespace App\Modules\Feature\Notifications;

use Illuminate\Notifications\Notification;

class FeatureStatusNotification extends Notification
{
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function via($notifiable): array
    {
        return ['database']; // 'mail' can be added later
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => "Description of the event",
            'action_url' => "/employee/feature/{$this->model->id}", // Relative frontend route
            'id' => $this->model->id,
            'type' => 'FeatureApproval', // Used for icon/title mapping
        ];
    }
}
```

### 2.2. Triggering Notifications
Notifications are typically triggered from **Services** after a database record is created or updated.

#### Manual Trigger (Specific User)
```php
$user->notify(new FeatureStatusNotification($record));
```

#### Bulk Trigger (Approval Groups/Roles)
```php
use Illuminate\Support\Facades\Notification;

$admins = ApprovalGroup::where('name', 'Admin HRD')->first()->employees;
foreach ($admins as $admin) {
    if ($admin->user) {
        $admin->user->notify(new FeatureStatusNotification($record));
    }
}
```

### 2.3. Integration with Approval Workflow
When using the `Approvable` trait, notifications should be sent by resolving the current steps of the `ApprovalRequest`.

```php
public function notifyApprovers(UnpaidLeave $leave)
{
    $leave->load(['approvalRequest.steps']);
    $request = $leave->approvalRequest;

    foreach ($request->steps as $step) {
        $approverIds = $step->getResolvedApproverIds();
        $employees = Employee::whereIn('id', (array)$approverIds)->get();
        
        foreach ($employees as $employee) {
            $employee->user?->notify(new UnpaidLeaveApprovalNotification($leave));
        }
    }
}
```

---

## 3. Frontend Implementation (Next.js)

### 3.1. Components
- **`NotificationsMenu`**: The dropdown in the header that shows a summary and unread count.
- **`NotificationsPage`**: The full list view for managing notifications.

### 3.2. Mapping and Visuals
The frontend maps the backend `type` (or class name) to human-readable titles and icons in `components/notifications-menu.tsx`:

- **Icons**: Handled by `getIcon(type)`.
- **Titles**: Handled by `getTitle(type)`.

**Example Mapping**:
- `*Leave*` -> Calendar Icon / "Pengajuan Cuti"
- `*Overtime*` -> Clock Icon / "Lembur"
- `*Approval*` -> Mail Icon / "Persetujuan"

### 3.3. Routing
The `action_url` provided by the backend is used to redirect the user when they click a notification. Ensure these URLs are relative (e.g., `/employee/unpaid-leave/123`).

---

## 4. How to Add a New Notification

1.  **Generate Class**: `php artisan make:notification Modules/[Module]/Notifications/[Name]`.
2.  **Configure `via`**: Set to `['database']`.
3.  **Implement `toArray`**: Use the standard structure (message, action_url, id).
4.  **Trigger in Service**: Call `$user->notify()` at the appropriate business logic point.
5.  **Update Frontend (Optional)**: If the new type needs a specific icon or title, update `getIcon` and `getTitle` in `hrms-frontend/components/notifications-menu.tsx`.

---

## 5. Troubleshooting

### Notifications not showing up?
1.  **Check DB**: `SELECT * FROM notifications ORDER BY created_at DESC;`
2.  **Check Queue**: If the notification class implements `ShouldQueue`, ensure `php artisan queue:work` is running. *Recommendation: Use synchronous notifications for database channel in local dev.*
3.  **Check User Link**: Ensure the `employees` table has a valid `user_id` linked to the `users` table.
4.  **Check Cache**: If loading relationships in a transaction, use `$model->unsetRelation('relationship')->load('relationship')` to ensure fresh data.
