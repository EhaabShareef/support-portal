# Settings Module Restructure Overview

This document summarizes the proposed structure for the new settings module and lists legacy files that should be removed once the migration is complete.

## Proposed Structure

- **Shell**
  - Route: `/settings`
  - Component: `App\Livewire\Settings\Index`
  - View: `resources/views/livewire/settings/index.blade.php`
  - Displays cards linking to each module.

- **Modules**
  - Routes: `/settings/{module}`
  - Components live under `app/Livewire/Settings/{Module}/Index.php`
  - Views live under `resources/views/livewire/settings/{module}/index.blade.php`
  - Current modules: General, Tickets, Organization, Contracts, Hardware, Schedule, Users.

- **Sections**
  - Complex modules (e.g., Tickets) break into section components and views, e.g.:
    - `app/Livewire/Settings/Tickets/Workflow.php`
    - `resources/views/livewire/settings/tickets/workflow.blade.php`
  - Additional sections can be added by placing new component/view pairs in the corresponding module folder.

- **Data Access**
  - All settings should use a centralized repository/service for persistence and caching.
  - Keys are namespaced by module, e.g. `tickets.default_reply_status`.

## Legacy Files to Remove

The following files and directories are remnants of the previous settings implementation or debug utilities and should be deleted to keep the codebase clean:

- `_deprecated/admin-settings/`
- `_deprecated/views/admin-settings/`
- `config/access-test.php`
- `debug-claude/` (debug scripts)
- `error.text`
- Any references to `App\Livewire\Admin\Settings\Shell` (removed from `routes/web.php` in this commit)

Removing these files will eliminate confusion between legacy and new implementations.

