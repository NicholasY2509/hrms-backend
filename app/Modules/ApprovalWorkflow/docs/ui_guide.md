# Approval Workflow Frontend Design Guide

This document outlines the recommended UI layout and user experience for managing the centralized approval system.

---

## 1. Approval Groups Management
**Purpose**: Manage pools of employees who can approve as a collective (e.g., 'HRD Staff').

### Layout
- **Table View**: List all groups (Name, Description, Member Count).
- **Manage Members Action**:
    - Opens a **Drawer** or **Modal**.
    - **Search Bar**: Use `GET /api/v1/employee/search` to find employees by name or NIK.
    - **Checklist**: Select/deselect employees to sync with the group.
    - **Display**: Show current members (name, position, NIK) at the top.

---

## 2. Policy Configuration (The Chain Builder)
**Purpose**: Define the approval steps for specific work positions and request types.

### Page Structure
- **Filter Section**: Select **Request Type** (e.g., UnpaidLeave) and **Work Position** (e.g., Sales Executive).
- **Policy Header**: Display policy name and an "Active/Inactive" toggle.

### The "Step Builder" (Visual Timeline)
Use a vertical timeline or a drag-and-drop list for steps:

#### Each Step Card should include:
1.  **Sequence Number**: (1, 2, 3...).
2.  **Step Type Selector**:
    - `Supervisor`: Automatic dynamic routing based on employee hierarchy.
    - `Dept Head`: Automatic dynamic routing based on department.
    - `Group`: Pick an **Approval Group** (e.g., 'HRD Staff').
    - `Specific Employee`: Search and pick a specific person.
3.  **Actions**: Delete step, Move up/down.

---

## 3. The Unified "Approval Inbox"
**Purpose**: A single place for any user to see what they need to approve.

### Layout
- **Tabs**:
    - `Pending My Action`: Tasks assigned explicitly to me or my groups.
    - `In Progress`: Tasks I've acted on that are still waiting for others.
    - `History`: My past decisions (Approved/Rejected).
- **List Item**:
    - Display Request Type (icon), Requester Name, Date, and current Step.
    - **Action Buttons**: Quick "Approve" / "Reject" buttons.
    - **Detail Link**: Opens the specific module record (e.g., the Leave details).

---

## 4. API Integration Tips
-   Use `GET /api/v1/employee/search` to find people for groups or specific steps.
-   Use `POST /api/v1/approvalworkflow/policies/{id}/steps` to save the entire chain.
-   **Note**: All IDs used for members and targets are **Employee IDs**, not User IDs.

---

## 5. API Endpoint Reference

### Approval Groups
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/approvalworkflow/groups` | List groups (paginated) |
| `POST` | `/api/v1/approvalworkflow/groups` | Create new group |
| `GET` | `/api/v1/approvalworkflow/groups/{id}` | Get group details + members |
| `POST` | `/api/v1/approvalworkflow/groups/{id}/sync-employees` | Sync employees in group |
| `DELETE` | `/api/v1/approvalworkflow/groups/{id}` | Delete group |

### Approval Policies
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/approvalworkflow/policies` | List policies (paginated) |
| `POST` | `/api/v1/approvalworkflow/policies` | Create new policy |
| `GET` | `/api/v1/approvalworkflow/policies/{id}` | Get policy details + steps |
| `POST` | `/api/v1/approvalworkflow/policies/{id}/steps` | Sync/Update policy steps |
| `DELETE` | `/api/v1/approvalworkflow/policies/{id}` | Delete policy |

### Master Data (Step Types)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/approvalworkflow/step-types` | List all available step types |
| `POST` | `/api/v1/approvalworkflow/step-types` | Create new step type |
| `PATCH` | `/api/v1/approvalworkflow/step-types/{id}` | Update step type |
| `DELETE` | `/api/v1/approvalworkflow/step-types/{id}` | Delete step type |

### Employee Utilities
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/employee/search` | Search employees by name/NIK (General utility) |
