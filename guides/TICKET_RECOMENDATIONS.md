
# Tickets Module QA & UX Audit

his document outlines logic gaps, authorization risks, and UI/UX inconsistencies in the Tickets module, with actionable steps to address them.

---

## 1. Model & Relationship Review

### 1.1 TicketPolicy uses outdated column names
**File**: `app/Policies/TicketPolicy.php`

Lines referencing non‑existent `dept_id` and `org_id` fields prevent proper authorization checks【F:app/Policies/TicketPolicy.php†L24-L41】.

**Steps**:
1. Replace `dept_id` with `department_id` and `org_id` with `organization_id` in all policy methods.
2. Add department‑group awareness (e.g., compare `$user->department->department_group_id` against `$ticket->department->department_group_id`).
3. Create unit tests for `view`, `update`, and `assign` to cover Admin, Agent (same department and same group), Client, and cross‑organization scenarios.

---

### 1.2 Ticket creation uses deprecated field names
**File**: `app/Livewire/CreateTicket.php`

Form fields and validation still expect `org_id` and `dept_id`, mismatching the database schema【F:app/Livewire/CreateTicket.php†L14-L33】.

**Steps**:
1. Rename form keys to `organization_id` and `department_id`.
2. In the `rules()` method, update validation rules to the new keys and ensure `client_id` is derived from `auth()->id()`.
3. In `submit()`, override `organization_id` and `client_id` with authenticated values for clients to prevent cross‑org ticket creation.
4. Update the corresponding Blade view (`resources/views/livewire/create-ticket.blade.php`) to bind the new keys.

---

### 1.3 Missing department‑group linkage in Ticket model
**File**: `app/Models/Ticket.php`

Tickets cannot be filtered by department group, hindering agents assigned to a group covering multiple departments.

**Steps**:
1. Add a helper relation `public function departmentGroup()` that returns `$this->department->departmentGroup()`.
2. Introduce a `scopeForDepartmentGroup($query, $groupId)` to enable group‑based queries.
3. Update seeders/tests to include tickets across multiple departments within a group.

---

## 2. Access Control & Authorization

### 2.1 ManageTickets lacks client safeguards
**File**: `app/Livewire/ManageTickets.php`

Clients cannot pick a department (empty collection) and the `save()` method trusts user‑supplied organization/client IDs【F:app/Livewire/ManageTickets.php†L241-L265】.

**Steps**:
1. When the authenticated user is a Client, load all active departments for selection.
2. In `save()`, enforce `organization_id = auth()->user()->organization_id` and `client_id = auth()->id()` for clients and agents.
3. Add authorization tests rejecting mismatched organization/department combinations.

---

### 2.2 support access limited to single department
**Files**: `app/Livewire/ManageTickets.php`, `app/Livewire/ViewTicket.php`

support only see tickets where `department_id` equals their own, ignoring department‑group assignments【F:app/Livewire/ManageTickets.php†L174-L201】【F:app/Livewire/ViewTicket.php†L62-L68】.

**Steps**:
1. Extend filtering in both components to allow agents whose department group matches the ticket’s department group.
2. Add computed property on `User` for `department_group_id` and utilize it in queries.
3. Write feature tests ensuring support with group membership can access tickets in any department within that group.

---

### 2.3 Legacy controllers reference obsolete fields
**Files**: `app/Http/Controllers/TicketController.php`, `TicketMessageController.php`, `TicketNoteController.php`

Controllers still use `org_id` and `dept_id` even though routes rely on Livewire components【F:app/Http/Controllers/TicketController.php†L23-L40】.

**Steps**:
1. Confirm these controllers are unused; if so, delete them to avoid confusion.
2. If API endpoints are required later, refactor controllers to the current schema and duplicate Livewire validation.

---

## 3. UI/UX Assessment

### 3.1 CreateTicket page deviates from design system
**File**: `resources/views/livewire/create-ticket.blade.php`

Uses legacy `form-input` and `page-header` classes, causing visual inconsistency and missing dark‑mode styles【F:resources/views/livewire/create-ticket.blade.php†L1-L52】.

**Steps**:
1. Replace legacy classes with shared component utilities (`bg-white/5`, `dark:bg-neutral-800`, `btn-primary`, etc.).
2. Optionally remove standalone page and trigger the ManageTickets modal via route redirect.
3. Ensure dark‑mode variants exist for all text and backgrounds.

---

### 3.2 ViewTicket duplicates conversation markup
**File**: `resources/views/livewire/view-ticket.blade.php`

Message threads are rendered twice, complicating maintenance and risking inconsistent styling.

**Steps**:
1. Extract a reusable Blade component (e.g., `<x-ticket-messages>`).
2. Replace both loops with the component, passing `messages` and `currentUser`.
3. Create a snapshot test to prevent future markup regressions.

---

### 3.3 Improve responsiveness and dark‑mode coverage

Some table layouts and form elements lack responsive classes or dark‑mode variants, leading to overflow on mobile and unreadable text in dark themes.

**Steps**:
1. Audit all ticket views for `sm:`, `md:`, `lg:` breakpoints and add missing responsive utilities.
2. Ensure every `bg-*` or `text-*` class has a `dark:` counterpart.
3. Test flows in both themes to confirm contrast ratios meet accessibility guidelines.

---

## 4. Quality‑of‑Life Enhancements

1. **Real‑time updates**: integrate broadcasting (Pusher/Laravel Echo) so agents see new messages without manual refresh.
2. **Status/priority badges**: centralize badge components to ensure consistent colors and typography.
3. **Attachment previews**: display thumbnails or icons for common file types in ViewTicket.

---

## Summary Implementation Plan

1. **Refactor authorization layer** (TicketPolicy, Livewire components) to respect departments, department groups, and organizations.
2. **Rename legacy fields** across models, Livewire components, and views to match current schema.
3. **Align UI with design system** and remove duplicate markup through reusable components.
4. **Delete or modernize legacy controllers**.
5. **Add tests** covering role‑based access and UI component rendering.

Following these steps will tighten role enforcement, reduce maintenance overhead, and deliver a more consistent user experience across the Tickets module.
