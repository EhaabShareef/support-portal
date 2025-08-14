# Project Scope Audit

## Executive Summary
- **Top Risks**
  - Duplicate migrations and seeders risk inconsistent schema and seeding (e.g., two `hardware_types` migrations and two hardware type seeders) leading to drift between environments.
  - Legacy Blade views (`resources/views/tickets`) remain alongside new Livewire tickets module and could reintroduce outdated UI or authorization gaps if referenced.
  - Dynamic column sorting in ticket management lacks safelist and could allow SQL‑style injection if parameters are tampered with.
- **Quick Wins**
  - Consolidate hardware‑type migrations/seeders and prune legacy ticket views.
  - Enforce a centralized card/layout component to remove styling drift across Livewire views.
- **Estimated Effort**: ~3 days engineering + design review.

## Inventory Snapshot
- **Routes / Entry Points** (`routes/web.php`)
  - Dashboard `/dashboard`
  - Organizations `/organizations`, `/organizations/{organization}`
  - Contracts `/contracts/manage/{organization}`
  - Hardware `/hardware/manage/{organization}`, `/organizations/{org}/hardware/create`
  - Users `/users/manage/{organization}`
  - Tickets `/tickets/manage`, `/tickets/{ticket}`, `/tickets/create`
  - Profile `/profile`
  - Schedule `/schedule`
  - Attachments `/attachments/{uuid}/{action}`
  - Admin `/admin/users-roles`, `/admin/settings`, `/admin/reports/*`
- **Models**: `ActivityLog`, `Attachment`, `ContractStatus`, `ContractType`, `DashboardWidget`, `Department`, `DepartmentGroup`, `HardwareSerial`, `HardwareStatus`, `HardwareType`, `Organization`, `OrganizationContract`, `OrganizationHardware`, `Role`, `Schedule`, `ScheduleEventType`, `Setting`, `Ticket`, `TicketMessage`, `TicketNote`, `TicketStatus`, `User`, `UserWidgetSetting`
- **Policies**: `RolePolicy`, `SchedulePolicy`, `ScheduleEventTypePolicy`, `TicketPolicy`, `TicketNotePolicy`, `UserPolicy`
- **Livewire Components**: Organization/Contract/Hardware/User management, Ticket management/viewing, Dashboard widgets, Admin settings, Reports, Auth login, Schedule calendar, etc.
- **Views**: Components (`resources/views/components`), Livewire views (`resources/views/livewire`), legacy ticket views (`resources/views/tickets`), auth views (`resources/views/auth`), error views.
- **Migrations**: 40+ files including core tables and later refactors; notable duplicates around hardware types and settings.
- **Seeders**: `RolePermissionSeeder`, `BasicDataSeeder`, `HardwareTypesSeeder` *and* `HardwareTypeSeeder`, contract/hardware/ticket lookups, widgets, application settings.
- **JS / Alpine**: `resources/js/app.js` handles dark‑mode toggle; Alpine loaded via Livewire.
- **Styles**: Tailwind via `resources/css/app.css`, `tailwind.config.js`.
- **Config**: Permission modules (`config/modules.php`), theme, loading overlay, services.

## Findings

### Bugs
1. **Unvalidated sort columns in ticket listing**
   - File Path: `app/Livewire/ManageTickets.php`
   - Line Number(s): 121-129
   - Category: Bug
   - Observed: `sortBy($field)` assigns `$field` directly to `$this->sortBy` which is later used in `orderBy` without validation.
   - Expected: Sort fields should be limited to a predefined allowlist.
   - Impact/Severity: Medium – tampering via query string could expose SQL injection or broken queries.
   - Suggested Fix: Validate `$field` against a set of allowed columns before assignment.
   - Repro/Verification Steps: Modify query string `?sortBy=1 desc` and observe SQL error.
   - Minimal Diff or Pseudocode:
     ```php
     $allowed = ['ticket_number','subject','created_at'];
     if (in_array($field,$allowed)) { $this->sortBy = $field; }
     ```
   - Tests to Add/Update: Livewire component test ensuring only safelisted fields are accepted.

2. **Duplicate form validation rules**
   - File Path: `app/Livewire/ManageTickets.php`
   - Line Number(s): 75-93
   - Category: Bug
   - Observed: Both `$rules` property and `rules()` method define validation, risking divergence.
   - Expected: Single source of truth for rules.
   - Impact/Severity: Low – maintainability issue.
   - Suggested Fix: Remove `$rules` property and keep `rules()` method.
   - Repro/Verification Steps: Update one rule and see tests diverge.
   - Minimal Diff or Pseudocode:
     ```php
     // remove protected $rules; rely on rules() only
     ```
   - Tests to Add/Update: Component validation tests.

### Deprecated
1. **Legacy ticket Blade views still present**
   - File Path: `resources/views/tickets/*`
   - Line Number(s): `show.blade.php` 1-40
   - Category: Deprecated
   - Observed: Old ticket pages remain although routes use Livewire components.
   - Expected: Remove or archive unused views.
   - Impact/Severity: Medium – risk of accidental routing to outdated UI.
   - Suggested Fix: Delete `resources/views/tickets` directory; keep redirect in routes if deep links exist.
   - Repro/Verification Steps: Search routes for references; none found.
   - Minimal Diff or Pseudocode: `git rm resources/views/tickets -r`
   - Tests to Add/Update: None.

2. **Redundant hardware type seeder**
   - File Path: `database/seeders/HardwareTypesSeeder.php`
   - Line Number(s): 1-16
   - Category: Deprecated
   - Observed: Simplistic seeder duplicates newer `HardwareTypeSeeder`.
   - Expected: Use single, fully‑featured seeder.
   - Impact/Severity: Medium – inconsistent hardware types between environments.
   - Suggested Fix: Remove `HardwareTypesSeeder` and adjust `DatabaseSeeder` ordering.
   - Repro/Verification Steps: `php artisan db:seed` would seed both sets.
   - Minimal Diff or Pseudocode: `git rm database/seeders/HardwareTypesSeeder.php`
   - Tests to Add/Update: Seeder execution test.

### Style
1. **Inconsistent card/background styles across Livewire views**
   - File Path: `resources/views/livewire/manage-tickets.blade.php`
   - Line Number(s): 1-20
   - Category: Style
   - Observed: Uses `bg-white/5 backdrop-blur-md shadow-md`; other modules use solid `bg-neutral-50` cards.
   - Expected: Standardized card component (e.g., `<x-card>` with consistent padding, borders, dark-mode classes).
   - Impact/Severity: Medium – visual inconsistency, harder theme updates.
   - Suggested Fix: Introduce reusable card Blade component and refactor views to use it.
   - Repro/Verification Steps: Compare ticket list vs settings tabs.
   - Minimal Diff or Pseudocode:
     ```blade
     <x-card>
       <x-slot:title>Support Tickets</x-slot:title>
       ...
     </x-card>
     ```
   - Tests to Add/Update: Snapshot/UI tests if available.

2. **Settings tabs mix neutral and sky color schemes**
   - File Path: `resources/views/livewire/admin/settings/tabs/general.blade.php`
   - Line Number(s): 8-26
   - Category: Style
   - Observed: Action buttons use neutral and sky palettes interchangeably.
   - Expected: Follow project monochrome theme; reserve sky accent for primary actions.
   - Impact/Severity: Low – inconsistent user experience.
   - Suggested Fix: Define design tokens for button variants; update tab templates.
   - Repro/Verification Steps: Inspect settings UI.
   - Minimal Diff or Pseudocode: replace `bg-neutral-600` with standardized `btn-secondary` component.
   - Tests to Add/Update: None.

### Migration/Seeder
1. **Duplicate `hardware_types` migrations**
   - File Path: `database/migrations/2025_01_01_000011_create_hardware_types_table.php` & `database/migrations/2025_08_13_174623_create_hardware_types_table.php`
   - Line Number(s): 1-22; 1-36
   - Category: Migration/Seeder
   - Observed: Two migrations create the same table with different schemas.
   - Expected: Single migration defining final structure; earlier migration should be removed or merged.
   - Impact/Severity: High – running migrations may fail on fresh installs.
   - Suggested Fix: Consolidate into one migration; drop old file and renumber.
   - Repro/Verification Steps: `php artisan migrate` fails after first create.
   - Minimal Diff or Pseudocode: delete obsolete migration and adjust references.
   - Tests to Add/Update: Migration test ensuring table has slug/description columns.

2. **Seeder order seeds obsolete data**
   - File Path: `database/seeders/DatabaseSeeder.php`
   - Line Number(s): 21-30
   - Category: Migration/Seeder
   - Observed: Seeder calls both `HardwareTypesSeeder` and `HardwareTypeSeeder`.
   - Expected: Only modern `HardwareTypeSeeder` should run.
   - Impact/Severity: Medium – conflicting records may cause duplicates.
   - Suggested Fix: Remove `HardwareTypesSeeder` from array.
   - Repro/Verification Steps: `php artisan db:seed` (on clean DB) shows duplicate hardware types.
   - Minimal Diff or Pseudocode:
     ```php
     $this->call([
         RolePermissionSeeder::class,
         BasicDataSeeder::class,
         HardwareTypeSeeder::class,
         // ...
     ]);
     ```
   - Tests to Add/Update: Seeder idempotence test.

## Test Plan
- Feature tests for ticket sort allowlist and creation flow.
- Policy tests for attachment access and ticket status/priority transitions.
- Seeder/migration test ensuring fresh database builds without duplicates.
- Livewire component tests for dashboard widgets and settings tabs.

## Cleanup Checklist
- [ ] Remove `resources/views/tickets` directory and related assets.
- [ ] Consolidate hardware type migrations; run `php artisan migrate:fresh` to verify.
- [ ] Delete `HardwareTypesSeeder` and update `DatabaseSeeder`.
- [ ] Introduce shared card/button components and refactor affected views.
- [ ] Add sorting allowlist and validation tests for `ManageTickets`.
- [ ] Review all migrations for duplicate timestamps and rename sequentially.

