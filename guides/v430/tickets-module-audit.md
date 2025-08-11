# Tickets Module Audit

## Current UX Flow & Pain Points

### Creation
- Form exposes organization and assignee fields even for clients; values are later overridden, causing confusion and hinting at missing authorization checks.
- A generic hotline note is appended to every ticket after creation, regardless of priority.

### Listing
- Quick filters provide “all”, “my tickets”, “my department”, and “unassigned”, but the component also contains an unused `my_department_group` filter.
- Status badges rely on a hard‑coded color map that omits several status values, leading to inconsistent presentation.
- Table shows only a view action; no quick actions such as self‑assignment or closure.

### Viewing & Updating
- Header already displays the ticket subject, yet the edit form still contains a read‑only subject field.
- Messages and notes refresh only after actions and do not update in real time if another user posts.
- Attachment handling requires opening a modal for preview; inline previews for common types are absent.

## Recommended Improvements (Prioritized)
1. **Role‑aware authorization checks**: enforce `tickets.read`/`tickets.create` abilities on routes and Livewire components to prevent unauthorized access.
2. **Critical priority confirmation**: when submitting a ticket marked `critical`, show a confirmation dialog instructing the user to call the hotline and stay available.
3. **Remove redundant subject field**: hide the subject input on the ticket edit form since the subject is immutable and displayed elsewhere.
4. **Inline attachment previews**: render image and PDF attachments directly in the message thread with thumbnail previews.
5. **Contextual badges**: generate status and priority labels directly from enums to ensure all states have consistent colors and text.
6. **Quick actions on listing**: add buttons for “assign to me”, “close ticket”, and similar frequent operations.
7. **Enhanced empty states**: include illustrations and guidance with a call‑to‑action button for creating a ticket when none exist.
8. **Improved filtering**: allow saving filter presets and provide multi‑select for statuses or priorities.
9. **Real‑time updates**: introduce Livewire polling or WebSockets so messages and notes update without manual refresh.

## Bugs & Inconsistencies
- `ManageTickets` eager loads the latest message with a `limit(1)` which triggers N+1 queries on some database engines【F:app/Livewire/ManageTickets.php†L232-L237】
- Status color map omits several enum values like `monitoring` or `awaiting_case_closure`【F:resources/views/livewire/manage-tickets.blade.php†L233-L260】
- Route group allows any authenticated user to access ticket pages without checking abilities【F:routes/web.php†L86-L89】
- Hotline note added to every ticket irrespective of priority【F:app/Livewire/CreateTicket.php†L83-L90】
- Subject field appears in edit form despite being read‑only and shown in the header【F:resources/views/livewire/view-ticket.blade.php†L97-L102】

## UI/UX Enhancements by Component
- **CreateTicket** (`app/Livewire/CreateTicket.php` & view): hide organization/assignee fields for clients; show post‑submission modal for critical tickets.
- **ManageTickets**: add quick‑action buttons in `resources/views/livewire/manage-tickets.blade.php` table rows; use enum helpers for badge rendering; surface “my department group” filter in UI or remove dead code.
- **ViewTicket**: remove subject field from edit form; show inline attachment thumbnails; use Livewire polling for conversation updates.

## Backend / Database Considerations
- Introduce a `critical_confirmed` boolean on tickets to track whether hotline guidance was acknowledged.
- Create a dedicated `latest_message_at` column updated via events to avoid subqueries or N+1 issues when listing tickets.
- Add indexes on `status`, `priority`, and `assigned_to` to improve filtering performance on large datasets.

## Test Plan
1. **Authorization**
   - Attempt to access `/tickets/manage` and `/tickets/create` as a user without `tickets.read` or `tickets.create` permissions.
   - Verify 403 responses and hidden navigation links.
2. **Critical priority workflow**
   - Submit a ticket with priority `critical` and confirm hotline dialog appears; ensure normal tickets bypass it.
3. **Listing quick actions**
   - Use “assign to me” button on a ticket and confirm assignment updates instantly.
   - Close a ticket via quick action and verify it disappears from open lists.
4. **Attachment previews**
   - Upload image and PDF attachments and ensure thumbnails or inline viewers render in the conversation.
5. **Real‑time updates**
   - Open the same ticket in two browsers and ensure messages/notes appear in both without manual refresh.
6. **Filtering**
   - Save a filter preset (e.g., high priority, open status) and reapply it to confirm persistence.

