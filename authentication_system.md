# Authentication System Overview

The HRMS Backend utilizes JSON Web Tokens (JWT) for API authentication, implemented using the `php-open-source-saver/jwt-auth` package. The authentication process is centralized around the `User` model, which acts as the primary authenticatable entity.

## Configuration

The authentication defaults and guards are configured in `config/auth.php`. 

### Guards
- **API Guard (`api`)**: This is the primary guard for authenticating incoming API requests. It uses the `jwt` driver and retrieves users via the `users` provider.
- **Web Guard (`web`)**: Uses the traditional `session` driver, though for API-first applications, the `api` guard is primarily utilized.

### User Provider
The `users` provider is configured to use the Eloquent driver, pointing to the `App\Modules\User\Models\User` model.

## User Model (`App\Modules\User\Models\User`)

The `User` model implements the `PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject` interface, which is required for JWT integration. It must define two key methods:

1.  **`getJWTIdentifier()`**: Returns the primary key of the user (typically the `id`).
2.  **`getJWTCustomClaims()`**: Returns an array of custom claims to be embedded within the JWT payload. 

### Custom JWT Claims (Roles)

The `getJWTCustomClaims` method is specifically designed to embed the user's authorization roles directly into the token:

```php
public function getJWTCustomClaims()
{
    // Extract roles from the user's employee work position if available
    $roles = [];
    $employee = $this->employee()->with('work_position.passportRoles')->first();
    if ($employee && $employee->work_position) {
        $roles = $employee->work_position->passportRoles->pluck('name')->toArray();
    }

    return [
        'roles' => $roles,
    ];
}
```

This ensures that once a token is decoded by the frontend or middleware, the user's assigned roles (derived from their current `work_position`) are immediately accessible without querying the database again.

## Relationships

The authentication entity (`User`) is linked to the core HR entity (`Employee`) through a pivot/mapping model:

-   A `User` has one `UserEmployee` mapping record.
-   A `User` is linked to an `Employee` record through a `hasOneThrough` relationship via the `UserEmployee` model.

## Security & Logging
-   The `password` and `remember_token` attributes are explicitly hidden when serializing the model.
-   The `password` attribute is automatically cast to `hashed`.
-   The model utilizes `Spatie\Activitylog\Traits\LogsActivity` to track all dirty changes, explicitly excluding sensitive fields like passwords and remember tokens from the activity logs.

## Roles & Permissions

The application implements a centralized Role-Based Access Control (RBAC) mechanism integrated with external Identity providers.

### 1. Passport Roles (`App\Modules\System\Models\PassportRole`)

Roles in this system are represented by `PassportRole`. These roles typically map to external authorization providers or internal SSO integrations:
- Contains an identifier (`passport_role_id`) for synchronization.
- Belongs to a specific client (`PassportClient`).
- Includes an `is_global` boolean flag indicating scope.

### 2. Role Assignment via Work Positions

Roles are not directly assigned to Users. Instead, they are bound to **Work Positions**:
- A `WorkPosition` (`App\Modules\Organization\Models\WorkPosition`) can have multiple `PassportRole`s.
- Employees are assigned to a `WorkPosition`.
- Consequently, an Employee inherits all the roles attached to their current `WorkPosition`.

### 3. Middleware Authorization (`RequireRole`)

The `RequireRole` middleware (`App\Http\Middleware\RequireRole`) intercepts incoming requests and evaluates the inherited roles:
- It is registered in `bootstrap/app.php` using the alias `role`.
- It expects an array of accepted roles (e.g., `->middleware('role:HR,Manager')`).
- Inside the middleware, it fetches the user's roles (decoded from the JWT payload).
- If the user has an `'IT'` role, they automatically bypass all role checks and are granted super-admin access.
- If they do not have the required role, the middleware aborts the request with a `403 Forbidden` JSON response.
*(Note: Currently, `RequireRole` contains a temporary bypass `return $next($request);` at the top of the handle method, presumably for development or migration purposes).*
