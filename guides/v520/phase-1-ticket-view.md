# Phase 1 – Ticket View Changes

## Discovery Map
- `app/Livewire/ViewTicket.php`
  - Note flag defaults and message loading: lines 61–107, 109–116【F:app/Livewire/ViewTicket.php†L61-L116】
  - Permissions for editing/replying/notes: lines 145–185【F:app/Livewire/ViewTicket.php†L145-L185】
- `resources/views/livewire/view-ticket.blade.php`
  - Original description displayed separately: lines 244–258【F:resources/views/livewire/view-ticket.blade.php†L244-L258】
  - Internal note listing and controls: lines 263–333【F:resources/views/livewire/view-ticket.blade.php†L263-L333】
  - Conversation stream loop: lines 535–629【F:resources/views/livewire/view-ticket.blade.php†L535-L629】
- `app/Livewire/CreateTicket.php`
  - Description stored only on ticket record: lines 25–64, 97–124【F:app/Livewire/CreateTicket.php†L25-L124】
- `resources/views/livewire/view-organization.blade.php`
  - “Quick Stats” card placeholder: lines 194–218【F:resources/views/livewire/view-organization.blade.php†L194-L218】

## Required Changes
1. **Move description to conversation**
   - Migration: copy any existing `tickets.description` into a `ticket_messages` record and mark the ticket column deprecated.
   - `app/Livewire/CreateTicket.php`
     ```diff
@@ line 97 @@
-        $ticket = Ticket::create($validated);
+        $ticket = Ticket::create(collect($validated)->except('description')->toArray());
+        TicketMessage::create([
+            'ticket_id' => $ticket->id,
+            'sender_id' => $ticket->client_id,
+            'message' => $validated['description'],
+        ]);
     ```
   - `resources/views/livewire/view-ticket.blade.php`
     ```diff
@@ line 244–258 @@
-@if($ticket->description)
-    … existing description block …
-@endif
     ```
2. **Unified conversation ordering**
   - `app/Livewire/ViewTicket.php`
     ```diff
@@ line 76–107 @@
-    $this->ticket->setRelation('messages', $ticket->messages()->…->latest('created_at')->get());
+    $messages = $ticket->messages()->select([...])->with('sender','attachments');
+    $publicNotes = $ticket->notes()->where('is_internal', false)->select(['id as note_id','user_id as sender_id','note as message','created_at']) ;
+    $this->conversation = $messages->unionAll($publicNotes)->orderBy('created_at','desc')->get();
     ```
   - Replace view loop to iterate over `$conversation` and add branches for note vs reply vs system.
3. **System events participation**
   - Ensure status changes/close/reopen create `TicketMessage` entries with `is_system_message = true` in `changeStatus`, `updateTicket`, and `submitClose` methods.
4. **Note editing and authorization**
   - Create `app/Policies/TicketNotePolicy.php` with `update`/`delete` rules mirroring `TicketPolicy` view logic.
   - Register policy in `AuthServiceProvider`.
   - Update notes loop to show **Edit** button and disabled tooltip when `@cannot('update', $note)`.
5. **Default notes to internal**
   - `app/Livewire/ViewTicket.php`: set `$noteInternal = true` (line 61).
   - Add checkbox helper text clarifying public note behavior.
6. **Conversation type styles**
   - Ticket replies: existing neutral style.
   - Public notes: add left border and “Note” label.
   - System messages: retain blue treatment; include status-change icon.
7. **Organization Note card**
   - `resources/views/livewire/view-ticket.blade.php`: insert card after ticket details and before internal notes.
   - `resources/views/livewire/view-organization.blade.php`: replace “Quick Stats” with same card content.

## UX Rationale
- Placing the initial description and system events into the conversation gives a single chronological narrative, reducing cognitive load.
- Consistent note editing prevents dead‑ends and clarifies permissions.
- Organization notes surface context without hunting through separate tabs.

## Accessibility Notes
- Ensure Edit buttons and system message labels expose `aria-label` and follow focus order.
- Use `role="status"` on system messages so screen readers announce changes.
- Tooltip for disabled Edit uses `title` and remains visible on keyboard focus.

## Performance Notes
- Use eager‑loading for `notes.user` and `messages.attachments` to avoid N+1 issues.
- The union query should select only required columns and apply `orderBy` in SQL to keep pagination light.

## Deprecations / Safe Removals
- `tickets.description` column – retain for now but mark deprecated; plan removal after backfill.
- Legacy “Quick Stats” card in organization view.

## Test Checklist
- Feature: creating a ticket stores initial description as first message.
- Feature: conversation renders replies, public notes, and system messages in descending order.
- Policy: unauthorized users cannot edit notes; authorized users can.
- Livewire: toggling “internal” unchecked pushes note into conversation feed.
- Feature: Organization note card displays in ticket and organization views.
