# Permissions Roadmap

## Overview
This document provides a roadmap for implementing a scalable, administrator‑friendly role and permission system using the [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/) package. It builds on the current implementation found in files such as `app/Livewire/Admin/ManageRoles.php`, `app/Livewire/Admin/ManageUsers.php`, and the `database/seeders/RolePermissionSeeder.php`.

The goals are:
- Standardize how roles and permissions are defined and assigned.
- Offer a dynamic interface for non‑technical administrators to manage permissions.
- Ensure all modules in the application can be controlled via CRUD‑style permissions.

## 1. Best Practices with Spatie Roles and Permissions
1. **Use Guard and Team Awareness**
   - Keep guards consistent (e.g., `web`) across the application. The configuration in `config/permission.php` already points models to Spatie defaults.
   - If departments or organizations represent "teams", consider enabling team support in `config/permission.php` and set the team via `SetDepartmentTeam` middleware when needed.
2. **Name Permissions by Module and Action**
   - Follow a `<module>.<action>` convention: `tickets.create`, `tickets.read`, `tickets.update`, `tickets.delete`.
   - Store a definitive list of modules and actions in a dedicated config file (e.g., `config/modules.php`) so seeding and UI generation can use a single source of truth.
3. **Cache Awareness**
   - Flush permission cache after changes by calling `app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions()` within seeders, tests, or artisan commands.
4. **Use Policies for Model Logic**
   - Leverage policies (e.g., `app/Policies/TicketPolicy.php`) to encapsulate model‑specific access rules beyond simple permission checks.
5. **Middleware and Blade Directives**
   - Protect routes with `role`/`permission` middleware (`routes/web.php`).
   - In Blade or Livewire components, prefer `@can`, `@cannot`, and `@role` directives for display logic.
6. **Seed Permissions and Roles Systematically**
   - Use a seeder like `database/seeders/RolePermissionSeeder.php` to create/update permissions and roles.
   - Centralize the modules/actions list so seeders remain deterministic and idempotent.

## 2. Admin Interface Plan
A new admin interface should offer clear, CRUD‑driven management of roles and permissions and expose user assignments.

### 2.1 Role Management
Located under a route such as `/admin/roles` using the existing Livewire component `app/Livewire/Admin/ManageRoles.php`.

1. **Create Roles**
   - Provide a simple form for role name, description, and guard (pre‑filled as `web`).
   - Validate uniqueness and prevent editing of system roles like `admin`.

2. **Define Permissions per Role (CRUD grid)**
   - Generate permissions automatically from the modules list.
   - Render a matrix: rows = modules, columns = actions (Create, Read, Update, Delete, Extras).
   - Allow toggling a whole row or individual cells. The existing `toggleAllPermissionsForModule` method can be extended for columns.
   - Persist selections via `$role->syncPermissions()`.

3. **Assign Roles to Users**
   - On saving a role, show how many users are currently using it (e.g., via `$role->users()->count()`).
   - Offer a link to filter ManageUsers by that role for quick reassignment before deletion.

4. **Audit View**
   - Add a "View Permissions" modal that shows consolidated permissions for the role, including inherited ones.

### 2.2 User Assignment Interface
Located under `/admin/users` leveraging `app/Livewire/Admin/ManageUsers.php`.

1. **Assign Roles to Users**
   - Continue using `assignRole` and `syncRoles`. Ensure form validation lists roles dynamically from the database.
2. **Audit Permissions per User**
   - Add a "View Access" action that displays the user's role and resolved permissions. Use `$user->getAllPermissions()`.
   - Highlight any discrepancies (e.g., user has permissions directly assigned outside the role).
3. **Bulk Actions**
   - Provide bulk activation/deactivation or role assignment to streamline admin tasks.

## 3. UI Structure Recommendations
1. **Permission Matrix**
   - Use a grid with modules on the Y‑axis and CRUD actions on the X‑axis.
   - Each cell is a toggle; entire rows/columns can be toggled for faster selection.
2. **Module Grouping**
   - Group related modules under expandable panels (e.g., Ticketing, Organizations, Settings) to avoid overwhelming the screen.
3. **Search and Filters**
   - Provide search across permissions and roles to quickly locate modules.
4. **Feedback and Audit Trails**
   - Display counts of users per role and last modified timestamps for roles and permissions.
5. **Accessibility**
   - Ensure toggles have labels and keyboard navigation support for non‑mouse users.

## 4. Backend Logic Improvements
1. **Policies**
   - Review existing policies (`app/Policies/*`) and ensure they authorize using `$user->can('module.action')` or dedicated policy methods.
   - Map models to policies in `app/Providers/AuthServiceProvider.php`.
2. **Middleware**
   - Confirm routes in `routes/web.php` use `->middleware(['role:Admin'])` or `permission:tickets.create` where appropriate.
   - Add a global middleware to set team context if using team features (`app/Http/Middleware/SetDepartmentTeam.php`).
3. **Model Helpers**
   - In `app/Models/User.php`, continue to `use HasRoles;` and consider helper methods like `isAdmin()` or `hasAnyRole()` for clarity.
4. **Command Utilities**
   - Consolidate artisan commands (`app/Console/Commands/*`) that manipulate roles into a single `permissions:sync` command to keep maintenance manageable.

## 5. Seeding and Migration Strategy
1. **Modules Configuration**
   - Create `config/modules.php` with an array of modules and supported actions.
2. **Migration for Existing Roles/Permissions**
   - Write a migration or seeder to map legacy permissions to the new `<module>.<action>` format.
   - Use `php artisan permission:migrate` or custom scripts to sync new permissions while preserving role assignments.
3. **Database Seeding**
   - Update `database/seeders/RolePermissionSeeder.php` to pull from `config/modules.php` instead of a hardcoded list.
   - Always call `PermissionRegistrar::class)->forgetCachedPermissions()` before and after seeding.
4. **Deployment Steps**
   - Add notes in deployment docs to run `php artisan migrate --seed` when permissions change.
5. **Testing**
   - Add feature tests under `tests/Feature/` verifying that each role has the expected permissions and that unauthorized actions are blocked.

## 6. Gaps and Pain Points Identified
- Permissions are hardcoded in seeders, making additions error‑prone.
- `ManageRoles` offers basic toggling but lacks a comprehensive CRUD matrix and column‑level toggles.
- No dedicated view for auditing permissions per user, leading to potential confusion.
- Policies exist for some models but may not cover all modules consistently.
- Dependency on artisan commands (`CheckUserRoles`, `FixUserRoles`, etc.) suggests manual intervention.

## 7. Actionable Roadmap Summary
1. **Centralize modules/actions in `config/modules.php`.**
2. **Refactor `RolePermissionSeeder` to generate permissions from this config.**
3. **Enhance `ManageRoles` UI with a permission matrix and column toggles.**
4. **Extend `ManageUsers` with a "View Access" panel and bulk role assignment.**
5. **Introduce auditing features (counts, timestamps, logs) for transparency.**
6. **Standardize policy and middleware usage across all modules.**
7. **Document deployment and seeding steps for future role or permission changes.**

This roadmap aims to provide a clear path toward a maintainable, user‑friendly authorization system that scales with the application.

