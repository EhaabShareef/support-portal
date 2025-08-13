# Manage Settings Refactor Guide

## 1. Current Implementation Overview

### Route and Middleware
- **Route**: `/admin/settings` mapped to `App\Livewire\Admin\ManageSettings` and protected by the `role:admin` middleware inside the admin route group【F:routes/web.php†L115-L127】

### Livewire Component and View
- **Component**: `app/Livewire/Admin/ManageSettings.php`
  - Controls all tabs via an `activeTab` property and numerous form state arrays for each settings domain【F:app/Livewire/Admin/ManageSettings.php†L17-L85】
  - Loads ticket colors and performs an explicit admin role check in `mount()`【F:app/Livewire/Admin/ManageSettings.php†L87-L98】
  - Exposes computed collections for department groups, departments, application settings, schedule event types, and color palettes【F:app/Livewire/Admin/ManageSettings.php†L100-L157】
  - Contains CRUD and validation logic for department groups, departments, application settings, schedule event types, and ticket colors, plus delete/reset confirmations【F:app/Livewire/Admin/ManageSettings.php†L164-L571】
- **View**: `resources/views/livewire/admin/manage-settings.blade.php`
  - Renders five tabs: Application Settings, Department Groups, Departments, Schedule Events, and Ticket Colors【F:resources/views/livewire/admin/manage-settings.blade.php†L42-L70】
  - Holds every form, modal, and list for all tabs within a single Blade file.

### Supporting Classes
- Models: `App\Models\Department`, `App\Models\DepartmentGroup`, `App\Models\Setting`, `App\Models\ScheduleEventType`
- Enums: `App\Enums\TicketStatus`, `App\Enums\TicketPriority`
- Service: `App\Services\TicketColorService` for palette retrieval and persistence
- Policy: `App\Policies\ScheduleEventTypePolicy` exists but is not invoked by the component

### Shared State
- `activeTab` switches views across tabs
- Flash messages stored in the session are displayed at top of page
- `TicketColorService` provides palette and default colors used by Ticket Colors tab

## 2. Tab Responsibilities and Logic
| Tab | Responsibilities | Validation & Persistence |
|-----|-----------------|-------------------------|
| **Application Settings** | List, create, edit, delete settings grouped by `group`. | Validation for key, value, type, group, label, description and flags; unique key handling; uses `Setting` model for CRUD【F:app/Livewire/Admin/ManageSettings.php†L314-L369】 |
| **Department Groups** | Manage department group records and colors. | Validates name, description, color, active flag, sort order; prevents deletion when departments exist【F:app/Livewire/Admin/ManageSettings.php†L164-L236】 |
| **Departments** | Manage departments and link to groups. | Validates name, description, group relationship, email, active flag, sort order; guards against deleting groups with users or tickets【F:app/Livewire/Admin/ManageSettings.php†L238-L312】 |
| **Schedule Event Types** | CRUD for schedule event types with label, color, Tailwind classes. | Validates label uniqueness, description, color, classes, flags, sort order; disallows deletion when events exist【F:app/Livewire/Admin/ManageSettings.php†L407-L490】 |
| **Ticket Colors** | Configure badge colors for ticket status/priority. | Ensures all statuses and priorities have colors before saving; uses `TicketColorService` for persistence and reset to defaults【F:app/Livewire/Admin/ManageSettings.php†L500-L565】 |

## 3. Proposed Target Structure
```
app/
└── Livewire/Admin/Settings/
    ├── ManageSettings.php          # parent shell
    ├── Tabs/
    │   ├── ApplicationSettings.php
    │   ├── DepartmentGroups.php
    │   ├── Departments.php
    │   ├── ScheduleEventTypes.php
    │   └── TicketColors.php
    └── services/
        └── SettingsRepository.php  # shared persistence API
resources/views/livewire/admin/settings/
    ├── manage-settings.blade.php   # layout + tab navigation
    └── tabs/
        ├── application-settings.blade.php
        ├── department-groups.blade.php
        ├── departments.blade.php
        ├── schedule-event-types.blade.php
        └── ticket-colors.blade.php
```

- Parent `ManageSettings` shell renders tab navigation, handles layout, listens for `flash` events from children, and authorizes `settings.read`.
- Each tab component encapsulates its own state, `rules()` validation, authorization (`can('...')`/`hasRole()`), and CRUD operations.
- `SettingsRepository` centralizes read/write operations for `Setting` records and any cross-tab settings (e.g., ticket color storage), so children call a consistent API.

## 4. Refactor Sequence
1. **Create parent shell** with route pointing to new namespace; include tab switching, flash listener, and stub slots for children.
2. **Extract Application Settings tab** into `Tabs\ApplicationSettings` component and blade.
3. **Extract Department Groups tab** into its own component and view.
4. **Extract Departments tab** with its component and view.
5. **Extract Schedule Event Types tab**; hook into existing `ScheduleEventTypePolicy` for authorization.
6. **Extract Ticket Colors tab**; wire up to `SettingsRepository`/`TicketColorService`.
7. After each extraction, remove the old section from the monolithic component and verify UI parity before proceeding to next tab.
8. When all tabs are migrated, delete the legacy component and blade.

Verification after each step:
- Component renders within shell and performs CRUD.
- Permissions block unauthorized actions.
- Flash messages surface through parent.

## 5. Event Contract
- Children dispatch `flash` events: `dispatch('flash', { message: '...', type: 'success|error' })`.
- Parent `ManageSettings` listens and sets session flash or local state to display messages.
- Optional `tabChanged` event emitted by parent if navigation needs to influence children.

## 6. Validation & Authorization Notes
- Use `authorize()` in each Livewire action (`create`, `update`, `delete`) referencing Spatie permissions:
  - `settings.read` to view the page.
  - `settings.update` for saving application settings.
  - `department-groups.*`, `departments.*`, `schedule-event-types.*`, `ticket-colors.update` for respective tabs.
- Hide tabs in the shell if the user lacks read permission, but always enforce server-side `authorize` calls even when tabs are hidden.
- Each component defines its own `rules()` or dedicated FormRequest for clarity.

## 7. Testing Plan
- **Unit**: cover `SettingsRepository` methods.
- **Feature/Livewire**:
  - Verify each tab component renders with correct permission gates.
  - CRUD tests for department groups, departments, settings, event types, and ticket colors.
  - Event tests ensuring `flash` is received by parent.
- **UI**: browser tests to ensure tab navigation and modals work across roles.

## 8. Mapping Old → New
| Old File & Range | New Component |
|------------------|---------------|
| `ManageSettings.php` lines 17‑85 | `Settings\ManageSettings` (parent shell) |
| lines 164‑236 | `Tabs\DepartmentGroups` |
| lines 238‑312 | `Tabs\Departments` |
| lines 314‑405 | `Tabs\ApplicationSettings` |
| lines 407‑490 | `Tabs\ScheduleEventTypes` |
| lines 500‑565 | `Tabs\TicketColors` |
| Blade `manage-settings.blade.php` all lines | Split into `settings/manage-settings.blade.php` + tab views |

## 9. Deprecations
- Remove `app/Livewire/Admin/ManageSettings.php` and `resources/views/livewire/admin/manage-settings.blade.php` after migration.
- No legacy partials or JS helpers were found; once the new structure is in place, the old monolithic component is the sole deprecated artifact.

