# Phase 4 – Multi‑Organization Clients

## Discovery Map
- `app/Models/User.php`
  - Single `organization_id` column and relationship: lines 51–52, 102–105, 113–120【F:app/Models/User.php†L51-L52】【F:app/Models/User.php†L102-L105】【F:app/Models/User.php†L113-L120】
- `app/Models/Organization.php`
  - `users()` hasMany assumption: lines 69–72【F:app/Models/Organization.php†L69-L72】
- `app/Livewire/CreateTicket.php`
  - Organization auto‑selection for clients: lines 97–124【F:app/Livewire/CreateTicket.php†L97-L124】
- `app/Livewire/ManageTickets.php`
  - Role‑based org filters rely on `user->organization_id`: lines 399–465【F:app/Livewire/ManageTickets.php†L399-L465】
- `routes/web.php`
  - Ticket routes guarded by `can:tickets.read` only: lines 66–90【F:routes/web.php†L66-L90】

## Required Changes
1. **Schema & Relationships**
   - Migration: create `organization_user` pivot (`user_id`, `organization_id`, timestamps).
   - Backfill: insert existing `users.organization_id` into pivot then drop column after verification; rollback re‑adds column.
   - `User.php`
     ```diff
@@ line 51 @@
-        'department_id',
-        'organization_id',
+        'department_id',
     ];
@@ line 102 @@
-    public function organization(): BelongsTo
-    {
-        return $this->belongsTo(Organization::class);
-    }
+    public function organizations(): BelongsToMany
+    {
+        return $this->belongsToMany(Organization::class)->withTimestamps();
+    }
@@ line 113 @@
-    public function tickets()
-    {
-        return $this->hasMany(Ticket::class, 'client_id');
-    }
+    public function tickets()
+    {
+        return $this->hasMany(Ticket::class, 'client_id');
+    }
     ```
   - `Organization.php`
     ```diff
@@ line 69 @@
-    public function users(): HasMany
-    {
-        return $this->hasMany(User::class, 'organization_id');
-    }
+    public function users(): BelongsToMany
+    {
+        return $this->belongsToMany(User::class)->withTimestamps();
+    }
     ```
2. **Policy & Query Updates**
   - Update `TicketPolicy::view`/`update` to check `user->organizations->contains($ticket->organization_id)` for clients.
   - Adjust `ManageTickets` filters to `whereHas('organizations', fn($q)=>$q->where('id',$orgId))` for clients.
   - `CreateTicket` mount: if client has multiple organizations, present selector; otherwise auto‑select first.
3. **UI Adjustments**
   - Ticket creation form: show organization dropdown for eligible clients; default to first allowed organization.
   - Listing filters: organization filter uses `auth()->user()->organizations` for clients.
   - Ensure Livewire components use `auth()->user()->organizations()->pluck('id')` for scoping.
4. **Spatie Permissions & Middleware**
   - Introduce `organization` scope middleware verifying `request()->route('organization')` is within user’s allowed set.
   - Update route groups and Livewire `can` checks to incorporate new pivot checks.
5. **Seeds & Fixtures**
   - Extend `ClientSampleDataSeeder` to attach each test client to one or more organizations via pivot.
   - Provide artisan command to backfill existing production users.

## UX Rationale
- Corporate clients can switch between organizations without separate accounts, streamlining support requests.
- Single‑org users retain existing behaviour with auto‑selected organization.

## Accessibility Notes
- Organization selector is keyboard navigable and labelled; announce changes via `aria-live`.
- When only one organization exists, render as static text to reduce tab stops.

## Performance Notes
- Index `organization_user.user_id` and `.organization_id` for fast scoping.
- Prefetch allowed organization IDs in session to avoid repeated pivot queries.

## Deprecations / Safe Removals
- Drop `users.organization_id` after backfill.
- Remove helper methods assuming singular organization, e.g., `User::organization()` accessor.

## Test Checklist
- Feature: multi‑org client can create ticket choosing any allowed organization; single‑org client auto‑selected.
- Feature: ticket listings and filters show only scoped organizations for clients.
- Policy: client cannot access tickets outside pivot memberships.
- Regression: admin/support unaffected; existing API tokens continue to function.
- Migration: backfill and rollback scripts verified on sample data.
