# Asynchronous Report Export System

This document outlines the architecture and implementation details of the HRMS asynchronous report generation system. This system allows users to generate large reports in the background with real-time feedback via WebSockets.

---

## 🏗️ Architecture Overview

1.  **Request Layer**: User submits parameters (start_date, department_id, etc.) to a module-specific Controller.
2.  **Service Layer**: The Controller calls a specialized Export Service (e.g., `AttendanceExportService`). This service **normalizes** parameters into a standardized `filters` payload.
3.  **System Layer**: The `ReportService` creates a `Task` (for UI tracking) and a `Report` record, then dispatches `ProcessExportJob`.
4.  **Execution Layer**: `ProcessExportJob` dynamically resolves the Export Class and View from `config/reports.php`.
5.  **Feedback**: The Job broadcasts progress (0-100%) and messages to the user via **Laravel Reverb**.

---

## 🛠️ Components

### 1. The Registry: `config/reports.php`
Every report must be registered here to be "discoverable" by the background job.
```php
'map' => [
    'team_report' => [
        'class' => \App\Modules\Attendance\Exports\TeamAttendanceExport::class,
        'view' => 'exports.attendance.team_report_pdf', // For PDF
        'txt_view' => 'exports.attendance.team_report_txt', // Optional: For custom TXT layouts
    ],
]
```

### 2. The Engine: `ProcessExportJob.php`
Handles formatting logic:
- **PDF**: Renders a Blade view to a string and saves it.
- **TXT**: If `txt_view` is defined, it renders a Blade view as plain text. Otherwise, it falls back to a standard tabular format.
- **Excel/CSV**: Uses `Maatwebsite/Excel` to process the data in chunks.

---

## 🚀 Adding a New Report: Step-by-Step

### 1. The Export Class
Create a class in `app/Modules/[Module]/Exports/`.
- **Query**: Use `FromQuery` for large datasets. **Always** specify an `orderBy` clause (required for chunking).
- **Filters**: Access input parameters via `$this->filters`.
- **Progress**: Call `$this->job->updateProgress($percentage, $message)` inside `map()` or events.

### 2. The Filter Pattern (Crucial)
To maintain consistency with the `Repository` and `whereIn` queries, the `ExportService` should map incoming params to **singular array keys**:
```php
'filters' => [
    'department_id' => isset($params['department_id']) ? [$params['department_id']] : [],
    'team_id' => isset($params['team_id']) ? [$params['team_id']] : [],
]
```
The Repository then uses these keys:
```php
if (!empty($filters['department_id'])) {
    $query->whereIn('department_id', $filters['department_id']);
}
```

### 3. Custom Layouts (PDF/TXT)
Create your templates in `resources/views/exports/`.
- **PDF**: Standard HTML/CSS (styled for DOMPDF).
- **TXT**: Plain text with Blade tags. Use `str_pad()` or fixed-width spacing for alignment.

---

## 📡 WebSocket Integration
The frontend listens on `private-reports.{user_id}` for progress updates.
- **Event**: `ReportProgressUpdated`
- **Payload**: Contains `progress` (int), `status` (string), and `download_url` (string).

## 💡 Best Practices
- **Memory**: For summary reports (Team/Dept), use a single query that aggregates in the database (`groupBy`, `COUNT`, `UNION`).
- **N+1**: Always eager load relationships in the `query()` method.
- **Stable Sort**: Every export query **must** have an `orderBy` to prevent data duplication across chunks.
