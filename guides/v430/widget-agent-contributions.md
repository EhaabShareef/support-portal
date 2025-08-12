# Agent Ticket Contributions Widget

This guide explains how to add an "Agent Ticket Contributions" widget that visualizes a support agent's daily ticket activity in a GitHub-style contribution heatmap. The steps below map directly to the project's existing widget framework.

## 1. Understand the Existing Widget System

### Rendering pipeline
Dashboard widgets are loaded through the main dashboard view, which loops over user widgets, resolves the component for the chosen size, and renders it within the grid layout【F:resources/views/livewire/dashboard.blade.php†L29-L42】.

### Registration & sizing
Widgets are registered in the seeder with a `base_component`, `available_sizes`, and role-based permissions【F:database/seeders/DashboardWidgetSeeder.php†L145-L163】. Size tokens map to component suffixes (`small`, `medium`, `large`) in the `DashboardWidget` model【F:app/Models/DashboardWidget.php†L151-L158】, and CSS grid spans are defined in `Dashboard::getWidgetClasses`【F:app/Livewire/Dashboard.php†L347-L357】.

### User preference storage
Per-user visibility, size, order, and options persist in the `user_widget_settings` table【F:database/migrations/2025_01_01_000025_create_user_widget_settings_table.php†L14-L26】.

### Widget component pattern
Existing widgets check permissions in `mount`, cache data in `loadData`, expose `refreshData`, and track `$dataLoaded` / `$hasError` states【F:app/Livewire/Dashboard/Widgets/Support/MyWorkload/Small.php†L16-L44】. Views follow a consistent structure with a header, refresh button, loading skeleton, and error fallback【F:resources/views/livewire/dashboard/widgets/admin/metrics/small.blade.php†L1-L50】.

## 2. Files and Naming

1. **Livewire components**:  
   - `app/Livewire/Dashboard/Widgets/Support/AgentContributions/Small.php`  
   - `app/Livewire/Dashboard/Widgets/Support/AgentContributions/Medium.php`  
   - `app/Livewire/Dashboard/Widgets/Support/AgentContributions/Large.php`
   Each class mirrors the pattern above and receives no props other than Livewire defaults. They emit a `refreshData` event when reloaded.

2. **Blade views**:  
   - `resources/views/livewire/dashboard/widgets/support/agent-contributions/small.blade.php`  
   - `resources/views/livewire/dashboard/widgets/support/agent-contributions/medium.blade.php`  
   - `resources/views/livewire/dashboard/widgets/support/agent-contributions/large.blade.php`
   Views share markup for a heatmap grid with a minimal "Less → More" legend.

3. **Seeder registration**: Insert a new entry in `DashboardWidgetSeeder::createSupportWidgets` with `base_component => 'support.agent-contributions'`, `available_sizes => ['1x1','2x2','3x2']`, `default_size => '2x2'`, and `permissions => ['dashboard.support']`.

## 3. Data Definition and Fetching

- **Contribution definition**: one count per ticket per day when the agent:
  - creates a ticket (tickets.created_by == agent),
  - posts an update (ticket_messages.sender_id == agent), or
  - closes/resolves a ticket they own (tickets.owner_id == agent and closed_at/resolved_at on that day).
- **Aggregation**: group events by date and count distinct ticket IDs so multiple actions on the same ticket in a day count once.
- **Query**: union subqueries for create/update/close events, then `groupBy(date)` in SQL; limit range based on widget size.
- **Caching**: wrap the query in `Cache::remember("agent_contrib_{$userId}_{$range}", ttl, fn() => ...)` with a short TTL (e.g., 300s).
- **Permissions**: abort in `mount` if the user lacks `dashboard.support` to avoid unnecessary queries (pattern above).

## 4. Size Modes and Date Ranges

Read the size from the component class:

| Size | Token | Date window | Grid columns |
|-----|-------|-------------|--------------|
| Small | `1x1` | last 7 days (today-6 → today) | 1 week (1 column × 7 rows) |
| Medium | `2x2` | current month | up to 5 columns |
| Large | `3x2` | current year | 52–53 columns |

Use the current date as the anchor, ordering weeks left-to-right and days top-to-bottom, matching GitHub's contribution layout.

## 5. Rendering the Heatmap

- **Grid**: CSS grid with 7 rows (days) and variable columns. Left axis shows day initials when space allows. On small screens, enable horizontal scroll or reduce cell size.
- **Cells**: square `div` or `button` elements sized via Tailwind utility (`w-3 h-3` etc.), each with `aria-label="{date}: {count} tickets"` and `tabindex="0"` for keyboard access.
- **Color scale**: monochrome theme using 5 greens: empty (`bg-neutral-200`), then progressively darker greens for counts exceeding thresholds (0, q1, q2, q3, max). Thresholds may be simple buckets (1, 2–3, 4–6, 7+) or computed from quantiles.
- **Legend**: "Less" and "More" labels with sample swatches below the grid.
- **Tooltip**: show on hover and focus using Alpine/Livewire or `<div role="tooltip">` that displays date and count. Respect `prefers-reduced-motion` by disabling transitions.

## 6. Data Loading Lifecycle

1. `mount` → permission check, compute date range, call `loadData`.
2. `loadData` → fetch grouped counts, populate `$heatmap`, set `$dataLoaded` true.
3. `refreshData` → clear cache and re-invoke `loadData`.
4. View shows skeleton until `$dataLoaded` is true; display fallback text ("No activity") if all counts are zero.

## 7. Registration & Persistence

1. **Seeder**: add the widget entry mentioned above and rerun `DashboardWidgetSeeder` to populate `dashboard_widgets`.
2. **User settings**: no extra options; `UserWidgetSetting` already persists visibility, size, and order for each user【F:database/migrations/2025_01_01_000025_create_user_widget_settings_table.php†L14-L26】.
3. **Customization modal**: the new widget will appear automatically for support agents once seeded because the modal iterates over widgets from the database with the user's `available_sizes` list【F:database/seeders/DashboardWidgetSeeder.php†L145-L163】.

## 8. Styling & Theme Alignment

- Use existing container styles (`bg-white/5 dark:bg-neutral-800 border border-white/20 rounded-lg`) as seen in current widgets to maintain the monochrome look【F:resources/views/livewire/dashboard/widgets/admin/metrics/small.blade.php†L1-L10】.
- Cell colors should be variations of `bg-green-200`, `bg-green-300`, `bg-green-400`, `bg-green-500`, `bg-green-600` applied via Tailwind classes.
- Ensure dark-mode contrast by pairing with `dark:bg-green-700` etc.

## 9. Accessibility Considerations

- Each cell is focusable and announces its date and count via `aria-label`.
- Tooltip appears on focus as well as hover, positioned with `role="tooltip"`.
- Honor `prefers-reduced-motion` by disabling color transition animations.
- Provide an accessible legend and fallback text when the range has no contributions.

## 10. Responsiveness & Performance

- On narrow screens, allow horizontal scrolling of the heatmap or collapse weeks into fewer columns.
- Server-side grouping keeps payloads lightweight; return a simple `{date => count}` map.
- Optionally cache results per user and range with a short TTL to avoid repeated queries.

## 11. Testing Notes

- **Unit tests**: verify date-window logic for all three sizes and that permission checks prevent data loading for unauthorized users.
- **Feature tests**: snapshot color bucket assignments for known datasets and ensure legend counts align with bucket thresholds.
- **Accessibility tests**: check that cells expose correct `aria-label` values and that tooltips appear on keyboard focus.

Following this plan will yield a drop-in heatmap widget consistent with the existing dashboard architecture and style.
