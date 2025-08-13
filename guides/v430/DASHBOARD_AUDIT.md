# Dynamic Dashboard Audit

This audit reviews the new widget‑based dashboard implementation for correctness, UX coherence, performance, and security.
All paths are relative to the repository root.

## Architecture & File Mapping

| Concern | Location |
|---|---|
| Dashboard route | `routes/web.php` line 42 |
| Livewire entry component | `app/Livewire/Dashboard.php` |
| Dashboard layout view | `resources/views/livewire/dashboard.blade.php` |
| Widget catalog model | `app/Models/DashboardWidget.php` |
| Per‑user widget settings model | `app/Models/UserWidgetSetting.php` |
| Customize modal component & view | `app/Livewire/CustomizeDashboard.php`, `resources/views/livewire/customize-dashboard.blade.php` |
| Admin metrics widget components | `app/Livewire/Dashboard/Widgets/Admin/Metrics/{Small,Medium,Large}.php` |
| Admin metrics widget views | `resources/views/livewire/dashboard/widgets/admin/metrics/{small,medium,large}.blade.php` |
| Widget & preference migrations | `database/migrations/2025_01_01_000024_create_dashboard_widgets_table.php`, `2025_01_01_000025_create_user_widget_settings_table.php` |
| Widget seed data | `database/seeders/DashboardWidgetSeeder.php` |
| Role/permission seed data | `database/seeders/RolePermissionSeeder.php` |

## Findings

### 1. Permission and Role Enforcement

- **Dashboard entry gate** – The `mount` method requires both `dashboard.access` and role‑specific permissions (e.g., `dashboard.admin`). Missing permissions abort with 403, which is correct【F:app/Livewire/Dashboard.php†L24-L37】.
- **Widget permission filtering bug** – `CustomizeDashboard::loadWidgets` filters on a non‑existent `$widget->permission` property, so all widgets are exposed in the modal regardless of permissions. Only after mapping are they flagged as `can_view` false. Unauthorized widgets are therefore displayed (though disabled) in the customization UI and may be saved inadvertently if `can_view` is later set true by mistake【F:app/Livewire/CustomizeDashboard.php†L34-L40】.
- **Widget rendering bypass** – `Dashboard::userWidgets` fetches widgets by role but does not validate the widget’s `permissions` array. If a widget’s role matches but the user lacks the required permission, it will still render. Expected behavior is to exclude widgets failing `isVisibleForUser()` or at least filter by permissions before rendering【F:app/Livewire/Dashboard.php†L50-L75】【F:app/Models/DashboardWidget.php†L68-L88】.

**Recommendation:**
```php
// app/Livewire/Dashboard.php
$availableWidgets = DashboardWidget::where('is_active', true)
    ->where('category', $userRole)
    ->get()
    ->filter(fn($widget) => $widget->isVisibleForUser($user))
    ->sortBy('sort_order');
```
Add unit tests for unauthorized users attempting to access widgets to ensure they are filtered.

### 2. User Preference Persistence

- Preferences are stored in `user_widget_settings` with unique `(user_id, widget_id)` and fields for visibility, size, order, and options【F:database/migrations/2025_01_01_000025_create_user_widget_settings_table.php†L14-L25】.
- `CustomizeDashboard::saveChanges` correctly upserts settings and skips widgets the user cannot view【F:app/Livewire/CustomizeDashboard.php†L168-L187】.
- `UserWidgetSetting::getEffectiveSize` guards against invalid sizes, but the customization UI offers all global sizes regardless of each widget’s `available_sizes`, allowing users to pick unsupported sizes that later fall back silently【F:app/Livewire/CustomizeDashboard.php†L69-L76】【F:app/Models/UserWidgetSetting.php†L63-L75】.

**Recommendation:** filter the size `<select>` options per widget and validate server‑side before saving.

### 3. Widget Visibility and Security

- `DashboardWidget::isVisibleForUser` checks activity, role category, permissions array, and user settings【F:app/Models/DashboardWidget.php†L55-L88】.
- Because `userWidgets()` does not use `isVisibleForUser`, a widget with mismatched permissions could still render and execute queries. Ensure `isVisibleForUser` is invoked before rendering.
- Unauthorized widgets still have data loaders defined in their components. If rendered, they will query data before permission checks. Enforce permission checks in `mount` of each widget component or by guarding `render`.

### 4. Layout & Responsiveness

- Widget size classes are centrally mapped in `Dashboard::getWidgetClasses`, providing responsive spans for five sizes【F:app/Livewire/Dashboard.php†L351-L365】.
- Layout integrity relies on user‑selected sizes being valid. Since the customize UI does not restrict to `available_sizes`, an unsupported size could cause unexpected placement until `getEffectiveSize` corrects it on render.
- No tests exist to confirm layout across breakpoints.

**Recommendation:**
- Limit size options per widget and write front‑end tests (e.g., Playwright) verifying grid consistency at mobile, tablet, and desktop widths.

### 5. UX & Accessibility

- The dashboard and widgets use Tailwind dark‑mode classes, keeping parity between light and dark themes【F:resources/views/livewire/dashboard.blade.php†L1-L26】【F:resources/views/livewire/dashboard/widgets/admin/metrics/large.blade.php†L1-L13】.
- Customize modal has basic ARIA attributes but lacks focus management or escape‑key support. Keyboard users can tab behind the modal, which violates accessibility best practices【F:resources/views/livewire/customize-dashboard.blade.php†L4-L7】.
- The “Customize” button still includes a debugging `onclick` console log, which should be removed for production polish【F:resources/views/livewire/dashboard.blade.php†L15-L17】.

**Recommendation:**
- Add focus trapping (e.g., `x-trap` or Livewire focus events) and `@keydown.escape` handler to the modal.
- Remove debug code and ensure all interactive elements have accessible labels.
- Add tests verifying modal focus and keyboard navigation.

### 6. Data Loading & Performance

- Dashboard level caching caches the full data array per user/role for five minutes【F:app/Livewire/Dashboard.php†L85-L101】.
- Admin metrics widget caches results separately per user for five minutes, mitigating repeated heavy counts【F:app/Livewire/Dashboard/Widgets/Admin/Metrics/Large.php†L24-L44】.
- Agent and client data methods perform multiple independent queries (e.g., numerous `Ticket::where` calls). When many widgets are active this could multiply queries.
- Some queries eager load related models (`getRecentActivity` uses `with(['client','owner'])`), but others like `getDepartmentRanking` might still produce N+1 if the department has many users.

**Recommendation:**
- Consolidate repeated ticket count queries with `withCount` or aggregate queries.
- Add database performance tests or Laravel debugbar metrics in development to watch for N+1 issues.

### 7. Error & Loading States

- Widgets implement explicit `$hasError` and `$dataLoaded` flags to show skeletons and error messages【F:resources/views/livewire/dashboard/widgets/admin/metrics/large.blade.php†L14-L33】.
- `Dashboard::refreshData` broadcasts events to reload all widgets and clear cache, providing resilience on manual refresh【F:app/Livewire/Dashboard.php†L324-L332】.
- Slow or failed loads are handled in the admin metrics widget, but other widgets (when added) must replicate this pattern.

### 8. Deprecated / Unused Assets

Legacy Blade dashboards remain in the codebase:
- `resources/views/livewire/dashboard/admin-dashboard.blade.php`
- `resources/views/livewire/dashboard/agent-dashboard.blade.php`
- `resources/views/livewire/dashboard/client-dashboard.blade.php`

These are superseded by the widget system and should be removed or redirected to `livewire.dashboard` to prevent stale deep links.

## Verification Checklist

Run through the following after fixes:

- [ ] Each role (admin/support/client) can access `/dashboard` only when possessing `dashboard.access` and the role‑specific permission.
- [ ] Widgets listed in the customization modal match the user’s permissions; unauthorized widgets do not appear.
- [ ] Preferences persist across refresh and respect widget `available_sizes`.
- [ ] Dashboard layout remains intact at 375px, 768px, and 1280px widths with various widget sizes.
- [ ] Modal traps focus, supports Escape to close, and is usable with keyboard and screen readers.
- [ ] Widget loading shows skeletons, handles API failures, and never fetches data for unauthorized users.
- [ ] Database query counts remain bounded when multiple widgets are visible; no N+1 queries.

## Cleanup Items

| File/Route | Action |
|---|---|
| `resources/views/livewire/dashboard/admin-dashboard.blade.php` | Delete – replaced by widget system |
| `resources/views/livewire/dashboard/agent-dashboard.blade.php` | Delete – replaced by widget system |
| `resources/views/livewire/dashboard/client-dashboard.blade.php` | Delete – replaced by widget system |
| Any routes or docs referencing above dashboards | Redirect to `/dashboard` |

## Notes on Testing

`composer install` and `vendor/bin/phpunit` could not run in this environment due to GitHub authentication and missing vendor packages. Ensure dependencies are installed and run the full test suite locally.

