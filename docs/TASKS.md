# Unified Task Tracking System

This document outlines how to use the generic Task system for background processes, real-time progress broadcasting, and frontend tracking.

## Overview

The Task system provides a unified way to:
1.  **Track** long-running background processes (Reports, Bulk Calculations, Data Migrations).
2.  **Broadcast** progress updates to specific users using Laravel Reverb (WebSockets).
3.  **UI Integration** via a global Activity Tracker in the frontend.

---

## Backend Implementation

### 1. The Task Model
Tasks are stored in the `tasks` table. Each task has:
- `user_id`: The owner of the task.
- `type`: Snake_case string (e.g., `attendance_calculation`, `report_generation`).
- `status`: `pending`, `processing`, `completed`, `failed`.
- `progress`: 0 to 100.
- `message`: Current status message shown to the user.
- `payload` & `metadata`: JSON fields for input parameters and result data (like file paths).

### 2. Using the `HasTaskProgress` Trait
Any Service or Job can use this trait to easily broadcast progress.

```php
use App\Modules\System\Traits\HasTaskProgress;

class MyBackgroundService
{
    use HasTaskProgress;

    public function handle($data)
    {
        // 1. Pre-load: Set the task instance
        // $this->setTask($task); 

        // 2. Update Progress
        $this->updateProgress(25, 'Fetching records...');

        // 3. Complete
        $this->completeTask('Process finished!', ['result_url' => $url]);

        // 4. Fail
        $this->failTask('Something went wrong.');
    }
}
```

### 3. Controller Implementation
Create the `Task` before dispatching the job to give the frontend an ID to listen to.

```php
public function startProcess(Request $request)
{
    $task = Task::create([
        'user_id' => auth()->id(),
        'type' => 'my_custom_process',
        'status' => 'pending',
        'message' => 'Waiting for worker...',
    ]);

    MyBackgroundJob::dispatch($task, $request->validated());

    return response()->json(['task_id' => $task->id]);
}
```

---

## Frontend Implementation

### 1. Registering an Activity
When you trigger a background process, register it in the `useActivityStore` immediately.

```typescript
import { useActivityStore } from '@/hooks/use-activity-store';

const { addActivity } = useActivityStore();

const handleClick = async () => {
  const res = await api.post('/my-endpoint');
  
  addActivity(res.data.task_id, {
    name: 'Processing Data', // Display Name
    type: 'general',         // Icon Type
  });
};
```

### 2. Real-time Listeners
The `ActivityTrackerContainer` automatically listens to the `tasks.{user_id}` channel.

- **Channel:** `private-tasks.{user_id}`
- **Event:** `task.progress` (mapped as `.task.progress` in Echo)

### 3. Icon Mapping
To add a new icon for a specific task type, update the `getActivityIcon` function in `components/layout/activity-tracker.tsx`.

---

## Database & Events Reference

- **Migration:** `database/migrations/2026_05_09_220506_create_tasks_table.php`
- **Event:** `App\Modules\System\Events\TaskProgressUpdated`
- **Frontend Hook:** `hooks/use-activity-store.ts`
- **Frontend UI:** `components/layout/activity-tracker.tsx`
