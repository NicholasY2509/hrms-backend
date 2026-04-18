---
trigger: always_on
---

# Backend Architecture Rules (Modular Laravel)

These rules define the architectural standards and coding patterns for the backend project . The Antigravity agent (and any other AI assistants) must strictly adhere to these guidelines when suggesting, generating, or refactoring code.

## 1. Architectural Paradigm: Modular Monolith
The application is organized into self-contained modules located in `app/Modules`. Each module represents a specific domain or feature set.

### Directory Structure
```text
app/Modules/[ModuleName]/
 ├── Controllers/     # Lean controllers (delegate to Services).
 ├── Services/        # Business logic and cross-module interaction.
 ├── Repositories/    # Data access logic (Eloquent queries).
 ├── Models/          # Eloquent models specific to the module.
 ├── Resources/       # API Resources for JSON transformation.
 ├── Requests/        # Form Requests for validation.
 ├── Routes/          # Module-specific routes (loaded in Providers).
 ├── Events/          # Module-specific events.
 ├── Migrations/      # Database migrations for the module.
 └── Seeders/         # Database seeders for the module.
```

## 2. Layers & Responsibilities

### Controllers
- **Rule**: Keep controllers lean. They should only handle HTTP concerns (request input, session/auth context, and returning responses).
- **Rule**: Delegate all business logic to **Services**.
- **Rule**: Use Type-Hinting in the constructor to inject Services.
- **Rule**: Use API Resources for formatting all JSON responses.
- **Documentation**: Every public method MUST have PHPDoc comments with `@group`, `@bodyParam`, and `@response` for documentation generation.

### Services
- **Rule**: Services contain the core business logic.
- **Rule**: They should be transaction-aware (use `DB::transaction` where appropriate).
- **Rule**: They should broadcast updates or fire events for side effects.
- **Rule**: Inject Repositories via the constructor.

### Repositories
- **Rule**: Encapsulate all Eloquent queries. 
- **Rule**: Methods should return Models, Collections, or Paginators.
- **Rule**: Avoid putting complex business logic here; focus on data retrieval and persistence.

### Models
- **Rule**: Define relationships, appenders, and scopes within the Model.
- **Rule**: Keep property names consistent (snake_case).

## 3. Communication & Responses
- **Traits**: Use the `ApiResponses` trait in all controllers for consistent JSON structure.
- **Success Mapping**: 
  - `successResponse($data, $message, $code)`
- **Error Mapping**:
  - `errorResponse($message, $code)`
- **Inter-module**: Use Events/Listeners for decoupling. If a Service needs logic from another module, inject that module's Service.

## 4. Coding Standards
- **Naming**: 
  - Classes: PascalCase.
  - Methods/Variables: camelCase (except Model properties which are snake_case).
  - Routes: kebab-case.
- **Validation**: Use **Form Requests** (`app/Modules/Order/Requests/...`) for all validation logic. Do not validate inside controllers.
- **Real-time**: Use Laravel Echo/Broadcasting for live updates. Traits like `BroadcastsTableUpdates` should be utilized where status changes occur.

## Agent Behavior Instructions
- **Scaffolding**: When adding a new module, ALWAYS create the full directory structure: `Controllers`, `Services`, `Repositories`, `Models`, `Routes`, etc.
- **Module Discovery**: Before modifying code, check if the logic belongs to an existing module or requires a new one.
- **Consistency**: Follow the existing pattern of lean controllers and service-based logic. Never put complex queries directly in a Controller.
- **Docs**: Always update or include the `@group` and `@bodyParam` tags in controllers when adding/modifying endpoints.
