# Seeder & Migration Audit

## Conflicts and Mismatches

### Missing DepartmentGroupSeeder
**File Path:** `database/seeders/DatabaseSeeder.php`

**Line Number(s):** 19-27

**Issue:** `DatabaseSeeder` references `DepartmentGroupSeeder`, but no corresponding seeder exists in the repository.【F:database/seeders/DatabaseSeeder.php†L19-L27】

**Impact:** Department groups may never be seeded, causing `DepartmentSeeder` and `UserSeeder` to fail or create orphaned records.

**Suggested Fix:** Add a `DepartmentGroupSeeder` or remove the reference and consolidate group creation elsewhere.

### Dashboard widgets missing `key` field
**File Path:**
- `database/migrations/2025_01_01_000024_create_dashboard_widgets_table.php`
- `database/seeders/UserWidgetSettingsSeeder.php`

**Line Number(s):**
- 14-26【F:database/migrations/2025_01_01_000024_create_dashboard_widgets_table.php†L14-L26】
- 20-24, 73-105【F:database/seeders/UserWidgetSettingsSeeder.php†L20-L24】【F:database/seeders/UserWidgetSettingsSeeder.php†L73-L105】

**Issue:** `UserWidgetSettingsSeeder` expects a `key` column and specific widget keys, but `dashboard_widgets` table defines no such column.

**Impact:** Widget settings cannot be linked to widgets, resulting in empty or failing seeder runs.

**Suggested Fix:** Introduce a unique `key` column and seed matching values, or refactor the seeder to use existing identifiers.

### Schedule event types seeder uses undefined `label`
**File Path:**
- `database/migrations/2025_01_01_000015_create_schedule_event_types_table.php`
- `database/seeders/ScheduleEventTypeSeeder.php`

**Line Number(s):**
- 14-22【F:database/migrations/2025_01_01_000015_create_schedule_event_types_table.php†L14-L22】
- 27-56【F:database/seeders/ScheduleEventTypeSeeder.php†L27-L56】

**Issue:** Seeder inserts a `label` field that does not exist in the migration schema.

**Impact:** Seeding fails when attempting to write to a non-existent column.

**Suggested Fix:** Add a `label` column to the migration or change the seeder to use `description` or `name`.

### User factory omits required fields
**File Path:**
- `database/migrations/2025_01_01_000004_create_users_table.php`
- `database/factories/UserFactory.php`

**Line Number(s):**
- 14-19【F:database/migrations/2025_01_01_000004_create_users_table.php†L14-L19】
- 24-33【F:database/factories/UserFactory.php†L24-L33】

**Issue:** The users table requires `uuid` and `username`, but the factory does not populate them.

**Impact:** Factory-generated users violate NOT NULL and unique constraints in tests or seeds.

**Suggested Fix:** Update the factory to generate `uuid` and unique `username` values.

### Duplicate performance indexes
**File Path:**
- `database/migrations/2025_01_01_000005_create_tickets_table.php`
- `database/migrations/2025_01_01_000021_add_performance_indexes_to_tickets_table.php`
- `database/migrations/2025_01_01_000006_create_ticket_messages_table.php`
- `database/migrations/2025_01_01_000007_create_ticket_notes_table.php`

**Line Number(s):**
- 74-80【F:database/migrations/2025_01_01_000005_create_tickets_table.php†L74-L80】
- 14-41, 43-60【F:database/migrations/2025_01_01_000021_add_performance_indexes_to_tickets_table.php†L14-L41】【F:database/migrations/2025_01_01_000021_add_performance_indexes_to_tickets_table.php†L43-L60】
- 32-35【F:database/migrations/2025_01_01_000006_create_ticket_messages_table.php†L32-L35】
- 32-35【F:database/migrations/2025_01_01_000007_create_ticket_notes_table.php†L32-L35】

**Issue:** Performance migration adds indexes already present on tickets, ticket messages, and ticket notes tables.

**Impact:** Redundant indexes waste disk space and may cause migration failures when duplicate indexes are created.

**Suggested Fix:** Remove duplicate index definitions or drop existing indexes before adding renamed versions.

### DepartmentSeeder depends on undefined groups
**File Path:** `database/seeders/DepartmentSeeder.php`

**Line Number(s):** 22-30【F:database/seeders/DepartmentSeeder.php†L22-L30】

**Issue:** Seeder queries department groups `BO` and `Email`, which are not created by any provided seeder.

**Impact:** Seeding may fail with null references or create departments without valid group links.

**Suggested Fix:** Implement a `DepartmentGroupSeeder` that includes all referenced groups or adjust the department definitions.

### BasicDataSeeder overlap
**File Path:** `database/seeders/BasicDataSeeder.php`

**Line Number(s):** 48-206【F:database/seeders/BasicDataSeeder.php†L48-L206】

**Issue:** Seeds organizations, department groups, and departments that are also handled by `UserSeeder` and `DepartmentSeeder`.

**Impact:** Running multiple seeders can produce conflicting or duplicate baseline data.

**Suggested Fix:** Consolidate baseline seeding into dedicated seeders and remove or disable `BasicDataSeeder`.

### UserWidgetSettingsSeeder duplicate risk
**File Path:**
- `database/seeders/UserWidgetSettingsSeeder.php`
- `database/migrations/2025_01_01_000025_create_user_widget_settings_table.php`

**Line Number(s):**
- 53-61【F:database/seeders/UserWidgetSettingsSeeder.php†L53-L61】
- 14-25【F:database/migrations/2025_01_01_000025_create_user_widget_settings_table.php†L14-L25】

**Issue:** Seeder uses `create` while the table enforces a unique constraint on `user_id` and `widget_id`.

**Impact:** Re-running the seeder results in duplicate key violations.

**Suggested Fix:** Use `updateOrCreate` or `upsert` keyed on `user_id` and `widget_id`.

### Non-idempotent seeder operations
**File Paths & Line Numbers:**
- `database/seeders/RolePermissionSeeder.php` 45-67【F:database/seeders/RolePermissionSeeder.php†L45-L67】
- `database/seeders/DepartmentSeeder.php` 19-20, 223-225【F:database/seeders/DepartmentSeeder.php†L19-L20】【F:database/seeders/DepartmentSeeder.php†L223-L225】
- `database/seeders/UserSeeder.php` 23-24【F:database/seeders/UserSeeder.php†L23-L24】
- `database/seeders/DashboardWidgetSeeder.php` 18-21【F:database/seeders/DashboardWidgetSeeder.php†L18-L21】
- `database/seeders/ScheduleEventTypeSeeder.php` 48-56【F:database/seeders/ScheduleEventTypeSeeder.php†L48-L56】

**Issue:** These seeders delete existing data or use plain `create`, making them unsafe to run multiple times.

**Impact:** Production seeding could wipe important data or fail due to duplicates.

**Suggested Fix:** Replace destructive operations with `updateOrCreate`/`upsert` patterns and avoid mass deletes.

## Duplicate Data Risks
- **Departments:** Both `BasicDataSeeder` and `DepartmentSeeder` populate the `departments` table, leading to duplicates or inconsistent names.
- **Organizations:** `BasicDataSeeder` and `UserSeeder` each create a "Hospitality Technology" organization, risking conflicting records.
- **Dashboard Widgets:** `UserWidgetSettingsSeeder` expects widget keys that `DashboardWidgetSeeder` does not define, causing mismatched settings.

## Recommended Removals
- `database/migrations/deprecated/2025_01_01_000016_create_schedules_table.php`
- `database/migrations/deprecated/2025_01_01_000017_add_description_to_roles_table.php`
- `database/migrations/deprecated/2025_01_01_000019_create_permission_tables.php`
- `database/migrations/deprecated/2025_01_01_000022_cleanup_schedules_table_structure.php`
- `database/seeders/BasicDataSeeder.php`

## Fix Suggestions
- Introduce a dedicated `DepartmentGroupSeeder` and update `DatabaseSeeder` accordingly.
- Add a `key` column to `dashboard_widgets` or adjust `UserWidgetSettingsSeeder` to use existing identifiers.
- Align `ScheduleEventTypeSeeder` with the table schema by adding a `label` column or reusing `description`.
- Update `UserFactory` to populate `uuid` and `username` fields.
- Remove redundant indexes from `add_performance_indexes_to_tickets_table` or drop originals before adding new ones.
- Make all seeders idempotent using `updateOrCreate` or `upsert` and avoid destructive deletes.
