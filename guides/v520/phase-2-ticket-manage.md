# Phase 2 – Ticket Manage Listing Improvements

## Discovery Map
- `app/Livewire/ManageTickets.php`
  - Query, sorting, and quick filters: lines 399–519【F:app/Livewire/ManageTickets.php†L399-L519】
  - Priority change handler: lines 280–304【F:app/Livewire/ManageTickets.php†L280-L304】
- `resources/views/livewire/manage-tickets.blade.php`
  - Desktop table header and row layout: lines 180–333【F:resources/views/livewire/manage-tickets.blade.php†L180-L333】
- `app/Models/Ticket.php`
  - Current `owner_id` relationship and scope: lines 60, 124–126, 242–244【F:app/Models/Ticket.php†L60】【F:app/Models/Ticket.php†L124-L126】【F:app/Models/Ticket.php†L242-L244】

## Required Changes
1. **Presence icons**
   - `app/Livewire/ManageTickets.php`
     ```diff
@@ line 399 @@
-    $query = Ticket::query()->with([...])->withCount('messages');
+    $query = Ticket::query()
+        ->with(['organization:id,name', 'department:id,name,department_group_id', 'department.departmentGroup:id,name', 'client:id,name,email', 'owner:id,name'])
+        ->withCount([
+            'notes as internal_note_count' => fn($q) => $q->where('is_internal', true),
+            'attachments'
+        ]);
     ```
   - `resources/views/livewire/manage-tickets.blade.php`
     ```diff
@@ line 194 @@
-    <div class="text-xs font-medium text-sky-600 dark:text-sky-400">
-        #{{ $ticket->ticket_number }}
-    </div>
+    <div class="flex items-center gap-1 text-xs font-medium text-sky-600 dark:text-sky-400">
+        #{{ $ticket->ticket_number }}
+        @if($ticket->internal_note_count)
+            <x-heroicon-o-document-text class="h-4 w-4" title="This ticket has internal notes" />
+        @endif
+        @if($ticket->attachments_count)
+            <x-heroicon-o-paper-clip class="h-4 w-4" title="This ticket has attachments" />
+        @endif
+    </div>
     ```
   - Provide similar icon row for mobile cards.
2. **Department group & department columns**
   - `resources/views/livewire/manage-tickets.blade.php` table header: insert columns after “Client”.
   - Update row layout with `$ticket->department->departmentGroup->name` and `$ticket->department->name`.
   - Add matching mobile card lines.
   - `ManageTickets` query: already eager‑loads `department.departmentGroup`; extend sortable/filterable arrays and UI.
3. **Rename “Assigned to” → “Owner”**
   - Database: rename the tickets column to `owner_id` and update related indexes.
   - `app/Models/Ticket.php`
     ```php
     // ... inside the model
     protected $fillable = [
         // ...
         'owner_id',
     ];

     protected $casts = [
         // ...
         'owner_id' => 'datetime', // retains null behaviour
     ];

     public function owner(): BelongsTo
     {
         return $this->belongsTo(User::class, 'owner_id');
     }

     public function scopeOwnedBy($query, $userId)
     {
         return $query->where('owner_id', $userId);
     }
     ```
   - Replace any lingering `assigned` references in `ManageTickets`, `ViewTicket`, tests, exports, and language strings with `owner`/`owner_id`.

## UX Rationale
- Icons quickly communicate hidden metadata without bloating the table.
- Splitting Department Group and Department clarifies routing of tickets.
- “Owner” aligns terminology with responsibility; naming consistency reduces cognitive friction.

## Accessibility Notes
- Icons include descriptive `title` attributes and `sr-only` text for screen readers.
- Additional columns maintain table header associations with `scope="col"`.
- Preserve color‑contrast and dark‑mode parity from existing monochrome palette.

## Performance Notes
- Added `withCount` reduces subqueries for note/attachment presence.
- Ensure `department` and `departmentGroup` are eager‑loaded to avoid N+1 lookups when rendering new columns.

## Deprecations / Safe Removals
- Remove `scopeAssignedTo` usage after `owner_id` migration.
- Update any lingering Blade components or helpers referencing `assigned`.

## Test Checklist
- Feature: listing displays icons only when internal notes or attachments exist.
- Feature: table shows Department Group and Department columns with sortable headers.
- Policy: ensure owner scope respects role‑based filtering.
- Regression: migrate data to `owner_id` without loss; rollback verifies reverse migration.
