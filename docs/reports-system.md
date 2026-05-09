# Asynchronous Report Export System

This document outlines the architecture and implementation details of the HRMS asynchronous report generation system. This system allows users to generate large reports (thousands of rows) in the background without blocking the UI, providing real-time progress updates via WebSockets.

## 🏗️ Architecture Overview

1.  **Trigger**: User initiates an export via the Frontend UI.
2.  **API**: `POST /api/v1/system/reports` creates a record in the `reports` table with status `pending`.
3.  **Background Job**: `ProcessExportJob` is dispatched to the queue.
4.  **Broadcasting**: The Job uses **Laravel Reverb** to broadcast progress updates (percentage and status messages) to a private user channel (`reports.{user_id}`).
5.  **Real-time UI**: A global `ReportProgressContainer` on the frontend listens for these events and shows an animated progress tracker.
6.  **Completion**: Once finished, the status changes to `completed`, and the frontend reveals a download button.

---

## 🛠️ Components

### 1. The Engine: `ProcessExportJob.php`
The core logic that handles different formats (Excel, CSV, PDF, TXT). It is designed to be **format-agnostic** and **type-agnostic**.

### 2. The Registry: `config/reports.php`
Instead of a giant switch-case, all report types are registered here.
```php
'map' => [
    'attendance_report' => [
        'class' => \App\Modules\Attendance\Exports\AttendanceExport::class,
        'view' => 'exports.attendance_pdf',
    ],
]
```

---

## 🚀 How to Add a New Report Type

Follow these steps to add a new report (e.g., "Payroll Report"):

### 1. Create the Export Class
Create a class in your module's `Exports` folder. It **must** implement `WithMapping` and `WithEvents`.

**CRITICAL: Real-time Progress Tracking**
To show granular progress for Excel/CSV, update the progress inside the `map()` function:

```php
public function map($row): array
{
    $this->processedCount++;
    if ($this->processedCount % 500 === 0 && $this->job) {
        $percent = 40 + round(($this->processedCount / $this->totalRecords) * 50);
        $this->job->updateProgress($percent, "Processing row {$this->processedCount}...");
    }
    return [ /* your columns */ ];
}
```

### 2. Create the PDF Template
Create a Blade view in `resources/views/exports/`.

### 3. Register the Report
Add your new type to `config/reports.php`.

### 4. Add the Frontend Button
Use or duplicate the `ExportAttendanceDialog` component to trigger the `POST /v1/system/reports` request.

---

## 💡 Best Practices & Performance

*   **Eager Loading**: Always use `->with([...])` in your Export's `query()` method. Mapping thousands of rows will trigger N+1 query death if relationships aren't eager-loaded.
*   **Progress Trickle**: The frontend has a built-in "trickle" effect that slowly creeps the bar forward while waiting for the backend.
*   **Timeouts**: The Job has a default timeout of 10 minutes (`$timeout = 600`). If a report is extremely large, consider using CSV as it is significantly faster and uses less memory than Excel or PDF.
*   **Memory Limit**: For massive PDF exports, ensure your server has enough RAM allocated to PHP, as DOMPDF is memory-intensive.

## 📡 WebSocket Events
*   **Event**: `App\Modules\System\Events\ReportProgressUpdated`
*   **Channel**: `private-reports.{user_id}`
*   **Payload**: `id`, `status`, `progress`, `current_message`, `download_url`.
