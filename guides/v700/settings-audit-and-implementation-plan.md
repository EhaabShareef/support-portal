# Settings Audit and Implementation Plan

## Part A — Repository & Database Inventory

### Livewire Settings Components
- `app/Livewire/Admin/Settings/Shell.php` – parent shell with tab navigation and admin role gate.
- `app/Livewire/Admin/Settings/Tabs/SettingsGeneral.php` – General tab handling support hotlines and theme stubs.
- `app/Livewire/Admin/Settings/Tabs/SettingsTicket.php` – Ticket tab with workflow prefs, color editor, and status management.
- `app/Livewire/Admin/Settings/Tabs/SettingsOrganization.php` – currently manages department groups and departments.
- `app/Livewire/Admin/Settings/Tabs/SettingsContracts.php` – contract type/status lookup maintenance (in‑memory editing).
- `app/Livewire/Admin/Settings/Tabs/SettingsHardware.php` – hardware type/status lookup maintenance (status CRUD not persisted).
- `app/Livewire/Admin/Settings/Tabs/SettingsSchedule.php` – weekend day setting and schedule event types.
- `app/Livewire/Admin/Settings/Tabs/SettingsUsers.php` – user registration defaults and security rules.

### Blade Views
- `resources/views/livewire/admin/settings/shell.blade.php` – vertical tab layout and keyboard navigation.
- `resources/views/livewire/admin/settings/tabs/*.blade.php` – per‑tab content for General, Ticket, Organization, Contracts, Hardware, Schedule, Users plus legacy `ticket-colors.blade.php`.

### Routes & Middleware
- Settings route: `routes/web.php` → `/admin/settings` uses `Shell` and inherits admin middleware.
- Route group guarded by `role:admin` inside `routes/web.php` ensuring only admins reach settings.

### Authorization & Policies
- Components call `$this->checkPermission('settings.read')` in `mount()` to guard views and `settings.update` before mutations.
- Permissions `settings.read`/`settings.update` defined in `config/modules.php` and seeded via `RolePermissionSeeder`.

### Settings Storage Layer
- `app/Services/SettingsRepository.php` implements `SettingsRepositoryInterface` using the `Setting` model and cache.
- `Setting` model (`app/Models/Setting.php`) handles type casting, encryption, and cache invalidation.

### Lookup Tables & Seeders
| Domain | Table | Seeder |
|---|---|---|
| Ticket Statuses | `ticket_statuses` | `TicketStatusSeeder.php` (also seeds dept group links) |
| Contract Types | `contract_types` | `ContractTypeSeeder.php` |
| Contract Statuses | `contract_statuses` | `ContractStatusSeeder.php` |
| Hardware Types | `hardware_types` | `HardwareTypeSeeder.php` |
| Hardware Statuses | `hardware_statuses` | `HardwareStatusSeeder.php` |
| Schedule Event Types | `schedule_event_types` | `ScheduleEventTypeSeeder.php` |
| Department Groups | `department_groups` | `BasicDataSeeder.php` |
| Departments | `departments` | `BasicDataSeeder.php` |

---

## Part B — Per‑Tab Functional Audit

### General
**Current State**
- Hotlines list with create/edit/delete and active toggle persists through `HotlineService` and `support_hotlines` setting.
- Theme settings fields loaded but not saved.

**Intended Behavior**
- Manage support hotlines; future theme configuration persisted via `SettingsRepository`.

**Gaps & Fixes**
- Theme inputs are read‑only; add save/reset hooks when feature is implemented.
- Add validation and success feedback for theme section.

**Permissions**
- `settings.read` in `mount()` and `settings.update` before hotline mutations.

**Tests to Add**
- Livewire test verifying hotline CRUD and reset.

### Ticket
**Current State**
- Workflow, attachment limits, status & priority colors persisted.
- Ticket statuses include department group assignment UI and backend calls.

**Intended Behavior**
- Configure workflow options, colors, and manage ticket status list with color selection only.

**Gaps & Fixes**
```
File Path: app/Livewire/Admin/Settings/Tabs/SettingsTicket.php
Line(s): 93‑100, 354‑382
Issue: Status loader and updater reference department groups.
Expected: Status CRUD without department linkage.
Suggested fix: Remove `departmentGroups`/`statusDepartmentGroups` properties and related methods; drop calls to `departmentGroups()` and `updateDepartmentGroupAssignment()`.
```
```
File Path: resources/views/livewire/admin/settings/tabs/ticket.blade.php
Line(s): 256‑273
Issue: Department group assignment grid.
Expected: Omit group UI; show only status fields and actions.
Suggested fix: Delete assignment block and adjust spacing.
```

**Permissions**
- `settings.read` checked on mount; `settings.update` before save/reset/status CRUD.

**Tests to Add**
- Livewire tests for saving workflow settings and CRUD on ticket statuses.
- Repository test to ensure colors persist in `ticket_statuses.color`.

### Organization
**Current State**
- Tab manages department groups and departments.

**Intended Behavior**
- Should manage organization subscription statuses once department logic relocates.

**Gaps & Fixes**
- Repurpose component: remove department management; introduce CRUD for subscription statuses.
- Move Department Group and Department CRUD to Users tab (see Part C & E).

**Permissions**
- `settings.read` only; department actions use `department-groups.*` and `departments.*` permissions.

**Tests to Add**
- Future tests around subscription status LOV once implemented.

### Contracts
**Current State**
- Types/statuses loaded from database but edits happen in memory; TODO marker to persist.

**Intended Behavior**
- Full CRUD persisting to `contract_types` and `contract_statuses` tables.

**Gaps & Fixes**
File Path: app/Livewire/Admin/Settings/Tabs/SettingsContracts.php
Line(s): 113-129
Issue: Save operations update local array only; database not written.
Expected: Use `ContractType`/`ContractStatus` models (with `key`/`slug`, unique index, optional `color`, `sort_order`) for create/update/delete inside transactions; validate uniqueness and non-empty labels; emit events and refresh lists.
Suggested fix: Replace in-memory mutations with model calls and refresh lists.

**Permissions**
- `settings.read` and `settings.update` used for all actions.

**Tests to Add**
- Livewire tests for type/status CRUD hitting database.

### Hardware
**Current State**
- Types persist to DB; statuses loaded from table or fallback array. Status save/delete only mutate array (`TODO` to persist).

**Gaps & Fixes**
```
File Path: app/Livewire/Admin/Settings/Tabs/SettingsHardware.php
Line(s): 60‑66, 215‑249, 241
Issue: Hardware statuses default to hardcoded array and CRUD does not hit database.
Expected: Always load from `hardware_statuses` table; use models for CRUD.
Suggested fix: Remove fallback array, implement `HardwareStatus` model operations, and seed defaults.
```

**Permissions**
- `settings.read` on mount; `settings.update` before mutations.

**Tests to Add**
- Livewire CRUD tests for hardware types and statuses.

### Schedule
**Current State**
- Weekend days saved via settings; event type CRUD persisted. Delete lacks usage check.

**Gaps & Fixes**
```
File Path: app/Livewire/Admin/Settings/Tabs/SettingsSchedule.php
Line(s): 188‑199
Issue: Deletion TODO does not verify if event type in use.
Expected: Block delete when schedules reference the type.
Suggested fix: Implement commented `withCount('schedules')` check before deletion.
```
- Add color preview and Tailwind class handling for custom colors.

**Permissions**
- `settings.read` and granular `schedule-event-types.*` permissions enforced.

**Tests to Add**
- Tests for weekend day persistence and event type CRUD with usage guard.

### Users
**Current State**
- Manages registration defaults, password policy, and email verification.

**Intended Behavior**
- Also host Department Group and Department management (migrated from Organization tab).

**Gaps & Fixes**
- Extend component to include department/department group CRUD sections with corresponding permissions.
- Ensure unsaved-change detection covers new sections.

**Permissions**
- `settings.read` and `settings.update` plus new department permission checks after migration.

**Tests to Add**
- Livewire tests verifying default organization/role saving and department CRUD once moved.

---

## Part C — Ticket Status Management Overhaul
1. **Scope**: Restrict Ticket tab to status CRUD plus color assignment. Remove department group linkage (see fixes above).
2. **Data Model**: `ticket_statuses` already exists with `color` column. Ensure migration is run and seed defaults via `TicketStatusSeeder`.
3. **Department Logic**: Relocate department group association screens to Users tab; remove many‑to‑many management from Ticket tab.
4. **Consumers**: Audit ticket forms, lists, and badges to read from `ticket_statuses` table and apply hex color styling. Update any enum references to use `TicketStatus` model or repository.

---

## Part D — Organization Settings Adjustments
1. **Subscription Status LOV**
   - Create `organization_subscription_statuses` table with `key`, `label`, `color`, `sort_order`, `is_active`.
   - Seed defaults (Trial, Active, Suspended, Cancelled) with colors.
   - Add Settings tab CRUD (or include in Organization tab after repurpose) to manage statuses.
2. **Organization View Cleanup**
   - Replace dual badges with single Active/Inactive badge in `resources/views/livewire/view-organization.blade.php`.
   - Make `company` and `tin_no` optional in all form views and remove any `required` attributes.
   - In create/edit (`resources/views/livewire/manage-organizations.blade.php`), place Active toggle next to `subscription_status` select for cohesive row.
   - Update validation rules if necessary and adjust `ValidatesOrganizations` trait for subscription status to pull from new LOV table.

---

## Part E — Additional LOV Candidates
| Feature | Current Source | Proposed Action |
|---|---|---|
| Contract Types & Statuses | Lookup tables exist; editing not persisted | Implement full CRUD persistence and expose in Settings tab |
| Hardware Types & Statuses | Tables exist; status CRUD in-memory | Persist status CRUD; manage both lists under Hardware tab |
| Schedule Event Types | Table with color & tailwind classes | Continue CRUD; ensure color management and safelist classes |
| Department Groups & Departments | Managed under Organization tab | Move to Users tab for central user/org administration |
| Ticket Priority Colors | Currently part of Ticket tab color editor | Leave in Ticket tab but ensure colors come from `ticket_statuses`/settings |
| Article/FAQ Categories | Not found | If introduced, create `article_categories` table and manage under Settings > Users or separate tab |

---

## Part F — Uniform UI/UX Contract for Settings
1. **Layout**: Vertical tab list on left, content pane on right (`shell.blade.php`); collapse to horizontal scroll on mobile.
2. **Controls**: Use Tailwind `block text-sm font-medium` labels, help text via `text-xs text-neutral-500` beneath inputs, and `@error` blocks.
3. **Buttons**: Sticky footer with **Save** (primary color) and **Reset to Defaults** (neutral). Warn on tab change if `hasUnsavedChanges`.
4. **Feedback**: Reuse flash message blocks; success toast via `dispatch('saved', ...)`; inline error summaries at top of forms.
5. **Accessibility**: `aria-selected` on tab buttons, keyboard arrows implemented in shell script, respect `prefers-reduced-motion` for transitions.
6. **Theme & Spacing**: Monochrome palette (`text-neutral-*, bg-neutral-*`) with single accent (`bg-sky-600` etc.), consistent padding (`p-6` cards), and icon size (`h-4 w-4`).
7. **Tailwind Utility Checklist**: `px-4 py-2 rounded-md text-sm focus:ring-2 focus:ring-sky-500`, `bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700`, `inline-flex items-center gap-2` for buttons.

---

## Part G — Wiring Plan
For each tab:
```
Blade inputs → Livewire state → validation rules → SettingsRepository / Model → DB write
```
- **General**: Hotline form → `hotlineForm` → inline validation → `HotlineService` & `SettingsRepository::set` → `settings` table.
- **Ticket**: Inputs/hex picker → component properties → `$this->validate()` → `SettingsRepository::set` & `TicketStatus` model → `settings` / `ticket_statuses` tables.
- **Organization**: Subscription status form → component state → validation trait → new repository/model → `organization_subscription_statuses` table.
- **Contracts**: Type/status modals → `typeForm`/`statusForm` → `$this->validate()` → `ContractType`/`ContractStatus` models → respective tables.
- **Hardware**: Type/status modals → forms → validation → `HardwareType`/`HardwareStatus` models → lookup tables.
- **Schedule**: Weekend checkboxes and event type modals → state arrays → validation → `SettingsRepository` or `ScheduleEventType` model → `settings` / `schedule_event_types`.
- **Users**: Dropdowns & toggles → properties → validation → `SettingsRepository::set` → `settings` table; department CRUD uses `Department`/`DepartmentGroup` models.

---

## Part H — Database & Seeder Reconciliation
1. Ensure all lookup tables above are seeded before dependent data.
2. Validate seed data: ticket statuses include color, contract statuses include color, hardware statuses exist with correct slugs.
3. Seeder run order for fresh installs:
   1. `RolePermissionSeeder`
   2. LOV seeders (`TicketStatusSeeder`, `ContractTypeSeeder`, `ContractStatusSeeder`, `HardwareTypeSeeder`, `HardwareStatusSeeder`, `ScheduleEventTypeSeeder`)
   3. `BasicDataSeeder` (organizations, departments, default settings)
   4. `ApplicationSettingsSeeder` and others as needed

---

## Part I — Consistency & Cleanup
1. Normalize headings, button placement, and spacing across tab blades.
2. Remove deprecated components: `TicketColors` and `ApplicationSettings` tabs plus their Blade files if not used.
3. Safelist dynamic Tailwind classes for LOV colors in `tailwind.config.js` (e.g., `bg-*-500`, `text-*-800`, `dark:bg-*-900/40`).
4. Deduplicate permission checks by centralizing `checkPermission` helper in trait if repeated.

---

## Part J — Post‑Implementation Checklists

### Functional
- [ ] Each tab loads defaults and saves changes to database.
- [ ] `settings.read` gates viewing and `settings.update` gates mutations.
- [ ] All dropdowns sourced from lookup tables; colors render from stored values.

### UX
- [ ] Consistent card spacing, labels, and help text.
- [ ] Sticky action row with Save/Reset on every tab.
- [ ] Keyboard‑navigable tabs with focus states and reduced‑motion support.

### Tests
- [ ] Livewire feature tests for save/reset on every tab.
- [ ] Unit tests for `SettingsRepository` and lookup repositories.
- [ ] Policy tests for `settings.read` and `settings.update` permissions.
- [ ] Seeder test verifying LOV tables populated with expected keys and colors.

