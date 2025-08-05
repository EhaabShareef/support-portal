# RBAC Review

This document summarizes potential issues found in the role‑based access control (RBAC) implementation using Spatie's permission package.

## Findings

### 1. Super Admin role excluded from admin routes
Admin routes use middleware that allows only the `Admin` role, preventing users with the `Super Admin` role from accessing management features.

- `routes/web.php` limits the admin route group to `role:Admin`【F:routes/web.php†L55-L60】
- The sidebar shows the Users link only to `Admin`, hiding it from `Super Admin` users【F:resources/views/components/sidebar.blade.php†L16-L21】

### 2. Policies rely solely on role names and omit Super Admin
Policies check for specific roles instead of permissions and ignore the `Super Admin` role.

- `UserPolicy` grants abilities only to users with the `Admin` role【F:app/Policies/UserPolicy.php†L15-L17】【F:app/Policies/UserPolicy.php†L46-L49】
- `TicketPolicy` similarly relies on role names and references invalid ticket fields `dept_id` and `org_id`, which do not exist, so the policy will never authorize correctly【F:app/Policies/TicketPolicy.php†L31-L37】

### 3. Direct database query for admin check
The `User` model implements `isAdmin()` using a raw database query rather than Spatie's role helpers, which is inconsistent with the rest of the code and bypasses guard/team awareness【F:app/Models/User.php†L106-L116】

### 4. Role management UI excludes Super Admin
The user management component restricts selectable roles to `Admin`, `Agent`, and `Client`, meaning Super Admins cannot be assigned through the UI【F:app/Livewire/Admin/ManageUsers.php†L33-L59】

### 5. Missing authorization inside Livewire actions
Actions that create or update organizations do not perform permission checks when executed, relying solely on UI gating. An attacker could invoke these methods directly.

- `save()` lacks any `canCreate`/`canEdit` verification【F:app/Livewire/ManageOrganizations.php†L135-L154】

### 6. Inconsistent role and permission checks in Livewire components
Some components rely on `hasRole` while others use `can`, leading to duplicated logic and potential oversights.

- Example: `ViewUser` checks for specific roles instead of permissions when mounting and determining edit capability【F:app/Livewire/Admin/ViewUser.php†L24-L28】【F:app/Livewire/Admin/ViewUser.php†L60-L63】
- `ViewTicket` repeatedly enumerates roles rather than using permissions, making the code harder to maintain【F:app/Livewire/ViewTicket.php†L130-L137】

### 7. Undefined permissions referenced in views
Blade templates reference permissions that are not created in the seed data, rendering the checks ineffective.

- `@can('tickets.show')` used in the organization tickets tab, but only `tickets.view` exists in the seeder【F:resources/views/livewire/partials/organization/tickets-tab.blade.php†L64-L70】【F:database/seeders/BasicDataSeeder.php†L56-L66】
- `contracts.*` and `hardware.*` permissions appear in views but are absent from the seed list【F:resources/views/livewire/partials/organization/contracts-tab.blade.php†L9-L60】【F:resources/views/livewire/partials/organization/hardware-tab.blade.php†L9-L14】

## Testing

`composer install` was attempted to run the test suite, but package downloads from GitHub failed due to network/authorization issues, preventing test execution【b5bba8†L1-L13】

## Recommendations

- Use permission middleware (e.g., `can:`) or include `Super Admin` in route middleware definitions.
- Refactor policies and components to leverage permissions instead of hard‑coded role names.
- Correct field references in `TicketPolicy` (`department_id`, `organization_id`).
- Replace the `isAdmin()` database query with Spatie's `hasRole` or `hasAnyRole` helper.
- Add explicit authorization checks inside mutating Livewire methods.
- Define all permissions referenced in views, or adjust views to use existing permissions.
- Centralize role and permission definitions to minimize duplication.
