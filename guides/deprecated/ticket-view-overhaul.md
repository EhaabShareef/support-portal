# Ticket View Overhaul

## File changes overview:

- **app/Livewire/ViewTicket.php** – _modified_: add eager loads for organization, contract, owner, department group & department; load counts for notes/attachments; emit `thread:refresh` and `ticket:refresh` after reply/note/close/reopen/edit/assign.
- **resources/views/livewire/tickets/view-ticket.blade.php** – _modified_: rebuild page into header, details/notes/conversation grid; mount `<livewire:tickets.quick-actions>` and `<livewire:tickets.conversation-thread>`; apply `grid grid-cols-12 gap-4`.
- **resources/views/components/tickets/header.blade.php** – _modified_: subject left, ticket number right, status & priority badges, and a quick actions row under the header.
- **app/Livewire/Tickets/QuickActions.php** – _modified_: expose Reply, Add Note, Edit, Close/Reopen, Assign to Me, Merge; gate each via `$this->authorize(...)`; dispatch `reply:toggle`, `note:toggle`, `close:toggle`, `reopen:toggle`, `merge:toggle`; emit `thread:refresh`/`ticket:refresh` after actions.
- **resources/views/livewire/tickets/quick-actions.blade.php** – _modified_: add monochrome buttons for all actions with `wire:loading.attr="disabled"` and permission checks.
- **app/Livewire/Tickets/ReplyForm.php** – _modified_: default `$replyStatus` to `config('tickets.default_reply_status', 'in_progress')`; after send emit `thread:refresh` and reset state.
- **app/Livewire/Tickets/NoteForm.php** – _modified_: keep internal notes in panel, public notes in thread; emit `thread:refresh` after submit.
- **app/Livewire/Tickets/CloseModal.php** – _modified_: on success, add system message, dispatch `thread:refresh` and `ticket:refresh`.
- **app/Livewire/Tickets/ReopenModal.php** – _modified_: accept optional reason, create system message, dispatch `thread:refresh` and `ticket:refresh`.
- **app/Livewire/Tickets/MergeTicketsModal.php** – _modified_: authorize merge, keep toggle event `merge:toggle`; refresh header on merge.
- **resources/views/livewire/tickets/conversation-thread.blade.php** – _modified_: loop over `$ticket->conversation` newest-first, use `wire:key="thread-{{ $item->type }}-{{ $item->id }}"` and `<x-tickets.conversation-item />`.
- **app/Livewire/Tickets/ConversationThread.php** – _modified_: eager load `sender` and `attachments`, listener `thread:refresh`, avoid Alpine `x-if` unmounts.
- **resources/views/components/tickets/conversation-item.blade.php** – _modified_: flat avatar & dashed divider for system messages, attachments rendered beneath body, `aria-label` on icon buttons.
- **resources/views/components/tickets/details.blade.php** – _modified_: ticket details card with organization, contract, owner, department group & department, created/updated times; truncate long strings with `title`.
- **resources/views/components/tickets/organization-note.blade.php** – _new_: single card showing `$ticket->organization->notes` placed above internal notes.
- **resources/views/components/tickets/notes.blade.php** – _modified_: internal notes container separated from conversation, using same card shell.
- **resources/views/livewire/tickets/merge-tickets-modal.blade.php** – _modified_: style modal with card classes and existing button variants.
- **resources/views/components/ticket/reply-bubble.blade.php** – _removed/deprecated_: replaced by `components/tickets/conversation-item.blade.php`.
- **tests/Feature/Tickets/ViewTicketTest.php** – _new_: cover header/badge/action gating, thread refresh after reply/note/close/reopen, organization note visibility.

## Agent Instruction:

1. **Header layout**
   1.1 Update `resources/views/components/tickets/header.blade.php`:
       - Wrap subject and ticket number in a flex container: `flex justify-between items-start`.
       - Place badges `<x-status-badge :status="$ticket->status" />` and `<x-priority-badge :priority="$ticket->priority" />` beside subject.
       - Below header, mount quick actions: `@livewire('tickets.quick-actions', ['ticket' => $ticket])`.
   1.2 In `app/Livewire/Tickets/QuickActions.php` add methods `edit()`, `assignToMe()`, `merge()`, `close()`, `reopen()`, `reply()`, `note()` with policy guards (`$this->authorize('update', $this->ticket)` etc.).
   1.3 Extend `resources/views/livewire/tickets/quick-actions.blade.php` to show buttons:
       ```html
       <div class="flex flex-wrap gap-2">
           <button wire:click="reply" class="btn-secondary" aria-label="Reply">Reply</button>
           <button wire:click="note" class="btn-secondary" aria-label="Add note">Note</button>
           <button wire:click="edit" class="btn-secondary" aria-label="Edit" @cannot('update', $ticket) disabled @endcannot>Edit</button>
           <button wire:click="close" class="btn-danger" x-show="$ticket->status !== 'closed'">Close</button>
           <button wire:click="reopen" class="btn-secondary" x-show="$ticket->status === 'closed'">Reopen</button>
           <button wire:click="assignToMe" class="btn-secondary" @cannot('assign', $ticket) disabled @endcannot>Assign to Me</button>
           <button wire:click="merge" class="btn-secondary" @cannot('update', $ticket) disabled @endcannot>Merge</button>
       </div>
       ```
       - Add `wire:loading.attr="disabled"` to each button.

2. **Body layout**
   2.1 In `resources/views/livewire/tickets/view-ticket.blade.php`, wrap body in `div class="grid grid-cols-12 gap-4"`.
   2.2 Column distribution:
       - Left `col-span-8` for conversation thread.
       - Right `col-span-4` stacked cards (ticket details, organization note, internal notes).
   2.3 Use card shell `bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50` on all cards.
   2.4 Truncate long fields with `<span class="truncate" title="{{ $value }}">{{ $value }}</span>` in `components/tickets/details.blade.php`.

3. **Conversation thread**
   3.1 In `app/Livewire/Tickets/ConversationThread.php::refreshConversation()` eager load:
       ```php
       $messages = $this->ticket->messages()
           ->select([...])
           ->with(['sender:id,name', 'attachments:id,uuid,attachable_id,attachable_type,original_name,stored_name,mime_type,size,is_image'])
           ->selectRaw("'message' as type")
           ->get();
       ```
   3.2 Merge public notes similarly and set relation `conversation` sorted descending.
   3.3 In `resources/views/livewire/tickets/conversation-thread.blade.php` replace loop with:
       ```blade
       @foreach($ticket->conversation as $item)
           <div wire:key="thread-{{ $item->type }}-{{ $item->id }}">
               <x-tickets.conversation-item :item="$item" :ticket="$ticket" />
           </div>
       @endforeach
       ```
   3.4 Ensure Livewire listener: `protected $listeners = ['thread:refresh' => 'refreshConversation'];`.

4. **Reply & Note forms**
   4.1 In `app/Livewire/Tickets/ReplyForm.php` set `public string $replyStatus = 'in_progress';` and allow override via config.
   4.2 After `sendMessage`, call `$this->dispatch('thread:refresh')->to(ConversationThread::class); $this->reset(['replyMessage','attachments','cc']);`.
   4.3 In `NoteForm`, maintain `$noteInternal` default `true`; after `addNote`:
       - If `!$this->noteInternal` also `$this->dispatch('thread:refresh')->to(ConversationThread::class);`.
       - Otherwise emit `refresh-notes` to `ViewTicket` or `Notes` panel.

5. **Quick actions: Close/Reopen/Merge/Assign**
   5.1 `CloseModal` and `ReopenModal` already exist; ensure methods `closeTicket()` and `reopenTicket()` call `$this->dispatch('thread:refresh')->to(ConversationThread::class); $this->dispatch('ticket:refresh')->to(QuickActions::class);`.
   5.2 `assignToMe()` in `ViewTicket.php` should authorize `assign`, update `owner_id`, then `$this->dispatch('ticket:refresh')->to(QuickActions::class);`.
   5.3 In `QuickActions.php` wire buttons to `dispatch('close:toggle')`, `dispatch('reopen:toggle')`, `dispatch('merge:toggle')` respectively.
   5.4 Merge button toggles `MergeTicketsModal`; if merge service not ready, keep button but display toast "coming soon".

6. **Eager loading & performance**
   6.1 In `app/Livewire/ViewTicket.php::mount()` add:
       ```php
       $ticket->load(['organization:id,name,notes', 'organization.contracts' => fn($q)=>$q->select(...)->limit(1),
                      'department.departmentGroup:id,name', 'owner:id,name'])
              ->loadCount(['notes', 'attachments']);
       ```
   6.2 When querying conversation, use eager loads from step 3 to avoid N+1.

7. **Style standardization & accessibility**
   7.1 Use existing button utility classes (`btn-primary`, `btn-secondary`, `btn-danger`).
   7.2 Ensure dark mode tokens (`dark:bg-neutral-900/50`, `dark:text-neutral-200`).
   7.3 Add `aria-label` to icon-only buttons; add `aria-live="polite"` on flash/toast container.
   7.4 Maintain logical focus order: header → quick actions → conversation → forms.

8. **Deprecated/unused items**
   - Delete `resources/views/components/ticket/reply-bubble.blade.php` (conversation now handled by `x-tickets.conversation-item`).
   - If Livewire quick-actions fully replaces Blade component `resources/views/components/tickets/quick-actions.blade.php`, remove that component and update `resources/views/livewire/manage-tickets.blade.php` to use the Livewire version.

9. **Tests & manual checklist**
   9.1 Create `tests/Feature/Tickets/ViewTicketTest.php`:
       - `test_header_shows_badges_and_actions_based_on_permissions()`
       - `test_thread_refreshes_after_reply_note_close_reopen()`
       - `test_internal_notes_hidden_from_clients_and_public_notes_in_thread()`
   9.2 Manual checklist:
       - Create ticket → verify header shows subject, number, badges.
       - Reply → thread updates without reload.
       - Add public note → appears in thread.
       - Add internal note → appears in internal panel only.
       - Close → confirmation modal, thread receives system message, buttons switch to Reopen.
       - Reopen → reason optional, system message added, buttons revert.
       - Assign to me → header owner updates, toast shown.
       - Merge button visible but gated without permission.

10. **Commit sequence (run in order)**
    1. Extract and refactor conversation thread component.
    2. Implement quick action methods and events.
    3. Rebuild header layout with quick actions row.
    4. Reorganize body grid with details/notes/organization note.
    5. Wire reply/note forms and close/reopen modals to refresh thread.
    6. Add eager loading and performance tweaks.
    7. Standardize styles and accessibility attributes.
    8. Remove deprecated `components/ticket/reply-bubble.blade.php` (and optional Blade quick-actions).
    9. Add tests and update any failing expectations.
    10. Final documentation and cleanup.
