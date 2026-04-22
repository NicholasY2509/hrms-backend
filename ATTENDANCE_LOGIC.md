# Smart Attendance System - Logic Specification

This document outlines the business rules, time windows, and UI behaviors for the intelligent attendance system in the HRMS application.

## 1. Shift Resolution Logic (Backend)
The system uses a priority-based resolution system to determine the "active" or "next" shift for a user, regardless of calendar dates (supporting night shifts).

### Resolution Priorities:
1.  **Upcoming Shift (Priority 1)**:
    *   **Condition**: A shift where the user has NOT clocked in.
    *   **Window**: Current time is between **(Shift Start - 1 Hour)** and **(Shift Start + 4 Hours)**.
    *   **Behavior**: System returns this shift as the active session to allow "Clock In".

2.  **Unfinished Session (Priority 2)**:
    *   **Condition**: A shift where the user HAS clocked in but NOT yet clocked out.
    *   **Window**: Current time is before **(Shift End + 5 Hours)**.
    *   **Behavior**: System returns this shift to allow "Clock Out". This window accommodates up to 4 hours of overtime + 1 hour buffer.

3.  **Default Fallback**:
    *   **Condition**: Outside of the above windows.
    *   **Behavior**: System returns Today's scheduled shift (if any) or Yesterday's shift as a fallback to show shift information in the UI, even if actions are locked.

---

## 2. Business Rule Windows
These windows are strictly enforced on both the Backend (API) and Frontend (Flutter).

| Action | Window Start | Window End | Grace/Buffer |
| :--- | :--- | :--- | :--- |
| **Clock In** | Shift Start - 1 Hour | Shift Start + 4 Hours | 1h Early limit |
| **Clock Out** | Any time after Clock In | Shift End + 5 Hours | 4h OT + 1h Buffer |

---

## 3. UI Behaviors (Flutter)

### Dashboard Punch Card
*   **Locked State**: If `now < Shift Start - 1 Hour`, the button is disabled.
*   **Visuals**:
    *   Text: `"BELUM TERSEDIA"`
    *   Icon: `Icons.lock_clock_rounded`
    *   Subtitle: `"Tersedia jam HH:mm"` (Shows the window opening time).
*   **Enabled State**: If within the window, shows `"ABSEN MASUK SEKARANG"`.

### Attendance History FAB
*   **Behavior**: The Floating Action Button to navigate to the clock screen is **hidden** if the user is outside the 1-hour early window and not currently clocked in.

### Attendance Action Page (Global Guard)
*   **Behavior**: A full-page guard is active. If the user reaches this page outside the shift window:
    *   Hides all verification components (Location/Camera).
    *   Displays a large lock icon with the message: `"Shift belum tersedia. Silahkan tunggu hingga jam HH:mm."`
    *   Provides a "Kembali" button to exit.

---

## 4. Night Shift Handling
The system automatically detects shifts that cross midnight (e.g., 21:00 to 08:00).
*   **Calculation**: If `Clock Out` time is numerically less than `Clock In` time, the system treats the `Shift End` as occurring on the following day relative to the `Shift Start`.
*   **Display**: UI correctly formats dates and times for overnight shifts (e.g., *"21 Apr 21:00 - 22 Apr 08:00"*).

---

## 5. Security Enforcements
*   **Backend Validation**: The API will throw an `ApplicationException` if any clock action is received outside the allowed windows, even if the UI is bypassed.
*   **Geo-Fencing**: Validates user coordinates against the registered office location.
*   **Face Verification**: Mandatory biometric check for both Clock In and Clock Out.
