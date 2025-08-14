# Project Scope Audit

## Executive Summary
- **Top Risks**
  - Unsanitized sort column in ticket management allows arbitrary column names leading to potential SQL injection.
  - Role/permission seeding clears all user data and assignments, risking data loss when run outside a disposable environment.
  - Dynamic Tailwind classes for ticket colors are generated in PHP but not safelisted, causing missing styles in production builds.
  - Duplicate and deprecated migrations can create schema drift between environments.
- **Quick Wins**
  - Introduce an allowlist for sortable ticket columns and validate the requested field.
  - Remove destructive data wipes from `RolePermissionSeeder` or gate behind an environment check.
  - Add a Tailwind safelist for status/priority color utilities and adopt shared card/button components.
  - Prune legacy controllers, views, and migrations residing in `database/migrations/deprecated` and `resources/views/tickets`.
- **Estimated Effort**: ~4–5 days of engineering plus design review.

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
- **Middleware**: `SetDepartmentTeam`, `FilterByUserRole`
- **Policies**: `TicketPolicy`, `TicketNotePolicy`, `SchedulePolicy`, `ScheduleEventTypePolicy`, `RolePolicy`, `UserPolicy`
- **Models**: `ActivityLog`, `Attachment`, `ContractStatus`, `ContractType`, `DashboardWidget`, `Department`, `DepartmentGroup`, `HardwareSerial`, `HardwareStatus`, `HardwareType`, `Organization`, `OrganizationContract`, `OrganizationHardware`, `Role`, `Schedule`, `ScheduleEventType`, `Setting`, `Ticket`, `TicketMessage`, `TicketNote`, `TicketStatus`, `User`, `UserWidgetSetting`
- **Migrations**: 40+ files including core tables and later patches; deprecated copies reside in `database/migrations/deprecated`
- **Seeders**: `RolePermissionSeeder`, `BasicDataSeeder`, `UserSeeder`, `ScheduleEventTypeSeeder`, `ContractTypeSeeder`, `ContractStatusSeeder`, `HardwareTypeSeeder`, `HardwareStatusSeeder`, `TicketStatusSeeder`, `DashboardWidgetSeeder`, `UserWidgetSettingsSeeder`, `ApplicationSettingsSeeder`, `OrganizationContractSeeder`, `OrganizationHardwareSeeder`, `SampleTicketSeeder`, `ClientSampleDataSeeder`
- **Factories**: `UserFactory`
- **Livewire Components**: organization/contract/hardware/user management, ticket management/viewing, dashboard widgets, admin settings, reports, auth login, schedule calendar, etc.
- **Blade Views**: component library under `resources/views/components`, Livewire views under `resources/views/livewire`, legacy ticket views under `resources/views/tickets`, auth views, error views.
- **JS / Alpine**: `resources/js/app.js` handles dark‑mode toggle; Alpine loaded via Livewire.
- **Styles**: Tailwind via `resources/css/app.css`, `tailwind.config.js`.
- **Config**: permission modules (`config/modules.php`), theme, loading overlay, services.
- **Services**: `ThemeService`, `PermissionService`, `SettingsRepository`, `TicketColorService`, `HardwareValidationService`, `HotlineService`

## Findings

### Bugs
1. **Unvalidated sort columns in ticket listing**
   - File Path: `app/Livewire/ManageTickets.php`
   - Line Number(s): 121-130
   - Category: Bug
   - Observed: `sortBy()` assigns any provided field to `orderBy` without validation.
   - Expected: Only predefined columns should be sortable.
   - Impact/Severity: High – malicious input could expose internal columns or enable SQL‑style injection.
   - Suggested Fix: Introduce an allowlist of column names and reject others.
   - Repro/Verification Steps: Trigger component with `?sortBy=nonexistent` and inspect generated SQL.
   - Minimal Diff or Pseudocode:
     ```php
     private array $sortable = ['ticket_number','subject','priority','status','created_at'];
     public function sortBy($field) {
         if(!in_array($field,$this->sortable)) return;
         // existing logic
     }
     ```
   - Tests to Add/Update: Livewire test asserting invalid sort fields are ignored.

2. **Seeder wipes user data**
   - File Path: `database/seeders/RolePermissionSeeder.php`
   - Line Number(s): 45-67
   - Category: Bug
   - Observed: `clearExistingData()` deletes all users, roles, and permissions on every run.
   - Expected: Seeder should create/update roles/permissions without destructive wipes unless explicitly requested.
   - Impact/Severity: High – running seeder in non‑fresh environments causes irrecoverable data loss.
   - Suggested Fix: Guard destructive operations behind an environment check or artisan flag.
   - Repro/Verification Steps: Execute seeder on populated database and confirm user records are removed.
   - Minimal Diff or Pseudocode:
     ```php
     if(app()->environment('local')) {
         // destructive clears
     }
     ```
   - Tests to Add/Update: Seeder test ensuring existing users survive when environment ≠ local.

3. **Mass‑assignment gap when creating tickets**
   - File Path: `app/Livewire/ManageTickets.php`
   - Line Number(s): 169-178
   - Category: Bug
   - Observed: `$ticketData = $this->form;` passes entire form array to `Ticket::create()`; malicious clients could inject fillable fields like `owner_id`.
   - Expected: Only validated/authorized fields should be persisted.
   - Impact/Severity: Medium – could allow privilege escalation by assigning owners or statuses.
   - Suggested Fix: Use `only()` to select allowed keys before create.
   - Repro/Verification Steps: Modify payload via browser devtools to include `owner_id`.
   - Minimal Diff or Pseudocode:
     ```php
     $ticketData = collect($this->form)->only(['subject','priority','description','organization_id','client_id','department_id'])->toArray();
     ```
   - Tests to Add/Update: Livewire test ensuring unexpected fields are ignored.

4. **Attachment routes lack rate limiting**
   - File Path: `app/Http/Controllers/AttachmentController.php`
   - Line Number(s): 13-70
   - Category: Bug
   - Observed: Download and view endpoints are unaudited for request frequency.
   - Expected: File endpoints should apply throttling to prevent abuse.
   - Impact/Severity: Medium – attackers could hammer download/view routes causing bandwidth exhaustion.
   - Suggested Fix: Apply `throttle` middleware to attachment routes.
   - Repro/Verification Steps: Hit `/attachments/{uuid}/download` rapidly and observe no throttling.
   - Minimal Diff or Pseudocode:
     ```php
     Route::middleware(['auth','throttle:60,1'])->group(function() {
         // attachment routes
     });
     ```
   - Tests to Add/Update: Feature test verifying excessive requests hit rate limit.

### Deprecated
1. **Legacy ticket views**
   - File Path: `resources/views/tickets/*`
   - Line Number(s): n/a
   - Category: Deprecated
   - Observed: Old Blade templates remain alongside new Livewire ticket module.
   - Expected: Only Livewire-based ticket views should exist.
   - Impact/Severity: Medium – unused files cause confusion and risk being referenced inadvertently.
   - Suggested Fix: Remove `resources/views/tickets` directory after confirming no routes reference it.
   - Repro/Verification Steps: Grep codebase for `resources/views/tickets` references; none found.
   - Minimal Diff or Pseudocode: `rm -r resources/views/tickets`
   - Tests to Add/Update: N/A

2. **Unused organization controllers**
   - File Path: `app/Http/Controllers/OrganizationController.php`, `OrganizationHardwareController.php`, `OrganizationContractController.php`
   - Line Number(s): n/a
   - Category: Deprecated
   - Observed: Controllers provide CRUD views replaced by Livewire components and are no longer routed.
   - Expected: Livewire components manage these flows.
   - Impact/Severity: Low – dead code increases maintenance burden.
   - Suggested Fix: Delete controllers after confirming no references.
   - Repro/Verification Steps: Search routes for these controllers; none found.
   - Minimal Diff or Pseudocode: remove files.
   - Tests to Add/Update: N/A

3. **Deprecated migrations folder**
   - File Path: `database/migrations/deprecated/*`
   - Line Number(s): n/a
   - Category: Deprecated
   - Observed: Old migration copies (e.g., `create_schedules_table.php`) linger in repository.
   - Expected: Obsolete migrations should be purged to prevent accidental execution.
   - Impact/Severity: Medium – risk of running outdated schema changes.
   - Suggested Fix: Delete folder or move to documentation.
   - Repro/Verification Steps: `php artisan migrate` with `--path=database/migrations/deprecated` recreates stale tables.
   - Minimal Diff or Pseudocode: `rm -r database/migrations/deprecated`
   - Tests to Add/Update: N/A

### Style
1. **Inline card/button styles bypass shared components**
   - File Path: `resources/views/livewire/manage-tickets.blade.php`
   - Line Number(s): 1-80
   - Category: Style
   - Observed: View uses bespoke utility classes instead of provided `glass-card` and `btn` classes from `resources/css/app.css`.
   - Expected: Layouts should consume shared component classes for consistency.
   - Impact/Severity: Medium – inconsistent spacing and colors across pages.
   - Suggested Fix: Replace repeated utility chains with `glass-card` and `btn-primary`/`btn-secondary` classes.
   - Repro/Verification Steps: Compare ticket page to dashboard card spacing.
   - Minimal Diff or Pseudocode:
     ```html
     <div class="glass-card">
     <button class="btn-primary">New Ticket</button>
     ```
   - Tests to Add/Update: Visual regression or Storybook snapshot.

2. **Dynamic color classes not safelisted**
   - File Path: `app/Services/TicketColorService.php`
   - Line Number(s): 12-41
   - Category: Style
   - Observed: Status/priority classes (`bg-red-100`, etc.) are generated in PHP but Tailwind `content` config only scans views/JS.
   - Expected: Dynamic utilities must be safelisted to survive purge.
   - Impact/Severity: High – status/priority badges may render unstyled in production.
   - Suggested Fix: Add safelist in `tailwind.config.js` for `bg-*`, `text-*`, and `dark:bg-*` combinations used by the service.
   - Repro/Verification Steps: Build assets and inspect missing classes in `app.css`.
   - Minimal Diff or Pseudocode:
     ```js
     safelist: [{ pattern: /(bg|text|dark:bg|dark:text)-(red|blue|green|yellow|orange|purple|pink|gray|teal|cyan|lime|emerald)-(100|200|300|700|800|900)/ }]
     ```
   - Tests to Add/Update: Build test verifying presence of safelisted classes.

3. **Legacy ticket view styling diverges from design system**
   - File Path: `resources/views/tickets/show.blade.php`
   - Line Number(s): 1-60
   - Category: Style
   - Observed: Uses `bg-white/50 dark:bg-neutral-900/40` palette unlike standardized `bg-white/5` cards.
   - Expected: All views should adhere to monochrome theme with shared spacing.
   - Impact/Severity: Low – inconsistent look if legacy view is ever rendered.
   - Suggested Fix: Remove legacy view or restyle using shared components.
   - Repro/Verification Steps: Manually render view and compare.
   - Minimal Diff or Pseudocode: Apply `glass-card` wrapper or delete file.
   - Tests to Add/Update: N/A

### Migration/Seeder
1. **Duplicate permission migrations**
   - File Path: `database/migrations/2025_01_01_000019_create_permission_tables.php` & `database/migrations/deprecated/2025_01_01_000019_create_permission_tables.php`
   - Line Number(s): n/a
   - Category: Migration/Seeder
   - Observed: Two versions of the same migration exist.
   - Expected: Single authoritative migration per table.
   - Impact/Severity: High – fresh installs may execute outdated version depending on path.
   - Suggested Fix: Remove deprecated copy and ensure proper order numbering.
   - Repro/Verification Steps: Run `php artisan migrate:fresh` and verify only one migration executes.
   - Minimal Diff or Pseudocode: delete deprecated file.
   - Tests to Add/Update: Migration test asserting permission tables schema.

2. **Timestamp collisions across migrations**
   - File Path: `database/migrations/2025_01_01_000012_create_*`
   - Line Number(s): n/a
   - Category: Migration/Seeder
   - Observed: Multiple migrations share identical timestamp `000012` leading to unpredictable execution order.
   - Expected: Each migration should have unique, sequential timestamps.
   - Impact/Severity: Medium – schema setup may vary by environment.
   - Suggested Fix: Renumber migrations or use `php artisan schema:dump` to consolidate.
   - Repro/Verification Steps: Inspect `migrations` table after fresh install to confirm ordering.
   - Minimal Diff or Pseudocode: rename files with unique timestamps.
   - Tests to Add/Update: Migration order smoke test.

3. **Seeder ordering depends on destructive RolePermissionSeeder**
   - File Path: `database/seeders/DatabaseSeeder.php`
   - Line Number(s): 18-36
   - Category: Migration/Seeder
   - Observed: `RolePermissionSeeder` (which deletes users) runs before `UserSeeder`; rerunning `DatabaseSeeder` after data creation wipes users again.
   - Expected: Idempotent seeders that do not reset unrelated data.
   - Impact/Severity: High – re-seeding in staging/production wipes accounts.
   - Suggested Fix: Refactor `RolePermissionSeeder` to sync roles/permissions without clearing `users`.
   - Repro/Verification Steps: Run `php artisan db:seed` twice and note user count reset.
   - Minimal Diff or Pseudocode: remove `User::delete()` from seeder and handle permissions separately.
   - Tests to Add/Update: Seeder idempotence test verifying user count remains stable.

4. **ClientSampleDataSeeder references possibly stale columns**
   - File Path: `database/seeders/ClientSampleDataSeeder.php`
   - Line Number(s): 1-120
   - Category: Migration/Seeder
   - Observed: Seeder assumes sample client relationships that may not match current schema after contract/hardware refactors.
   - Expected: Seeders should align with latest column names and foreign keys.
   - Impact/Severity: Medium – running seeder may fail or produce inconsistent relationships.
   - Suggested Fix: Review sample seeder against current models and adjust.
   - Repro/Verification Steps: Run `php artisan db:seed --class=ClientSampleDataSeeder` on fresh DB.
   - Minimal Diff or Pseudocode: update attributes to match migrations.
   - Tests to Add/Update: Seeder test confirming inserted sample data matches schema.

## Test Plan
- Feature tests for ticket sort allowlist and creation flow.
- Policy tests for attachment access and ticket status/priority transitions.
- Seeder/migration tests ensuring fresh database builds without duplicates or data loss.
- Livewire component tests for dashboard widgets and settings tabs.

## Cleanup Checklist
- [ ] Remove `resources/views/tickets` and deprecated organization controllers.
- [ ] Add sort column allowlist and tests in `ManageTickets`.
- [ ] Safelist dynamic Tailwind classes and adopt `glass-card`/`btn-*` utilities.
- [ ] Delete `database/migrations/deprecated` and resolve duplicate migrations/timestamps.
- [ ] Refactor `RolePermissionSeeder` to avoid destructive data wipes.
- [ ] Validate all seeders against current schema and run `php artisan migrate:fresh --seed` once dependencies install.
