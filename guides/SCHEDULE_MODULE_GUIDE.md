# Schedule Module Implementation Guide

## Overview
This guide outlines how to add a monthly schedule module to the Support Portal. The module tracks user activities using a color-coded calendar so admins can visualize duties and events for every agent and admin.

## 1. Data Model
### 1.1 Tables
- **`schedule_event_types`** – maintains event codes, labels and colors so admins can change them later.
- **`schedules`** – stores the user, event type and date plus optional remarks.

### 1.2 Migrations
Create the migrations:
```bash
php artisan make:migration create_schedule_event_types_table
php artisan make:migration create_schedules_table
```
Example structure:
```php
Schema::create('schedule_event_types', function (Blueprint $table) {
    $table->id();
    $table->string('code')->unique();
    $table->string('label');
    $table->string('color', 20); // hex or tailwind class
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});

Schema::create('schedules', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('event_type_id')->constrained('schedule_event_types');
    $table->date('date');
    $table->string('remarks')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});
```
Seed `schedule_event_types` with default codes (PR, PO, HAS, WFH, etc.) and colors.

### 1.3 Model Relationships
- `User` **has many** `Schedule` entries.
- `Schedule` **belongs to** `User` and `ScheduleEventType`.
- `ScheduleEventType` **has many** `Schedule` entries.
- Access department grouping through `$schedule->user->department->departmentGroup` to display users under their group.

## 2. Livewire Components
### 2.1 Calendar View (`ScheduleCalendar`)
Responsibilities:
- Render current month in a grid where rows are users grouped by Department Group and columns are days.
- Pull schedules for all visible users in the month using eager loading.
- Show events as small colored badges inside each cell. If multiple exist, stack or show "+more" with an Alpine dropdown.
- Provide filters for Department Group, Users and Event Type with `<select>` elements bound to Livewire properties.

### 2.2 Admin Modal (`ScheduleEditor`)
Responsibilities:
- Visible only to Admins (`auth()->user()->isAdmin()`).
- Form fields: user multi-select, date range picker, event type selector and optional remarks.
- On save, create `Schedule` records for each user/date combination and refresh `ScheduleCalendar` using Livewire events.
- Allow editing or deleting existing entries via icons inside each cell.

## 3. UI Layout
- Use Tailwind to build a responsive table-like grid:
  - Sticky header for days 1–31.
  - Left column lists users; department group headers separate groups.
  - Cells have `min-h-16` with flex column to stack multiple events.
- Event badges: `inline-block px-1 rounded text-xs text-white` with dynamic background color from `schedule_event_types`.
- Remarks: show on hover using the HTML `title` attribute or an Alpine tooltip component.

## 4. Filtering & Grouping
- **Department Group Filter:** dropdown of active groups; selecting one limits the user rows.
- **User Filter:** multi-select of users within selected group(s).
- **Event Type Filter:** dropdown populated from `schedule_event_types`.
- Filters update the calendar reactively through Livewire.

## 5. Permissions
- Only users with the **Admin** role may create, update or delete schedules or event types.
- Agents and Clients can view their own schedules but not others.
- Apply checks in Livewire `mount()` methods and on mutating actions.

## 6. Managing Event Types
- Create an admin page or setting section (`ManageScheduleEventTypes` Livewire component) to add/edit/delete event types and choose colors.
- Color values can be plain hex codes or Tailwind color classes.
- When colors change, the calendar should pull fresh values via Livewire so styling updates automatically.

## 7. Integration Steps
1. **Create migrations and models** for `Schedule` and `ScheduleEventType`.
2. **Seed** default event types with their colors.
3. **Build Livewire components** (`ScheduleCalendar`, `ScheduleEditor`, `ManageScheduleEventTypes`).
4. **Add routes** and navigation links (e.g., `/schedule`).
5. **Implement policies or gates** ensuring only admins mutate schedules.
6. **Style views** with Tailwind and interactivity via Alpine for tooltips or "more" menus.
7. **Write tests** covering migrations, event creation and permission enforcement.
8. **Deploy** and run `php artisan migrate --seed` to initialize tables.

## 8. Future Enhancements
- Weekly or daily detailed views.
- CSV or PDF export of schedules.
- Notifications when users are assigned new events.
- Sync with external calendars (iCal, Google Calendar).

This guide provides the foundation for adding a flexible and extensible schedule module to the Support Portal.
