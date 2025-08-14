# User Activity Audit Implementation

## Schema
- **Table:** `user_activities`
- **Fields:** `id`, `user_id`, `activity_type`, `action`, `model_type`, `model_id`, `message`, `changes` (JSON), `ip_address`, `user_agent`, `request_id`, `created_at`, `updated_at`
- **Indexes:** `user_id`, `activity_type`, `action`, `created_at`, composite `(model_type, model_id)`

## ActivityLogger Service
- **File:** `app/Services/ActivityLogger.php`
- **API:** `log(user, activityType, action, targetModel, message, changes = [], context = [])`
- **Helper:** `logModelChange(user, activityType, action, model, message, context = [])`
- **Call Sites:**
  - Model events via `UserActivityObserver`
  - Ticket priority escalations in `ManageTickets` and `ViewTicket`
  - Report exports in `UserActivityReport`
  - Login/Logout listeners in `EventServiceProvider`
  - Issue reporter component
  - Exception handler in `bootstrap/app.php`

## Modules & Actions Logged
- Tickets, Organizations, Contracts, Hardware, Schedules, Users, Roles, Permissions, Settings (create/update/delete/status changes)
- Auth events: login, logout
- Reports exports
- User submitted issues and unhandled exceptions

## Reports Integration
- **Component:** `app/Livewire/Admin/Reports/UserActivityReport.php`
- **View:** `resources/views/livewire/admin/reports/user-activity-report.blade.php`
- **Route:** `admin.reports.user-activity`
- Added to reports dashboard

## Request Context Middleware
- **File:** `app/Http/Middleware/RequestContext.php`
- Seeds `request_id`, `ip_address`, `user_agent`

## Auth Event Listeners
- **File:** `app/Providers/EventServiceProvider.php`
- Logs login/logout via ActivityLogger

## Exception Hook
- **Location:** `bootstrap/app.php`
- `report()` handler logs unhandled exceptions as `issues`

## Test Plan
1. Creating a ticket records a `tickets/created` activity.
2. Updating ticket priority logs `tickets/priority_escalated` with before/after.
3. Report export logs `reports/exported`.
4. Unauthorized users cannot access `/admin/reports/user-activity`.
5. Filters in activity report return expected rows.
6. Issue reporter creates `issues/created` without blocking navigation.

Deprecated `ActivityLog` model and `LogsActivity` trait have been removed.
