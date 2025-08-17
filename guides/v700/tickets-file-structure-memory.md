# Tickets File Structure Memory

This document inventories existing ticket-related files and their purposes. Paths are listed relative to the repository root.

## Livewire Components
- `app/Livewire/ManageTickets.php` – ticket listing and filters.
- `app/Livewire/ViewTicket.php` – full ticket detail page including actions.
- `app/Livewire/CreateTicket.php` – form for creating new tickets.
- `app/Livewire/Tickets/TicketConversation.php` – manages conversation thread, replies and notes.
- `app/Livewire/Dashboard/Widgets/Client/MyTickets/Small.php` – dashboard widget for client ticket overview.
- `app/Livewire/Dashboard/Widgets/Admin/TicketAnalytics/Small.php` – admin dashboard ticket analytics (small widget).
- `app/Livewire/Dashboard/Widgets/Admin/TicketAnalytics/Medium.php` – admin dashboard ticket analytics (medium widget).
- `app/Livewire/Admin/Reports/TicketVolumeReport.php` – admin report showing ticket volume.
- `app/Livewire/Admin/Settings/Tabs/TicketColors.php` – admin settings tab for ticket color configuration.
- `app/Livewire/Admin/Settings/Tabs/SettingsTicket.php` – admin settings tab for general ticket options.

## Blade Views
- `resources/views/livewire/view-ticket.blade.php` – main ticket detail view.
- `resources/views/livewire/manage-tickets.blade.php` – ticket listing view.
- `resources/views/livewire/create-ticket.blade.php` – create ticket view.
- `resources/views/livewire/tickets/conversation.blade.php` – conversation thread and reply/note modals.
- `resources/views/livewire/partials/organization/tickets-tab.blade.php` – organization profile tab displaying tickets.
- `resources/views/livewire/dashboard/widgets/client/my-tickets/small.blade.php` – client dashboard tickets widget view.
- `resources/views/livewire/dashboard/widgets/admin/ticket-analytics/small.blade.php` – admin dashboard ticket analytics widget (small).
- `resources/views/livewire/dashboard/widgets/admin/ticket-analytics/medium.blade.php` – admin dashboard ticket analytics widget (medium).
- `resources/views/livewire/admin/settings/tabs/ticket-colors.blade.php` – ticket color settings view.
- `resources/views/livewire/admin/settings/tabs/ticket.blade.php` – general ticket settings view.
- `resources/views/livewire/admin/reports/ticket-volume-report.blade.php` – ticket volume report view.

## Blade Components
- `resources/views/components/tickets/conversation-item.blade.php` – renders a single conversation entry.
- `resources/views/components/tickets/quick-actions.blade.php` – quick action buttons for ticket listings.
- `resources/views/components/tickets/notes.blade.php` – **deprecated**; unused notes sidebar.
- `resources/views/components/tickets/details.blade.php` – **deprecated**; unused ticket detail block.
- `resources/views/components/tickets/header.blade.php` – **deprecated**; legacy header partial.
- `resources/views/components/tickets/close-modal.blade.php` – **deprecated**; old modal for closing tickets.

## Models & Related Classes
- `app/Models/Ticket.php` – ticket model.
- `app/Models/TicketMessage.php` – messages attached to tickets.
- `app/Models/TicketNote.php` – notes for tickets.
- `app/Models/TicketStatus.php` – reference model for ticket statuses.
- `app/Enums/TicketPriority.php` – ticket priority enum.
- `app/Enums/TicketStatus.php` – ticket status enum.
- `app/Policies/TicketPolicy.php` – authorization rules for tickets.
- `app/Policies/TicketNotePolicy.php` – authorization rules for ticket notes.
- `app/Services/TicketColorService.php` – helper for ticket status/priority colors.

## Routes
- `routes/web.php` – defines routes for managing, viewing, creating, and reporting on tickets.

## Notes
- Files marked **deprecated** appear to have no references and may be removed in the refactor.
- Conversation thread currently lives in `app/Livewire/Tickets/TicketConversation.php` and `resources/views/livewire/tickets/conversation.blade.php`.
