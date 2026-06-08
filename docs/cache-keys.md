# Redis Cache Keys Documentation

This document serves as a central registry for all Redis cache keys used across the HRMS application. Having a single source of truth helps prevent cache key collisions and makes it easier for developers to manually clear specific caches during debugging.

## System & Infrastructure

| Cache Key Pattern | Expiration | Location | Description |
| :--- | :--- | :--- | :--- |
| `gcs_temp_url_{md5_path}` | 55 Minutes | `StorageService` | Caches the generated temporary signed URL for Google Cloud Storage assets (like avatars and attachments) to avoid hitting the GCS API repeatedly. |
| `user_auth_{tokenHash}` | 6 Hours | `AuthSyncService` | Caches the parsed JWT payload and fetched remote roles to avoid repetitive HTTP calls to the central Passport authentication server. |

## Authentication & User Session

| Cache Key Pattern | Expiration | Location | Description |
| :--- | :--- | :--- | :--- |
| `auth_me_user_{userId}` | 60 Minutes | `AuthController`, `EmployeeService` | Stores the fully loaded user profile (including relationships and roles). Cleared automatically when an employee updates their own details (e.g., insurance). |

## Employee Management

| Cache Key Pattern | Expiration | Location | Description |
| :--- | :--- | :--- | :--- |
| `employees_management_index_{hash}` | 30 Minutes | `EmployeeManagementController` | Caches the highly-filtered, paginated JSON response for the HR/Management Employee List page. The hash is generated from `perPage`, `page`, and all active `filters`. |
| `employee_management_summary` | 30 Minutes | `EmployeeManagementController` | Caches the raw aggregate data (count by status) for the employee summary cards. |

## Employee Portal (Self-Service)

| Cache Key Pattern | Expiration | Location | Description |
| :--- | :--- | :--- | :--- |
| `employee_dashboard_{userId}` | 5 Minutes | `MyDashboardController`, `MyAttendanceController` | Caches the individual employee's dashboard widgets (leave balances, attendance stats, upcoming holidays). Automatically cleared when the employee clocks in/out. |

## Configuration & Core Domain

| Cache Key Pattern | Expiration | Location | Description |
| :--- | :--- | :--- | :--- |
| `payroll_salary_components` | Forever | `SalaryComponentService` | Caches all active payroll salary components globally. Automatically cleared when a new component is created, updated, or deleted. |
| `all_holidays_array` | Forever | `HolidayService` | Caches the system-wide list of holidays to speed up working-day calculations for leaves and attendance. Automatically cleared when HR modifies the holiday calendar. |
| `attendance_settings_calculation` | Forever | `AttendanceCalculationSettingController` | Caches global attendance rules (e.g., late tolerance, overtime minimums). Automatically cleared when settings are updated. |

---
*Note: If you are ever stuck trying to figure out why data isn't updating locally, you can clear all of these manually by running `php artisan cache:clear`.*
