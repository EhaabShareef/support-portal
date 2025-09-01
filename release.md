# Major Conflicts

## Permission “teams” vs code usage

config/permission.php: Currently teams is false, but code uses team helpers and department-scoped logic (e.g., setPermissionsTeamId() in middleware).
Impact: Role/permission scoping by department won’t actually work; assignments are global.
Fix options:
Enable teams: set config('permission.teams') true and re-run migrations that add department_id columns to roles and pivots, or add a new migration to include team columns.
Or remove team-based middleware/logic and lean on your explicit department_group filters in queries/policies.
**APPLY THIS FIX - REMOVE MIDDLEWARE LOGIC**

## Client role has wide “update” power

database/seeders/RolePermissionSeeder.php: Client gets tickets.update.
Combined with TicketPolicy and components, clients can pass “update” checks intended for staff:
app/Livewire/Tickets/MergeTicketsModal.php
uses $this->authorize('update', $ticket) → clients can merge tickets in same org (policy allows).
app/Livewire/Tickets/SplitTicketModal.php
authorizes ‘split’, and TicketPolicy::split returns tickets.update, so clients can split tickets.
app/Livewire/Tickets/ViewTicket.php
updateTicket gate is “edit” (has tickets.update) and lacks policy-based status check for clients.
Fix (pick one):
Remove tickets.update from Client role and gate client actions via specific abilities (reply, reopen).
Or keep tickets.update, but tighten policy methods and component checks:
TicketPolicy::update should not return true for clients generally; separate abilities (reply, reopen, setStatus, assign, merge, split) with specific logic.
In MergeTicketsModal/SplitTicketModal require admin/support (role check) or policy methods that enforce role.
Policy Issues

## Missing imports cause policy checks to fail at runtime

app/Policies/TicketPolicy.php
, 174 reference SettingsRepositoryInterface::class without import.
app/Policies/TicketPolicy.php
references TicketPriority::compare without import.
Fix: Add use App\Contracts\SettingsRepositoryInterface; and use App\Enums\TicketPriority; at top.
TicketNotePolicy uses non-existent user fields

app/Policies/TicketNotePolicy.php
, 39–40, 74, 78–79 use $user->department_id and $user->department?->department_group_id.
Users no longer have department_id or a department relation.
Fix: Base note visibility solely on department_group_id (user) vs ticket->department->department_group_id.
Controller Issues

AttachmentController uses non-existent user department fields
app/Http/Controllers/AttachmentController.php
–178, 196–203, 236–243 use $user->department and $user->department_id.
With the current schema, those are undefined. This will evaluate to null and deny or mis-allow access.
Fix: Enforce group-based checks only:
For support: $user->department_group_id === $ticket->department?->department_group_id.
For clients: $ticket->organization_id === $user->organization_id.
Also ensure methods return boolean; canUserAccessAttachment() has an implicit null return at the end — return false explicitly.
Livewire Components

## ManageTickets mixes group and non-existent user.department

app/Livewire/Tickets/ManageTickets.php:
Access checks correctly use department_group_id (353–355) — good.
Option builders are inconsistent:
467–496 use $user->department?->department_group_id — user has no department.
478–479 fallback to $user->department_id — user has no department_id.
495–496 TicketStatusModel::optionsForDepartmentGroup($user->department->department_group_id) — should use $user->department_group_id.
Fix: Replace all user->department and user->department_id usage with user->department_group_id, and query departments by group id.
ViewTicket allows client status changes without policy enforcement

app/Livewire/Tickets/ViewTicket.php:
Access checks by group are good (135–136).
updateTicket() (lines near 270) validates status via TicketStatusModel::validationRule() without scoping for clients and does not call the setStatus policy.
Clients can change status to any allowed by options() list; only priority escalation is restricted.
Fix:
For support: validate using TicketStatusModel::validationRule($user->department_group_id).
For clients: do not allow freeform status changes; drive through $user->can('setStatus', [$ticket, $status]).
Or move all status/priority changes through policies (setStatus/escalatePriority) for both components.
Option builders still use $user->department?->department_group_id and $user->department_id in several places (301–302, 796–808, 819–820) — switch to department_group_id.
CreateTicket: validation and logic look fine; client organization/assignment constraints are correct.

HardwareRelatedTickets status filter includes “pending” which isn’t seeded

app/Livewire/HardwareRelatedTickets.php: returns a status option ‘pending’ not present in TicketStatusSeeder.
Fix: Add ‘pending’ to statuses or remove it from the filter.
Model and Migration Alignment

Users table vs code

Users have department_group_id and no department_id.
User model has departmentGroup() and not department; “departments” accessor returns a collection via group.
Many ticket modules still reference user->department/user->department_id incorrectly.
Tickets table

Tickets remain tied to a concrete department_id, which is fine and used correctly across queries/policies.
Self-referencing FKs in tickets migration

Already fixed earlier by naming fk_tickets_split_from and fk_tickets_merged_into to avoid MySQL naming conflicts.
Spatie permissions tables and teams

database/migrations/2025_01_01_000019_create_permission_tables.php: uses $teams = config('permission.teams').
config/permission.php: teams=false and team_foreign_key points to department_id.
Fix: Decide on teams:
If you want department-scoped roles, set teams true and add columns. Then ensure middleware SetDepartmentTeam is registered and you set session('department_id') deterministically.
If not, remove team-id expectations from code and rely purely on department_group checks in queries/policies.
Security/Authorization Gaps To Address

Clients merging/splitting tickets

MergeTicketsModal authorizes ‘update’, and policy update returns true for clients (same org). Split uses split policy which also returns tickets.update.
Fix: Restrict merge/split to support/admin only. Implement merge/split in policy requiring role or a distinct permission like tickets.merge / tickets.split.
Client status updates

As above, restrict via policy setStatus and avoid broad tickets.update for clients.
Concrete Fix Checklist

Short list of changes I recommend making next:

Policies
TicketPolicy imports: app/Policies/TicketPolicy.php: add use App\Contracts\SettingsRepositoryInterface; and use App\Enums\TicketPriority;.
Tighten TicketPolicy:
update(): Do not return true for clients generally. Limit to admin/support. Handle client reopening via reopen() only.
split() and add merge(): restrict to admin/support.
Permissions
RolePermissionSeeder: remove tickets.update from client role, keep tickets.read and tickets.create (and maybe a tickets.reply if you add one).
Controllers
AttachmentController: replace user department checks (171–178, 196–203, 236–243) with department_group checks; return false explicitly at method end.
Livewire
ManageTickets: replace all $user->department?->department_group_id and $user->department_id with $user->department_group_id. Adjust queries accordingly (469–496, 478–479).
ViewTicket:
updateTicket() enforce policies: call $user->can('setStatus', [$this->ticket, $this->form['status']]) and $user->can('escalatePriority', [$this->ticket, $this->form['priority']]).
Replace all user->department references with department_group_id (301–302, 796–808, 819–820).
Optional
HardwareRelatedTickets: remove “pending” unless you seed it.
Teams: choose to enable or remove — I can implement either path cleanly.
If you want, I can apply these policy and component fixes now. Would you like me to:

Update TicketPolicy imports and tighten update/split (and add merge)?
Refactor AttachmentController, ManageTickets, and ViewTicket to use department_group_id and policy checks?
Adjust RolePermissionSeeder to remove tickets.update for client role?