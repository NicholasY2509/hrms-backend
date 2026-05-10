# Frontend Integration: Attendance Calculation

This guide explains how to integrate the Attendance Calculation feature into the frontend, leveraging the unified Task tracking system.

## 1. API Endpoint

**Endpoint:** `POST /v1/portal/management/attendances/calculate`
**Permission Required:** `admin`, `hr-manager`, or `manager`

### Request Payload
```json
{
  "start_date": "2024-05-01",
  "end_date": "2024-05-31"
}
```

### Response
```json
{
  "success": true,
  "message": "Attendance calculation started",
  "data": {
    "task_id": 123,
    "status": "pending"
  }
}
```

---

## 2. Implementation Guide

### A. Initializing the Process
When the user clicks the "Calculate" button, you should trigger the API and then register the task in the global `ActivityStore`.

```typescript
import { useActivityStore } from '@/hooks/use-activity-store';
import apiClient from '@/lib/api-client';
import { toast } from 'sonner';

const { addActivity } = useActivityStore();

const handleCalculate = async (values: { start_date: string; end_date: string }) => {
  try {
    const res = await apiClient.post('/v1/portal/management/attendances/calculate', values);
    
    // 1. Notify the user
    toast.success('Calculation process has started in the background');

    // 2. Add to Activity Tracker
    addActivity(res.data.data.task_id, {
      name: `Calculating Attendance (${values.start_date} - ${values.end_date})`,
      type: 'attendance_calculation', // This will use the Database icon
    });
  } catch (error) {
    toast.error('Failed to start calculation');
  }
};
```

### B. Progress Tracking
The `ActivityTrackerContainer` is already listening to the `tasks.{user_id}` channel. Once you call `addActivity`, it will automatically:
1.  Listen for updates from the backend.
2.  Update the progress bar percentage.
3.  Display the current message (e.g., *"Processing: John Doe (2024-05-15)"*).
4.  Show a "Success" state once finished.

### C. UI Component (Activity Tracker)
The icons for attendance calculation are already mapped in `components/layout/activity-tracker.tsx`.

| Task Type | Icon |
| :--- | :--- |
| `attendance_calculation` | `Database02Icon` |
| `report_generation` | `DocumentValidationIcon` |

---

## 3. Best Practices

1.  **Date Validation:** Ensure the `end_date` is not before the `start_date` before sending the request.
2.  **Double Trigger Prevention:** Disable the calculation button while a task of type `attendance_calculation` is currently in the `processing` status in the store to prevent redundant loads.
3.  **Real-time Feedback:** The backend sends a heartbeat update every 50 records processed. This ensures the user sees constant movement in the progress bar.

## 4. Dependencies
- **Store:** `useActivityStore` (Zustand)
- **Broadcasting:** Laravel Echo / Reverb
- **Channel:** `private-tasks.{user_id}`
- **Event:** `.task.progress`
