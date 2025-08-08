# Reports Module Guide

## Overview
The Reports module provides administrators with a central location to review operational and performance metrics across the support portal. Reports are strictly read‑only and pull data directly from the database via optimized queries with bound parameters. Each report is delivered as an isolated Livewire component and accessed through a unified dashboard under the existing admin area (`/admin/reports`).

### Data Sources
Reports leverage the following models and relationships:
- Tickets, including status, priority and response timing fields.
- Organizations with related contracts, hardware and users.
- Contracts and associated hardware assets.
- Hardware inventory with warranty and maintenance dates.
- Users, departments and department groups.
- Schedules for workforce planning.

## Recommended Reports
Reports are grouped by domain. Each item lists its purpose, useful filters and expected output.

### 1. Ticket & Support Performance
#### 1.1 Ticket Volume & Status Trends
- **Description:** Shows ticket counts grouped by status over a selected period to track workload and backlog trends.
- **Why:** Helps admins monitor operational load and spot spikes requiring additional resources.
- **Filters:** Date range (created_at), organization, department, department group, assigned agent, ticket type, priority, status.
- **Output:** Table and line/bar chart of ticket counts per status per period.

#### 1.2 Response & Resolution Time Analysis
- **Description:** Aggregates average `first_response_at` and `resolved_at` durations to evaluate SLA compliance.
- **Why:** Highlights performance issues and areas needing process improvement.
- **Filters:** Date range (created_at/resolved_at), organization, department, department group, assigned agent, status.
- **Output:** Average and median response_time_minutes and resolution_time_minutes with breakdowns by department or agent.

#### 1.3 Agent Workload Distribution
- **Description:** Counts tickets assigned to each agent and their open/closed status.
- **Why:** Ensures equitable workload and identifies capacity issues.
- **Filters:** Date range, organization, department, department group, ticket status, priority.
- **Output:** Table of agents with counts of assigned, resolved and remaining tickets.

#### 1.4 Ticket Type & Priority Breakdown
- **Description:** Displays distribution of tickets by `type` and `priority` to identify common issue categories.
- **Why:** Supports resource planning and trend analysis for recurring problems.
- **Filters:** Date range, organization, department, department group, assigned agent.
- **Output:** Pivot‑style table or stacked chart showing counts per type and priority.

#### 1.5 Aging & Overdue Tickets
- **Description:** Lists open tickets grouped by age buckets (e.g., 0‑7, 8‑14, 15+ days).
- **Why:** Flags stalled requests requiring escalation.
- **Filters:** Date range (created_at), organization, department, department group, assigned agent, priority.
- **Output:** Table of tickets with age bucket and days open; summary counts per bucket.

### 2. Organization & Contract Oversight
#### 2.1 Organization Summary
- **Description:** High‑level snapshot per organization including number of active users, open tickets, active contracts and hardware assets.
- **Why:** Provides quick visibility into client health and engagement.
- **Filters:** Organization activity status, subscription_status.
- **Output:** Tabular summary per organization with counts and status indicators.

#### 2.2 Contract Renewal Forecast
- **Description:** Lists contracts approaching `end_date` within a chosen horizon.
- **Why:** Enables proactive renewal discussions and avoids service interruptions.
- **Filters:** End date range, status, contract type, organization, department.
- **Output:** Table of contracts with contract_number, organization, department, end_date and days to expiry.

#### 2.3 Contract Value by Organization
- **Description:** Aggregates `contract_value` totals grouped by organization and optionally department.
- **Why:** Supports revenue tracking and forecasting.
- **Filters:** Date range (start_date/end_date), status, type, organization, department.
- **Output:** Summary table with total and average contract_value per group; optional bar chart.

### 3. Hardware & Asset Management
#### 3.1 Hardware Inventory Snapshot
- **Description:** Inventory list of hardware items including `hardware_type`, `status`, location and related contract.
- **Why:** Provides visibility into asset allocation and lifecycle state.
- **Filters:** Organization, contract, hardware_type, status, warranty_expiration range, purchase_date range.
- **Output:** Paginated table with asset_tag, type, status, organization, contract, warranty_expiration and location.

#### 3.2 Warranty & Maintenance Schedule
- **Description:** Highlights assets with upcoming `warranty_expiration` or `next_maintenance` dates.
- **Why:** Helps plan maintenance and replacement cycles to reduce downtime.
- **Filters:** Organization, hardware_type, date range (warranty_expiration/next_maintenance).
- **Output:** Table grouped by date with asset details and days until action.

#### 3.3 Hardware Allocation by Contract
- **Description:** Counts hardware items linked to each contract or organization.
- **Why:** Validates contract deliverables and asset distribution.
- **Filters:** Organization, contract, hardware_type, status.
- **Output:** Summary table of contract_number with count and types of associated hardware.

### 4. User & Department Activity
#### 4.1 User Account Status & Access
- **Description:** Lists users with role, department, organization, `is_active` flag and `last_login_at`.
- **Why:** Supports security audits and onboarding/offboarding processes.
- **Filters:** Organization, department, department group, role, active/inactive, date range (last_login_at).
- **Output:** Paginated table with user details and activity indicators.

#### 4.2 Department Performance
- **Description:** Aggregates ticket counts and average response/resolution times per department or department group.
- **Why:** Measures effectiveness of support teams and informs resource allocation.
- **Filters:** Date range, department, department group, ticket status, priority.
- **Output:** Summary table and bar chart per department with counts and timing metrics.

#### 4.3 Agent Productivity
- **Description:** For each agent, shows tickets created vs. resolved along with average resolution time.
- **Why:** Enables performance reviews and recognition of top performers.
- **Filters:** Date range, department, department group, organization.
- **Output:** Table with counts of created/assigned/resolved tickets and average resolution_time_minutes.

### 5. Schedule & Workforce Planning
#### 5.1 Schedule Coverage
- **Description:** Calendar‑style view of schedule entries across departments or groups within a date range.
- **Why:** Ensures adequate staffing and identifies coverage gaps.
- **Filters:** Date range, department, department group, user, event type.
- **Output:** Calendar grid or table showing user, event_type and date range.

#### 5.2 User Schedule Summary
- **Description:** Lists schedule events per user within a period for planning and reporting.
- **Why:** Tracks workload and validates timesheet or attendance data.
- **Filters:** Date range, department, department group, user, event type.
- **Output:** Paginated table grouped by user with event details and total days.

## Implementation Plan
1. **Routing & Dashboard**
   - Add `/admin/reports` route within the existing admin group.
   - Create a `ReportsDashboard` Livewire component serving as entry point with links to individual reports.

2. **Livewire Components**
   - Create components under `App\Livewire\Reports` (e.g., `TicketStatusReport`, `ContractRenewalReport`).
   - Each component:
     - Declares public filter properties (e.g., `$startDate`, `$endDate`, `$departmentId`).
     - Uses `withPagination` for large datasets.
     - Builds queries using `when()` clauses and eager loading to honor filters while avoiding N+1 issues.
     - Renders a Blade view under `resources/views/livewire/reports`.

3. **Query Logic**
   - Use indexed columns (`status`, `priority`, `organization_id`, `department_id`, date fields) for filtering.
   - Apply `selectRaw` or aggregate functions for summaries (counts, averages).
   - Bind parameters through Eloquent/Query Builder to prevent injection.

4. **UI & Filters**
   - Provide a consistent filter bar with date pickers, select boxes for organizations/departments/agents, and status/type dropdowns.
   - Persist filter state in the query string for shareable URLs.
   - Display results in responsive tables; use charts (e.g., Chart.js) for trend visualization.

5. **Exporting**
   - Add optional `export()` methods on components to generate CSV or Excel using a package such as Laravel Excel.
   - Exports honor current filters and provide column headings matching on‑screen data.

6. **Authorization**
   - Gate all report routes/components to `Admin` role using existing middleware.

7. **Testing**
   - Add feature tests ensuring each component applies filters correctly and respects authorization.

## Layout, Pagination & Responsiveness
- Use the existing Tailwind utility classes and card layout to match the portal style.
- Dashboard lists reports by category; selecting a report loads it within the same layout.
- Tables are paginated (default 25 rows) with responsive stacking on small screens.
- Charts resize using responsive containers.
- Export buttons are placed above the table and disabled when no data is available.

## Notes
- Reports exclude audit logs and bug‑tracking data; activity logs remain separate.
- All queries should be read‑only and avoid heavy joins without appropriate indexes.
