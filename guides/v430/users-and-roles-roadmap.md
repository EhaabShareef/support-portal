# Users & Roles Consolidation Roadmap

This document outlines how to merge the existing **Users** and **Roles** admin sections into a single "Users & Roles" destination with two tabs.

## 1. Current Structure

| Area | File & Lines | Notes |
|------|--------------|-------|
| Admin routes | `routes/web.php` lines 82‑90 | Separate `/admin/users` and `/admin/roles` routes guarded by the `role:admin` middleware. |
| Sidebar links | `resources/views/components/sidebar.blade.php` lines 16‑27 | Two distinct navigation links for Users and Roles in the admin block. |
| Top nav links | `resources/views/components/navigation.blade.php` lines 54‑67 and 217‑231 | Desktop and mobile menus duplicate the Users and Roles links. |
| Manage users component | `app/Livewire/Admin/ManageUsers.php` lines 33‑43 and 430‑461 | Handles user CRUD and filtering; defaults new users to the `client` role. |
| Manage roles component | `app/Livewire/Admin/ManageRoles.php` lines 104‑134 | Provides role CRUD and permission assignment; only flashes an error when editing the Admin role. |
| View user component | `app/Livewire/Admin/ViewUser.php` lines 48‑56 and 214‑218 | Builds a toggleable permission matrix and blocks direct permission updates. |
| View user blade | `resources/views/livewire/admin/view-user.blade.php` lines 222‑244 | Renders checkboxes allowing permission toggling. |
| User policy | `app/Policies/UserPolicy.php` lines 15‑48 | Restricts user management actions to admins. |

## 2. Combined Page Implementation

1. **New Livewire wrapper**
   - Create `app/Livewire/Admin/UsersRoles.php` with an `activeTab` property (defaults to `users`) and query‑string support.
   - View: `resources/views/livewire/admin/users-roles.blade.php` containing a two‑tab layout. Each tab mounts the existing components: `<livewire:admin.manage-users />` and `<livewire:admin.manage-roles />`.

2. **Routes & redirects**
   - Replace individual admin routes with a single route:
     ```php
     Route::get('/users-roles', UsersRoles::class)->name('users-roles.index');
     ```
   - Keep legacy routes as redirects:
     ```php
     Route::get('/users', fn() => redirect()->route('admin.users-roles.index', ['tab' => 'users']))
         ->name('users.index');
     Route::get('/roles', fn() => redirect()->route('admin.users-roles.index', ['tab' => 'roles']))
         ->name('roles.index');
     ```
   - Update all internal links (breadcrumbs, tests, etc.) to point to `admin.users-roles.index` with the appropriate `tab` parameter.

3. **Navigation rename**
   - Replace the two admin links in the sidebar and top navigation with a single "Users & Roles" entry targeting `route('admin.users-roles.index')`.
   - Ensure both desktop and mobile menus are updated.

4. **Breadcrumbs**
   - In `resources/views/livewire/admin/view-user.blade.php`, change the "Back to Users" link to `route('admin.users-roles.index', ['tab' => 'users'])`.

## 3. Users Tab Adjustments

1. **Hide client users**
   - In `ManageUsers::render()` add a base scope excluding the `client` role and restrict `availableRoles` to `admin` and `support`.
   - Update default form role to `support` so new admin users are not accidentally created as clients.
   - Apply the same default in `resetForm()`.

2. **Preserve all other CRUD and filtering features** so the tab behaves identically to the old `/admin/users` page.

## 4. View User Permission Display

1. Replace the permission matrix with a read‑only card list:
   - Modify `ViewUser::syncPermissions()` to group permissions by module and expose a simple array such as `['tickets' => ['create', 'view'], ...]`.
   - Remove checkbox markup in `view-user.blade.php`; instead render each module as a card listing the allowed actions.
   - Drop the `updatePermission()` method entirely to ensure permissions are not mutable from the UI or component.

## 5. Roles Tab & Admin Role Lock

1. **UI warning**
   - When `$editMode` and `$form['name'] === 'admin'`, show a warning banner in `manage-roles.blade.php`, disable all form fields and hide action buttons.

2. **Backend guard**
   - Add `app/Policies/RolePolicy.php` with `update` and `delete` methods returning `false` when the role name is `admin`.
   - Register the policy in `AuthServiceProvider` and authorize `ManageRoles` actions through `Gate::authorize` calls.
   - Keep the existing guard in `saveRole()` as a secondary check.

## 6. Legacy Cleanup & Deprecations

After the combined page ships:

- Remove unused menu strings and duplicated links for `admin.users.index` and `admin.roles.index`.
- Verify no other components link to the old routes; update tests accordingly.
- Consider deprecating `app/Models/Role.php` if all role operations rely exclusively on Spatie's `Role` model.

## 7. Migration & Test Plan

1. **Migrations** – no database changes required.
2. **Manual tests**
   - Load `/admin/users-roles` and confirm both tabs render their respective components.
   - Verify Users tab excludes client users and new user form defaults to support/admin roles only.
   - View a user and confirm the Permissions tab shows a read‑only list derived from their role.
   - Navigate to old `/admin/users` and `/admin/roles` URLs and confirm redirect to the combined page with the correct tab selected.
   - Attempt to edit the Admin role; the UI should display a warning and the server should block the action.
3. **Automated tests**
   - Update or add feature tests covering tab switching, client user filtering, read‑only permissions, redirect behavior, and admin role immutability.
   - Run `phpunit` to ensure the suite passes.

