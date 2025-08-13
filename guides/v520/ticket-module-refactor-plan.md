# Ticket Module Audit & Refactor Plan

This document captures structural refactors, bug fixes, and new feature work for the ticket module.

## 1. Refactors
- **Split large Livewire components**
  - Extract conversation handling from [`app/Livewire/ViewTicket.php`](../app/Livewire/ViewTicket.php) into a dedicated `TicketConversation` component to reduce the 600+ line class and centralize refresh logic【F:app/Livewire/ViewTicket.php†L101-L175】.
  - Break [`resources/views/livewire/view-ticket.blade.php`](../resources/views/livewire/view-ticket.blade.php) into partials for header, details, conversation, and modals; conversation markup alone spans 100+ lines and mixes style rules with data checks【F:resources/views/livewire/view-ticket.blade.php†L558-L660】.
- **Consistent naming & folder structure**
  - Group ticket views under `resources/views/tickets/` with sub‑partials for details, conversation, notes, and modals to mirror Livewire component names.
- **Centralize system message rendering**
  - Move the conditional styling for system/notes/replies out of Blade and into a reusable view component (e.g., `components/tickets/conversation-item.blade.php`).
- **Identify duplicate/unused code**
  - Desktop and mobile quick‑action markup in [`resources/views/livewire/manage-tickets.blade.php`](../resources/views/livewire/manage-tickets.blade.php) is duplicated; consolidate with a Blade component.
  - No unused ticket components were found.

## 2. Issue Fixes
### Conversation Thread & System Messages
- Map `ticket_messages.is_system` to three visual styles in the conversation component:

  | Type          | Avatar (solid) | Icon    | Border (bottom dashed) |
  |---------------|----------------|---------|------------------------|
  | Closing       | `bg-red-500`   | warning | `border-b border-red-500 border-dashed` |
  | Reopen        | `bg-sky-500`   | arrow   | `border-b border-sky-500 border-dashed` |
  | Note          | `bg-yellow-500`| note    | `border-b border-yellow-500 border-dashed` |

- Remove left border colors from note messages and ensure avatars use flat colors (no gradients)【F:resources/views/livewire/view-ticket.blade.php†L569-L578】.

### Message & Status Handling
- On close: split remarks and solution summary
  - Create a **system-only** closing message even when remarks are blank; send solution summary as a normal message visible to clients【F:app/Livewire/ViewTicket.php†L495-L525】.
- Replies default status to `in_progress` but allow override before send【F:app/Livewire/ViewTicket.php†L139-L139】【F:app/Livewire/ViewTicket.php†L552-L576】.
- When a ticket is closed hide all edit buttons except **Reopen** in both ticket view and quick actions【F:resources/views/livewire/view-ticket.blade.php†L88-L96】【F:resources/views/livewire/manage-tickets.blade.php†L330-L337】.
- Ensure conversation refresh after note or edit by emitting events from the note component and listening in the conversation component instead of reloading the whole page【F:app/Livewire/ViewTicket.php†L142-L175】【F:app/Livewire/ViewTicket.php†L612-L646】.

### Ticket Close & Reopen UX
- Manage view close button should open a confirmation modal directing users to the ticket view for remarks/summary【F:resources/views/livewire/manage-tickets.blade.php†L330-L337】.
- Reopening tickets should allow admins to supply an optional reason; if provided, add a system message, otherwise use the default reopen message【F:app/Livewire/ManageTickets.php†L605-L618】.

## 3. New Features
### Ticket View
- Display organization notes above internal notes as a new “Organization Note” card (unlimited characters) under Ticket Details【F:resources/views/livewire/view-ticket.blade.php†L83-L88】.

### Ticket Manage
- Show note and attachment icons beside ticket numbers in the listing; current icons column can be merged into ticket number column for brevity【F:resources/views/livewire/manage-tickets.blade.php†L200-L214】.
- Include Department Group and Department columns; header and row output already exist but should be finalized【F:resources/views/livewire/manage-tickets.blade.php†L233-L241】.
- Column “Assigned To” should be labeled “Owner” (already reflected)【F:resources/views/livewire/manage-tickets.blade.php†L176-L176】.

### Organization View
- Replace any legacy “Quick Stats” card with an always-visible “Organization Notes” card; current notes block can be expanded to fill the removed section【F:resources/views/livewire/view-organization.blade.php†L194-L202】.

### General Ticket Rules
- Enforce priority escalation rules so clients cannot raise priority and only Admin/Support can set `high` or `critical`; show descriptive errors and a confirmation dialog before escalation【F:app/Livewire/ManageTickets.php†L306-L315】【F:resources/views/livewire/view-ticket.blade.php†L694-L728】.
- Limit reopen actions to tickets closed within X days (default 3); display error with link to create a new ticket referencing the old one when over the limit【F:app/Livewire/ManageTickets.php†L592-L603】.

