## Role Mismatches

- File Path: README.md
  - Line Number: 72, 110, 127, 138, 177, 203, 233, 244, 293, 433
  - Found: Agents, Super Admin, Finance, Human Resource, Project Manage, Sales
  - Suggested Replacement: support, admin, admin, admin, admin, admin

- File Path: plan.txt
  - Line Number: 16, 33
  - Found: Super Admin, Finance, Human Resource, Project Manage, Sales; Super Admin
  - Suggested Replacement: admin; admin

- File Path: app/Livewire/Dashboard.php
  - Line Number: 47, 88
  - Found: Agent
  - Suggested Replacement: support

- File Path: app/Livewire/ScheduleCalendar.php
  - Line Number: 33, 35, 96, 181
  - Found: Super Admin, Agent
  - Suggested Replacement: admin, support

- File Path: app/Services/PermissionService.php
  - Line Number: 144, 152
  - Found: Super Admin
  - Suggested Replacement: admin

- File Path: app/Providers/AuthServiceProvider.php
  - Line Number: 33, 37, 41
  - Found: Super Admin
  - Suggested Replacement: admin

- File Path: resources/views/components/navigation.blade.php
  - Line Number: 46, 193
  - Found: Super Admin
  - Suggested Replacement: admin

- File Path: resources/views/livewire/admin/manage-users.blade.php
  - Line Number: 126, 128, 316
  - Found: Super Admin, Agent
  - Suggested Replacement: admin, support

- File Path: resources/views/livewire/admin/manage-roles.blade.php
  - Line Number: 78, 79, 83, 92, 119, 175, 199
  - Found: Super Admin, Agent
  - Suggested Replacement: admin, support

- File Path: resources/views/livewire/partials/organization/users-tab.blade.php
  - Line Number: 35
  - Found: Agent, Super Admin
  - Suggested Replacement: support, admin

- File Path: resources/views/livewire/manage-users.blade.php
  - Line Number: 45
  - Found: Agent, Super Admin
  - Suggested Replacement: support, admin

- File Path: database/seeders/DepartmentGroupSeeder.php
  - Line Number: 24
  - Found: Super Admin
  - Suggested Replacement: admin

- File Path: database/seeders/ScheduleEventTypeSeeder.php
  - Line Number: 19, 23
  - Found: Super Admin
  - Suggested Replacement: admin

- File Path: database/seeders/BasicDataSeeder.php
  - Line Number: 38, 40
  - Found: Super Admin, Agent
  - Suggested Replacement: admin, support

- File Path: database/seeders/DepartmentSeeder.php
  - Line Number: 34
  - Found: Super Admin
  - Suggested Replacement: admin

- File Path: database/seeders/DatabaseSeeder.php
  - Line Number: 27
  - Found: Super Admin
  - Suggested Replacement: admin

- File Path: database/seeders/UserSeeder.php
  - Line Number: 47, 51, 62
  - Found: Super Admin
  - Suggested Replacement: admin

- File Path: resources/views/livewire/schedule-calendar.blade.php
  - Line Number: 36, 172, 210, 254
  - Found: Super Admin
  - Suggested Replacement: admin

- File Path: config/modules.php
  - Line Number: 195, 220
  - Found: Super Admin, Agent
  - Suggested Replacement: admin, support

- File Path: guides/PERMISSION_DEPLOYMENT.md
  - Line Number: 150, 152, 234, 237
  - Found: Super Admin, Agent
  - Suggested Replacement: admin, support

- File Path: guides/PERMISSION_ROADMAP.md
  - Line Number: 37
  - Found: Super Admin
  - Suggested Replacement: admin

- File Path: guides/USER_ROLE_MANAGEMENT.md
  - Line Number: 14, 107, 108, 122, 138, 143
  - Found: Agent
  - Suggested Replacement: support

- File Path: guides/TICKET_RECOMENDATIONS.md
  - Line Number: 18, 61, 64
  - Found: Agent
  - Suggested Replacement: support

- File Path: guides/DATABASE_MIGRATION_GUIDE.md
  - Line Number: 143, 149
  - Found: Agent
  - Suggested Replacement: support

- File Path: guides/SCHEDULE_ANALYSIS.md
  - Line Number: 12
  - Found: Super Admins
  - Suggested Replacement: admin

- File Path: guides/SCHEDULE_MODULE_GUIDE.md
  - Line Number: 77
  - Found: Agents
  - Suggested Replacement: support

---

## Permission Mismatches

- File Path: app/Console/Commands/TestUserRole.php
  - Line Number: 42–43
  - Found: admin.access
  - Suggested Replacement: dashboard.access

- File Path: app/Livewire/ViewTicket.php
  - Line Number: 59, 132
  - Found: tickets.view, tickets.edit
  - Suggested Replacement: tickets.read, tickets.update

- File Path: app/Livewire/ViewOrganization.php
  - Line Number: 34, 57, 162
  - Found: organizations.view, organizations.edit
  - Suggested Replacement: organizations.read, organizations.update

- File Path: app/Livewire/ManageOrganizations.php
  - Line Number: 39, 53
  - Found: organizations.view, organizations.edit
  - Suggested Replacement: organizations.read, organizations.update

- File Path: app/Livewire/Admin/ManageRoles.php
  - Line Number: 67
  - Found: users.edit
  - Suggested Replacement: users.update

- File Path: resources/views/livewire/partials/organization/contracts-tab.blade.php
  - Line Number: 67
  - Found: contracts.view
  - Suggested Replacement: contracts.read

- File Path: resources/views/livewire/partials/organization/hardware-tab.blade.php
  - Line Number: 67
  - Found: hardware.view
  - Suggested Replacement: hardware.read

- File Path: resources/views/livewire/partials/organization/users-tab.blade.php
  - Line Number: 90
  - Found: users.view
  - Suggested Replacement: users.read

- File Path: resources/views/livewire/partials/organization/tickets-tab.blade.php
  - Line Number: 68
  - Found: tickets.view
  - Suggested Replacement: tickets.read

- File Path: guides/USER_ROLE_MANAGEMENT.md
  - Line Number: 77, 83, 89
  - Found: tickets.view, users.view, organizations.view
  - Suggested Replacement: tickets.read, users.read, organizations.read

- File Path: config/modules.php
  - Line Number: 203, 205, 206, 207, 208, 209, 210, 211, 212, 214, 215
  - Found: users.*, departments.*, tickets.*, contracts.*, hardware.*, settings.*, notes.*, messages.*, articles.*, schedules.*, schedule-event-types.*
  - Suggested Replacement: Expand to explicit permissions per module
