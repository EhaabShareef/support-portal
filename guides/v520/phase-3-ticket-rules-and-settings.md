# Phase 3 – Ticket Rules and Settings

## Discovery Map
- `app/Livewire/ManageTickets.php`
  - Priority change handler lacks role guards: lines 280–304【F:app/Livewire/ManageTickets.php†L280-L304】
- `app/Livewire/ViewTicket.php`
  - Priority/status updates: lines 217–276, 312–378【F:app/Livewire/ViewTicket.php†L217-L276】【F:app/Livewire/ViewTicket.php†L312-L378】
- `app/Models/Ticket.php`
  - `priority` and `status` fields: lines 23–53【F:app/Models/Ticket.php†L23-L53】
- `app/Models/Setting.php`
  - Generic key/value API for application settings: entire file【F:app/Models/Setting.php†L1-L120】

## Required Changes
1. **One‑way priority escalation**
   - Add `escalatePriority` gate in `TicketPolicy` ensuring only support/admin can raise above current level.
   - `ManageTickets::changePriority` and `ViewTicket::updateTicket`
     ```diff
@@ line 284 ManageTickets.php @@
-    $ticket->update(['priority' => $priority]);
+    if(auth()->user()->hasRole('client') && TicketPriority::compare($priority, $ticket->priority) > 0) {
+        session()->flash('error', 'Clients cannot escalate priority.');
+        return;
+    }
+    $ticket->update(['priority' => $priority]);
+    if(TicketPriority::compare($priority, $ticket->priority) > 0) {
+        ActivityLog::record('ticket.priority_escalated', $ticket->id);
+    }
     ```
   - Add confirmation dialog in UI for support/admin before escalating.
2. **Reopen window setting**
   - Migration & seed: insert setting key `tickets.reopen_window_days` default `3`.
   - `ViewTicket::changeStatus`
     ```diff
@@ line 327 @@
-    $wasTicketClosed = $this->ticket->status === 'closed';
-    $isTicketBeingReopened = $wasTicketClosed && $status !== 'closed';
+    $wasTicketClosed = $this->ticket->status === 'closed';
+    $reopenLimit = Setting::get('tickets.reopen_window_days', 3);
+    $isWithinWindow = $this->ticket->closed_at && now()->diffInDays($this->ticket->closed_at) <= $reopenLimit;
+    $isTicketBeingReopened = $wasTicketClosed && $status !== 'closed' && $isWithinWindow;
+    if($wasTicketClosed && !$isWithinWindow && auth()->user()->hasRole('client')) {
+        session()->flash('error', 'Ticket closed more than ' . $reopenLimit . ' days ago. Please create a new ticket.');
+        return redirect()->route('tickets.create', ['subject' => 'Re: ' . $this->ticket->ticket_number]);
+    }
     ```
   - Document new setting in settings guide: key `tickets.reopen_window_days`, integer, validated ≥1.
3. **Audit log entries**
   - `ActivityLog` model: add static `record` helper; log priority escalations and reopen confirmations.
4. **Client escalation denial message**
   - In priority change methods, return validation message when blocked.
   - Provide “Are you sure?” dialog for support/admin escalations.
5. **Server‑side policy enforcement**
   - Extend `TicketPolicy::update` to guard against client escalations and reopen attempts beyond window.
   - Smoke tests verifying policy denies direct HTTP attempts.

## UX Rationale
- Clients receive immediate, contextual feedback and a clear path to open a new ticket when limits apply.
- Confirmation dialogs prevent accidental escalations by staff.

## Accessibility Notes
- Confirmation modals use focus traps and `aria-modal="true"`.
- Error messages are announced via Livewire `wire:loading.attr="aria-busy"` on forms.

## Performance Notes
- Setting lookup uses cached `Setting::get` to avoid repeated queries.
- Priority comparisons rely on enum helpers to avoid string comparisons.

## Deprecations / Safe Removals
- Remove any direct `status` or `priority` mutations bypassing policy checks in legacy scripts.
- Mark old reopen logic for deletion once setting is enforced.

## Test Checklist
- Feature: client cannot raise priority above current value after creation.
- Feature: support/admin escalation triggers confirmation modal and audit log.
- Feature: client reopening after window redirects to new ticket creation with subject prefilled.
- Policy: direct API calls respecting `tickets.reopen_window_days` and escalation rules.
- Setting: failing validation for negative or non‑integer window values.
