# Phase 2 – Status by Department, Organization & Contracts

## Discovery Map
### Ticket Status Selection
- `app/Enums/TicketStatus.php` – hard‑coded status enum definitions【F:app/Enums/TicketStatus.php†L8-L17】
- `database/migrations/2025_01_01_000005_create_tickets_table.php` – tickets table stores status as ENUM, blocking new values【F:database/migrations/2025_01_01_000005_create_tickets_table.php†L20-L30】
- `app/Models/Ticket.php` – model scopes and helpers depend on the enum status field【F:app/Models/Ticket.php†L53-L262】
- `app/Livewire/ManageTickets.php` – exposes `filterStatus` and uses status chips in listings【F:app/Livewire/ManageTickets.php†L30-L59】
- `resources/views/livewire/manage-tickets.blade.php` – renders status badges in table rows【F:resources/views/livewire/manage-tickets.blade.php†L251-L255】
- `app/Livewire/ViewTicket.php` – validates status and loads related contract details including value/currency【F:app/Livewire/ViewTicket.php†L80-L88】【F:app/Livewire/ViewTicket.php†L281】
- `resources/views/livewire/view-ticket.blade.php` – displays active contract value beside status【F:resources/views/livewire/view-ticket.blade.php†L208-L210】
- `resources/views/livewire/partials/organization/tickets-tab.blade.php` – organization summary shows status badge per ticket【F:resources/views/livewire/partials/organization/tickets-tab.blade.php†L30-L32】
- `app/Policies/TicketPolicy.php` – contains status checks for reopen window enforcement【F:app/Policies/TicketPolicy.php†L95-L102】
- `app/Services/TicketColorService.php` – maps status keys to Tailwind classes used across badges

### Organization View Badges/Fields
- `app/Traits/ValidatesOrganizations.php` – company and TIN marked required, active boolean validated【F:app/Traits/ValidatesOrganizations.php†L15-L23】
- `app/Livewire/ManageOrganizations.php` – form state includes `is_active` flag and subscription status【F:app/Livewire/ManageOrganizations.php†L20-L31】
- `resources/views/livewire/manage-organizations.blade.php` – Subscription Status select and Active checkbox rendered separately【F:resources/views/livewire/manage-organizations.blade.php†L60-L101】
- `app/Livewire/ViewOrganization.php` – edit form mirrors validation trait and toggles active state
- `resources/views/livewire/view-organization.blade.php` – header currently renders dual Active/Subscription badges【F:resources/views/livewire/view-organization.blade.php†L17-L29】

### Contract Create/Edit Flows
- `database/migrations/2025_01_01_000009_create_organization_contracts_table.php` – includes `contract_value`, `currency`, and `terms_conditions` columns【F:database/migrations/2025_01_01_000009_create_organization_contracts_table.php†L25-L36】
- `app/Models/OrganizationContract.php` – fillable and casts reference removed fields【F:app/Models/OrganizationContract.php†L16-L27】【F:app/Models/OrganizationContract.php†L32-L37】
- `app/Livewire/OrganizationContractForm.php` – form/rules for type, value, currency, renewal, and terms【F:app/Livewire/OrganizationContractForm.php†L17-L44】
- `resources/views/livewire/organization-contract-form.blade.php` – UI for contract value/currency, end date & renewal, terms & conditions【F:resources/views/livewire/organization-contract-form.blade.php†L84-L116】【F:resources/views/livewire/organization-contract-form.blade.php†L119-L140】
- `app/Livewire/ManageContracts.php` – duplicate form logic for contract management【F:app/Livewire/ManageContracts.php†L19-L47】【F:app/Livewire/ManageContracts.php†L123-L156】
- `resources/views/livewire/manage-contracts.blade.php` – modal fields for value/currency and terms【F:resources/views/livewire/manage-contracts.blade.php†L123-L156】
- `resources/views/livewire/partials/organization/contracts-tab.blade.php` – shows contract value and currency chips【F:resources/views/livewire/partials/organization/contracts-tab.blade.php†L44-L48】
- `app/Livewire/ViewTicket.php` & `resources/views/livewire/view-ticket.blade.php` – ticket screen pulls/display contract value and currency【F:app/Livewire/ViewTicket.php†L80-L88】【F:resources/views/livewire/view-ticket.blade.php†L208-L211】
- `database/seeders/ClientSampleDataSeeder.php` – seeds contracts with value and terms text【F:database/seeders/ClientSampleDataSeeder.php†L271-L284】
- `app/Http/Controllers/OrganizationContractController.php` – legacy CRUD uses status/type enums and notes fields【F:app/Http/Controllers/OrganizationContractController.php†L28-L38】【F:app/Http/Controllers/OrganizationContractController.php†L65-L75】

## Ticket Status by Department Group (Settings‑driven)
1. **Create status store**
   - Introduce `ticket_statuses` table with `name`, `key`, `protected`, and pivot to department groups. Seed immutable Open, In Progress, Closed flagged as `protected`.
   - Replace ENUM column on tickets with string: migrate `tickets.status` to `string` and backfill existing values. Update `TicketStatus` enum to soft‑map base statuses while allowing dynamic entries. Adjust `Ticket` model casts/scopes accordingly.
2. **Settings UI**
   - Add Settings tab (`app/Livewire/Admin/Settings/Tabs/TicketStatuses.php`) allowing admin CRUD of statuses and assignment to department groups. Persist via `SettingsRepository` or dedicated model.
   - Hide delete/edit controls when `protected` is true.
3. **Form filtering**
   - `app/Livewire/ManageTickets.php` & `resources/views/livewire/manage-tickets.blade.php`: load allowed statuses for selected department group when creating tickets; populate a select replacing hardcoded default. Default to **Open**.
   - `app/Livewire/ViewTicket.php` & `resources/views/livewire/view-ticket.blade.php`: filter status dropdown to the ticket’s department‑group whitelist. Update validation rule to check membership instead of enum【F:app/Livewire/ViewTicket.php†L281】.
4. **Server enforcement**
   - Inject allow‑list check in `TicketPolicy` update and changeStatus methods so unauthorized values are rejected before save【F:app/Policies/TicketPolicy.php†L95-L102】.
   - Update `Ticket` model helpers and `TicketColorService` to source status list from new table; chip displays and filters should query through relationships instead of enum calls.
5. **Default status on replies**
   - When adding replies in `ViewTicket`, if no explicit status, set to **In Progress** for that department group.
6. **Migration note**
   - Because the current schema uses an ENUM【F:database/migrations/2025_01_01_000005_create_tickets_table.php†L20-L30】, include a migration to convert `tickets.status` to `string` before deploying dynamic statuses.

## Organization View Adjustments
1. **Single status badge**
   - Edit `resources/views/livewire/view-organization.blade.php` to drop the subscription badge and render only an Active/Inactive badge near the name【F:resources/views/livewire/view-organization.blade.php†L17-L29】.
2. **Optional fields**
   - In `app/Traits/ValidatesOrganizations.php`, change company and `tin_no` rules to `nullable|string` so both fields become optional【F:app/Traits/ValidatesOrganizations.php†L15-L18】.
   - Ensure `app/Livewire/ViewOrganization.php` and `app/Livewire/ManageOrganizations.php` accept null values and display “Not provided” when blank.
3. **Active toggle alignment**
   - Replace checkbox markup in `resources/views/livewire/manage-organizations.blade.php` with a monochrome toggle component placed on the same row as `subscription_status` so the two controls appear unified【F:resources/views/livewire/manage-organizations.blade.php†L60-L101】.
   - Mirror the toggle in the view page’s edit mode.
4. **Validation updates**
   - Update any calls to `getOrganizationValidationRules*` so company/TIN absence passes; adjust related tests once added.

## Contracts Module Changes
1. **Contract Type LOV**
   - Move type options to Settings: create `contract_types` list managed via new Settings tab and seed baseline types (Support, Hardware, Software…).
   - In `OrganizationContractForm.php` and `ManageContracts.php`, load types from Settings and validate using `Rule::in($types)` instead of hardcoded list【F:app/Livewire/OrganizationContractForm.php†L17-L44】【F:app/Livewire/ManageContracts.php†L19-L47】.
   - Update blade selects (`organization-contract-form.blade.php`, `manage-contracts.blade.php`) to loop over dynamic options.
2. **Drop contract value & currency**
   - Create migration removing `contract_value` and `currency` from `organization_contracts` and from related seed data【F:database/migrations/2025_01_01_000009_create_organization_contracts_table.php†L25-L32】【F:database/seeders/ClientSampleDataSeeder.php†L271-L284】.
   - Strip fields from models, Livewire components, controllers, and views: `OrganizationContract.php`, `OrganizationContractForm.php`, `ManageContracts.php`, `ManageContracts` blade, `organization-contract-form` blade, `contracts-tab` partial, `ViewTicket.php`, and `view-ticket.blade.php`【F:resources/views/livewire/partials/organization/contracts-tab.blade.php†L44-L48】【F:app/Livewire/ViewTicket.php†L80-L88】【F:resources/views/livewire/view-ticket.blade.php†L208-L211】.
3. **End date / renewal logic**
   - In form components, when `renewal_months` is entered compute `end_date = start_date + months` and render end_date as read‑only. If months empty, allow manual end_date entry (default to today). Add watcher methods to keep the two fields synced.
   - Provide one‑time backfill script to recompute `end_date` for existing rows where `renewal_months` is populated.
4. **Oracle flag & CSI number**
   - Migration: add boolean `is_oracle` (default false) and string `csi_number` nullable to `organization_contracts`.
   - Validation: `required_if:is_oracle,true` rule for `csi_number` in Livewire components and controller.
   - Blade: show `csi_number` field only when toggle is checked.
5. **Rename Terms & Conditions**
   - Rename `terms_conditions` column to `notes` via migration. Update model fillable/casts and replace textarea labels/placeholders in blades with “Notes”【F:resources/views/livewire/organization-contract-form.blade.php†L171-L179】.
6. **Cleanup**
   - Remove references from `ClientSampleDataSeeder.php` and any exports or reports consuming contract value/currency.

## Test Checklist
- Ticket create/edit only shows statuses allowed for the selected department group; forcing an unauthorized status returns 422.
- Open/In Progress/Closed appear in Settings but cannot be edited or deleted.
- Organization view shows a single Active/Inactive badge; company and TIN can be blank without validation errors; Active toggle aligns with subscription status.
- Contract forms load type options from Settings; saving persists selection.
- Contract value and currency are absent everywhere; reports and views render without errors.
- Renewal months automatically compute end date and lock the field; manual end date works when months blank.
- Toggling “Oracle” requires CSI number before save.
- All updated views retain monochrome styling and respond properly on mobile and desktop widths.
