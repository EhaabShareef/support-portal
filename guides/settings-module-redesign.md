# Settings Module Redesign & Enum Configuration Guide

## 1. Current Settings Implementation Inventory

### Routes & Middleware
- **`routes/web.php`** – `/admin/settings` is served by a Livewire component and is protected by the `role:admin` middleware in the admin group【F:routes/web.php†L115-L131】

### Livewire Components
| File | Responsibility |
| --- | --- |
| `app/Livewire/Admin/Settings/ManageSettings.php` | Parent shell managing the active tab and flash messages; performs an explicit admin role check in `mount()`【F:app/Livewire/Admin/Settings/ManageSettings.php†L10-L27】 |
| `app/Livewire/Admin/Settings/Tabs/ApplicationSettings.php` | CRUD for generic `Setting` records, including validation and modal state【F:app/Livewire/Admin/Settings/Tabs/ApplicationSettings.php†L12-L123】 |
| `app/Livewire/Admin/Settings/Tabs/DepartmentGroups.php` | CRUD for department groups; view contains TODO modals and repeats button styles【F:resources/views/livewire/admin/settings/tabs/department-groups.blade.php†L1-L56】 |
| `app/Livewire/Admin/Settings/Tabs/Departments.php` | CRUD for departments; view mirrors department‑group markup and also lacks extracted modals【F:resources/views/livewire/admin/settings/tabs/departments.blade.php†L1-L60】 |
| `app/Livewire/Admin/Settings/Tabs/ScheduleEventTypes.php` | Manage schedule event types (label, color, classes). |
| `app/Livewire/Admin/Settings/Tabs/TicketColors.php` | Configure status and priority color mappings via `TicketColorService`. |
| `app/Livewire/Admin/Settings/Tabs/Hotlines.php` | Manage support hotlines stored as a JSON setting【F:app/Livewire/Admin/Settings/Tabs/Hotlines.php†L10-L140】 |

### Blade Views & Partials
| File | Notes |
| --- | --- |
| `resources/views/livewire/admin/settings/manage-settings.blade.php` | Renders horizontal tab navigation with duplicated button markup and inline success/error flash messages【F:resources/views/livewire/admin/settings/manage-settings.blade.php†L42-L76】 |
| `resources/views/livewire/admin/settings/tabs/application-settings.blade.php` | Oversized (200+ lines) single file containing list and modal markup; inconsistent spacing compared with other tabs【F:resources/views/livewire/admin/settings/tabs/application-settings.blade.php†L1-L80】 |
| `resources/views/livewire/admin/settings/tabs/department-groups.blade.php` & `.../departments.blade.php` | Similar grid/card structures; both end with TODO comments for modal extraction【F:resources/views/livewire/admin/settings/tabs/department-groups.blade.php†L49-L56】 |

### Services & Storage
- **`app/Models/Setting.php`** – key/value store with typed casting and cache invalidation【F:app/Models/Setting.php†L11-L59】
- **`app/Services/SettingsRepository.php`** – cached API for retrieving and mutating settings【F:app/Services/SettingsRepository.php†L13-L52】
- **`app/Services/HotlineService.php`** – reads/writes `support_hotlines` setting【F:app/Services/HotlineService.php†L12-L80】
- **`app/Services/TicketColorService.php`** – persists status/priority color maps, seeds defaults【F:app/Services/TicketColorService.php†L31-L52】

### Policies & Authorization
- `TicketPolicy` uses `Setting::get('tickets.reopen_window_days', 3)` to enforce reopen limits【F:app/Policies/TicketPolicy.php†L95-L102】
- Various tab components repeat `checkPermission('settings.update')` for CRUD actions【F:app/Livewire/Admin/Settings/Tabs/ApplicationSettings.php†L39-L45】

### Observations
- Many views replicate button classes and modal markup, leading to duplication.
- Tab components each implement their own permission checks and form-reset helpers.
- Some views (Department Groups, Departments) include TODO placeholders and lack extracted partials.
- Overall styling mixes ad‑hoc borders and colors, producing inconsistent visuals across tabs.

## 2. Redesign Overview
- Replace horizontal tabs with **left-side vertical tabs**. Tabs occupy a fixed-height column; the active tab has a subtle accent border. Content loads in a right-hand panel.
- Responsive: on small screens, collapse into top accordion or horizontally scrollable tabs while keeping the content panel stacked below.
- Accessibility: keyboard arrow navigation between tabs; active tab label announced via `aria-selected` and `aria-controls`.
- Styling: monochrome palette with existing accent color only; focus rings and `prefers-reduced-motion` fallbacks applied. 

### Top-Level Tabs
1. **General** – app-wide settings (hotlines, theme, misc).
2. **Ticket** – ticket workflow, colors, limits.
3. **Organization** – department groups and departments.
4. **Contracts** – contract types & statuses.
5. **Hardware** – hardware types & statuses.
6. **Schedule** – weekend days, event types.
7. **Users** – defaults for user management.

### Mapping Existing Keys to New Groups
| Setting Key | Current Purpose | New Group / Component |
| --- | --- | --- |
| `weekend_days` | Highlighted days in `ScheduleCalendar`【F:app/Livewire/ScheduleCalendar.php†L187-L209】 | Schedule → `SettingsSchedule` |
| `default_organization` | Default org for new users in `ManageUsers`【F:app/Livewire/Admin/ManageUsers.php†L220-L230】 | Users → `SettingsUsers` |
| `support_hotlines` | Hotline numbers managed via `HotlineService`【F:app/Services/HotlineService.php†L12-L80】 | General → `SettingsGeneral` |
| `ticket_status_colors` / `ticket_priority_colors` | Badge colors loaded by `TicketColorService`【F:app/Services/TicketColorService.php†L31-L52】 | Ticket → `SettingsTicket` |
| `tickets.reopen_window_days` | Reopen grace period enforced in policy & components【F:app/Policies/TicketPolicy.php†L95-L102】 | Ticket → `SettingsTicket` |

### New Directory Structure
```
app/Livewire/Admin/Settings/
├── Shell.php                       # parent shell
└── Tabs/
    ├── SettingsGeneral.php
    ├── SettingsTicket.php
    ├── SettingsOrganization.php    # wraps DepartmentGroups & Departments
    ├── SettingsContracts.php
    ├── SettingsHardware.php
    ├── SettingsSchedule.php
    └── SettingsUsers.php
resources/views/livewire/admin/settings/
├── shell.blade.php                 # vertical tabs layout
└── tabs/
    ├── general.blade.php
    ├── ticket.blade.php
    ├── organization.blade.php
    ├── contracts.blade.php
    ├── hardware.blade.php
    ├── schedule.blade.php
    └── users.blade.php
```
- Route remains `/admin/settings` but references `Settings\Shell` instead of `ManageSettings`.
- Oversized tab views (e.g., Application Settings modal) are extracted into partials such as `tabs/partials/application-setting-form.blade.php`.
- Legacy `ManageSettings` files become deprecated and removed after migration.

## 3. Settings Repository Interface
Introduce `App\Contracts\SettingsRepositoryInterface` and update existing `SettingsRepository` to implement it.
```php
interface SettingsRepositoryInterface
{
    /** @return \Illuminate\Support\Collection */
    public function all(): Collection;
    public function get(string $key, $default = null);
    public function set(string $key, $value, string $type = 'string'): void;
    public function reset(string $key): void;            // restore seeded default
    public function forGroup(string $group): Collection; // grouped retrieval
}
```
- Replace direct `Setting::get()` calls with `app(SettingsRepositoryInterface::class)->get()` in policies/components (e.g., `TicketPolicy`, `ManageUsers`).
- Child tab components inject the repository for all read/write operations, eliminating custom queries.

## 4. Tab Details
Each tab component:
- Authorizes viewing with `settings.read` and updating with `settings.update`.
- Emits `saved`, `error`, and `reset` events; `Shell` listens to show global toasts.
- Provides sticky **Save** and **Reset to defaults** actions at panel bottom; unsaved changes warn via `beforeunload` and Livewire events.

### 4.1 General Tab
| Field | Validation | Default | Help | Events |
| --- | --- | --- | --- | --- |
| Support Hotlines | array structure validated by `HotlineService` | seeded defaults | “Numbers displayed to clients on ticket forms.” | `saveHotlines`, `resetHotlines` |
| Theme / global misc (future) | – | – | – | – |

### 4.2 Ticket Tab
| Field | Validation | Default | Help | Consumed In |
| --- | --- | --- | --- | --- |
| Default status on reply (`tickets.default_reply_status`) | `required|exists:ticket_statuses,slug` | `in_progress` | Status assigned after a reply if user doesn’t choose one. | `ViewTicket` `replyStatus` preset |
| Reopen window days (`tickets.reopen_window_days`) | `required|integer|min:1` | `3` | Clients may reopen closed tickets within this many days. | `TicketPolicy`, `ManageTickets`, `ViewTicket`【F:app/Policies/TicketPolicy.php†L95-L102】【F:app/Livewire/ManageTickets.php†L603-L623】【F:app/Livewire/ViewTicket.php†L300-L309】 |
| Priority escalation policy (`tickets.require_escalation_confirmation`) | `boolean` | `true` | Support/Admin must confirm when raising priority. | `TicketPolicy::escalatePriority` & `ViewTicket` validation |
| Message ordering (`tickets.message_order`) | `required|in:newest_first,oldest_first` | `newest_first` | Order of messages in ticket view. | `ViewTicket` message query |
| Attachment size (`tickets.attachment_max_size_mb`) | `required|integer|min:1` | `10` | Max MB per attachment. | `ViewTicket` attachment validation |
| Attachment count (`tickets.attachment_max_count`) | `required|integer|min:1` | `5` | Max files per reply. | `ViewTicket` attachment handling |
| Status/Priority colors | via `TicketColorService` repository methods | seeded | Accent color for badges. | `TicketStatus::cssClass`, `TicketPriority::cssClass` |

**Ticket Tab Verification Checklist**
- Replying to a ticket without selecting a status uses the configured default.
- Closed tickets older than N days reject reopen attempts.
- Escalating priority prompts confirmation and respects client restrictions.
- Message list respects ordering setting.
- Uploading >N files or files exceeding the size limit fails validation with inline errors.

### 4.3 Organization Tab
- Houses **Department Groups** and **Departments** components, now split:
  - `SettingsOrganization` shell loads sub-components.
  - Modals extracted into `tabs/organization/partials`.

### 4.4 Contracts Tab
- CRUD for contract types and statuses (lookup tables defined in §5).

### 4.5 Hardware Tab
- CRUD for hardware types and statuses.

### 4.6 Schedule Tab
- Fields: `weekend_days` multi-select, schedule event types.

### 4.7 Users Tab
- Field: `default_organization` select.

## 5. Configurable Enum Strategy
### 5.1 Audit
| Domain | Current Storage |
| --- | --- |
| Ticket status/priority | ENUM columns `status` & `priority` in `tickets` table【F:database/migrations/2025_01_01_000005_create_tickets_table.php†L20-L38】 and PHP enums `TicketStatus`, `TicketPriority` |
| Hardware status | ENUM column in `organization_hardware`【F:database/migrations/2025_01_01_000010_create_organization_hardware_table.php†L38-L40】 and enum `HardwareStatus` |
| Hardware type | String column `hardware_type` constrained only in code【F:database/migrations/2025_01_01_000010_create_organization_hardware_table.php†L27】 with enum `HardwareType` |
| Contract type/status | ENUM columns in `organization_contracts`【F:database/migrations/2025_01_01_000009_create_organization_contracts_table.php†L26-L31】 |

### 5.2 Phase 1 – Lookup Tables & CRUD
1. Create tables: `ticket_statuses`, `ticket_priorities`, `hardware_types`, `hardware_statuses`, `contract_statuses`, `contract_types` (id, name, slug, sort_order, is_protected, timestamps, soft deletes).
2. Seed tables with current enum values (mark critical ones as `is_protected`).
3. Add models & relationships:
   - `Ticket` → `belongsTo` `TicketStatus` & `TicketPriority`.
   - `OrganizationHardware` → `belongsTo` `HardwareType` & `HardwareStatus`.
   - `OrganizationContract` → `belongsTo` `ContractType` & `ContractStatus`.
4. Policies: reuse `settings.read`/`settings.update` for CRUD components under respective tabs.
5. Admin CRUD Livewire components under relevant tabs to manage lists. Soft-deleting non‑protected values; prevent edits to `is_protected` records.
6. Cache lookups in repository/service (`Cache::remember`) for dropdowns.
7. Dropdowns in Blade now query lookup models instead of hardcoded arrays or enums:
   - Ticket forms (`resources/views/livewire/manage-tickets.blade.php`, `view-ticket.blade.php`).
   - Hardware forms (`resources/views/livewire/organization-hardware-form.blade.php`, `manage-hardware.blade.php`).
   - Contract forms (`resources/views/livewire/organization-contract-form.blade.php`, `manage-contracts.blade.php`).

### 5.3 Phase 2 – Safe Column Migration
1. For each domain, add nullable FK columns (`ticket_status_id`, `ticket_priority_id`, etc.), backfill from existing string/enum values, then switch application code to use FKs.
2. Where risky, continue storing legacy string columns and populate lookups; schedule FK enforcement for later release.
3. Update validation rules from `in:` lists to `exists:<lookup_table>,id` or `slug`.
4. Provide rollback by keeping original columns until confidence is high, then drop enums and rename FK columns.

## 6. Visual Language Standardization
- Use shared card component with uniform padding, radius, and subtle shadow.
- Monochrome backgrounds (`bg-neutral-50` / dark variants) with a single accent color.
- Vertical tab list uses consistent `gap-2`, `rounded-l-lg` active border, and `focus:ring` utilities; respects dark mode.
- Add skeleton loaders for initial fetch, inline validation errors below fields, and toast confirmations via Livewire events.
- `prefers-reduced-motion` media queries disable transitions; focus rings use `outline-none focus-visible:ring-2`.

## 7. Test Plan
- **Feature/Livewire tests** for each tab verifying authorization, validation, and repository integration.
- **Repository unit tests** covering all interface methods and cache clearing.
- **Permission tests** confirming `settings.read` and `settings.update` gates.
- **Enum CRUD tests** ensuring protected values cannot be deleted and lookups are cached.

## 8. Deprecated / Duplicate Files
| File | Action |
| --- | --- |
| `app/Livewire/Admin/Settings/ManageSettings.php` | Replace with `Shell` after migration |
| `resources/views/livewire/admin/settings/manage-settings.blade.php` | Replace with `shell.blade.php` |
| `app/Livewire/Admin/Settings/Tabs/*` | Superseded by new per‑group components |
| `resources/views/livewire/admin/settings/tabs/*.blade.php` | Replaced by new tab views & extracted partials |

## 9. Migration Checklist
1. Run migrations to create lookup tables and new settings columns.
2. Seed defaults (`ApplicationSettingsSeeder` updated for new keys and lookup seeds).
3. Backfill enums into lookup tables; optionally schedule FK migrations.
4. Deploy code with repository interface and new components.
5. Clear caches: `php artisan config:clear && php artisan cache:clear`.
6. Verify `/admin/settings` renders vertical tabs; run checklist from §4.2.
7. Remove deprecated files after successful rollout.

---
This guide outlines the current state, the planned vertical‑tab redesign, and a progressive approach for making existing enums configurable while preserving behavior.
