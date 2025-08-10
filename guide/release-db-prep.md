# Database Release Preparation Guide

This guide provides a step-by-step process for deploying the Support Portal database to production.

## Overview

The database has been cleaned up and prepared for production deployment with:

- **Consolidated Migrations**: Removed problematic incremental migrations and created clean, atomic migrations
- **Production Seeders**: Baseline data only, no development/test data
- **Proper Foreign Keys**: All relationships properly constrained for data integrity
- **Idempotent Seeders**: Can be run multiple times safely using upserts

## Final Migration Set

The following migrations will be executed in dependency order:

### Core Structure (000001-000014)
- `2025_01_01_000001_create_organizations_table.php` - Client organizations
- `2025_01_01_000002_create_department_groups_table.php` - Department grouping
- `2025_01_01_000003_create_departments_table.php` - Internal departments
- `2025_01_01_000004_create_users_table.php` - System users
- `2025_01_01_000005_create_tickets_table.php` - Support tickets
- `2025_01_01_000006_create_ticket_messages_table.php` - Ticket communications
- `2025_01_01_000007_create_ticket_notes_table.php` - Internal notes
- `2025_01_01_000008_create_knowledge_articles_table.php` - Knowledge base
- `2025_01_01_000009_create_organization_contracts_table.php` - Client contracts
- `2025_01_01_000010_create_organization_hardware_table.php` - Hardware assets
- `2025_01_01_000011_create_attachments_table.php` - File attachments
- `2025_01_01_000012_create_activity_logs_table.php` - Audit trail
- `2025_01_01_000013_create_settings_table.php` - Application settings
- `2025_01_01_000014_create_notifications_table.php` - System notifications

### Scheduling System (000015-000016)
- `2025_01_01_000015_create_schedule_event_types_table.php` - Event types
- `2025_01_01_000016_create_schedules_table.php` - User schedules

### Infrastructure (000018-000025)
- `2025_01_01_000018_create_cache_table.php` - Laravel cache
- `2025_01_01_000019_create_permission_tables.php` - Spatie permissions (CONSOLIDATED)
- `2025_01_01_000020_create_sessions_table.php` - User sessions
- `2025_01_01_000021_add_performance_indexes_to_tickets_table.php` - Performance indexes
- `2025_01_01_000023_add_avatar_to_users_table.php` - User avatars
- `2025_01_01_000024_create_dashboard_widgets_table.php` - Dashboard widgets
- `2025_01_01_000025_create_user_widget_settings_table.php` - User widget preferences

## Production Seeder Classes

### Primary Seeder
- **`DatabaseSeeder`** - Main entry point for seeding all baseline data

### Component Seeders
- **`RolePermissionSeeder`** - Creates roles and permissions from modules config
- **`BasicDataSeeder`** - Creates organization, department groups, and departments
- **`UserSeeder`** - Creates users with proper role assignments  
- **`ScheduleEventTypeSeeder`** - Creates schedule event types (PR, PO, HAS, etc.)
- **`DashboardWidgetSeeder`** - Creates dashboard widget catalog
- **`UserWidgetSettingsSeeder`** - Creates default user widget settings
- **`ApplicationSettingsSeeder`** - Creates application settings (weekend days, default org, etc.)

## Deployment Commands

### For Fresh Server Deployment

```bash
# 1. Navigate to project directory
cd /path/to/support-portal

# 2. Configure environment
cp .env.example .env
# Edit .env with your database credentials and app settings

# 3. Install dependencies
composer install --no-dev --optimize-autoloader

# 4. Generate application key
php artisan key:generate

# 5. Run migrations (creates all tables and indexes)
php artisan migrate --force

# 6. Seed baseline data (creates roles, permissions, initial admin, etc.)
php artisan db:seed --force

# 7. Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Verification Steps

After deployment, verify the schema matches expectations:

```bash
# Check migration status
php artisan migrate:status

# Verify critical tables exist and have data
php artisan tinker
>>> App\Models\User::count(); // Should be 6+ (super admin + department managers)
>>> Spatie\Permission\Models\Role::count(); // Should be 3 (admin, support, client)
>>> Spatie\Permission\Models\Permission::count(); // Should be ~40+ permissions
>>> App\Models\Organization::count(); // Should be 1 (Hospitality Technology)
>>> App\Models\Department::count(); // Should be 14 (various PMS, POS, MC, Hardware, Admin depts)
>>> App\Models\DepartmentGroup::count(); // Should be 5 (Admin, PMS, POS, MC, Hardware)
>>> App\Models\ScheduleEventType::count(); // Should be 10+ (PR, PO, HAS, etc.)
>>> App\Models\Setting::count(); // Should be 2+ (weekend_days, default_organization)
>>> exit
```

## Critical Tables Checklist

Verify these tables exist and have the correct structure:

### Core Business Tables
- [ ] `organizations` - Client organizations with TIN, contact info
- [ ] `users` - System users with UUID, department assignments
- [ ] `departments` - Internal departments linked to groups
- [ ] `department_groups` - Department grouping with colors
- [ ] `tickets` - Support tickets with proper status/priority enums
- [ ] `ticket_messages` - Ticket communications
- [ ] `ticket_notes` - Internal notes
- [ ] `contracts` - Organization contracts
- [ ] `hardware` - Hardware asset tracking

### Spatie Permission Tables
- [ ] `permissions` - All module permissions
- [ ] `roles` - Admin, support, client roles with descriptions
- [ ] `model_has_permissions` - Direct user permissions
- [ ] `model_has_roles` - User role assignments
- [ ] `role_has_permissions` - Role permission assignments

### Scheduling System
- [ ] `schedule_event_types` - Holiday, maintenance, training types
- [ ] `schedules` - User schedule entries with date ranges

### Infrastructure Tables
- [ ] `settings` - Application configuration
- [ ] `notifications` - System notifications
- [ ] `activity_logs` - Audit trail
- [ ] `attachments` - File upload tracking
- [ ] `sessions` - User sessions
- [ ] `cache` - Application cache

## Default Credentials

**Super Admin Account:**
- Email: `superadmin@hospitalitytechnology.com.mv`
- Username: `superadmin`
- Password: `password`

**Department Manager Accounts:**
- Admin Manager: `admin@hospitalitytechnology.com.mv` / `password`
- PMS Manager: `pms@hospitalitytechnology.com.mv` / `password`
- POS Manager: `pos@hospitalitytechnology.com.mv` / `password`
- MC Manager: `mc@hospitalitytechnology.com.mv` / `password`
- Hardware Manager: `hardware@hospitalitytechnology.com.mv` / `password`

⚠️ **CRITICAL**: Change all default passwords immediately after first login!

## Environment-Specific Seeding

### Production Seeding (Default)
```bash
php artisan db:seed
# Uses DatabaseSeeder - creates roles, permissions, departments, users, widgets, and settings
```

### Development Seeding (Alternative)
```bash
# For local development, you can run specific seeders
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=ApplicationSettingsSeeder
# etc.
```

### Demo/Staging Seeding
For demo environments, you can run additional seeders after the production baseline:

```bash
php artisan db:seed
php artisan db:seed --class=DemoTicketSeeder  # If you create one
php artisan db:seed --class=SampleDataSeeder  # If you create one
```

## Rollback Strategy

If deployment fails or issues are discovered:

### Emergency Rollback
```bash
# Rollback all migrations (DESTRUCTIVE)
php artisan migrate:rollback --step=25

# Or rollback to specific migration
php artisan migrate:rollback --batch=1
```

### Safe Recovery
1. Take database backup before any rollback
2. Use specific migration rollbacks for targeted fixes
3. Re-run seeders if data corruption occurs

## Post-Deployment Configuration

After successful deployment:

1. **Update Organization Details**
   - Login as admin
   - Navigate to Organizations → Edit Default Organization
   - Update with actual company details

2. **Configure Departments**
   - Review and modify department groups
   - Add/remove departments as needed
   - Update department email addresses

3. **Create Additional Users**
   - Create real user accounts
   - Assign appropriate roles
   - Disable or delete the default admin if needed

4. **Review Settings**
   - Check application settings in admin panel
   - Configure pagination, notifications, etc.

## Deprecated Files

The following migration files have been moved to `database/migrations/deprecated/` and should not be used:

- `2025_01_01_000017_add_description_to_roles_table.php` - Merged into permission tables migration
- `2025_01_01_000019_create_permission_tables.php` - Replaced with cleaner consolidated version
- `2025_01_01_000022_cleanup_schedules_table_structure.php` - No longer needed (fixed at source)
- `2025_01_01_000016_create_schedules_table.php` - Replaced with corrected version

These files are preserved for reference but should not be included in fresh deployments.

## Troubleshooting

### Common Issues

1. **Permission Cache Issues**
   ```bash
   php artisan permission:cache-reset
   ```

2. **Foreign Key Constraint Errors**
   - Check that all referenced tables exist
   - Verify foreign key relationships in migrations

3. **Seeder Failures**
   - Ensure seeders run in correct dependency order (roles before users, organizations before users)
   - Verify modules.php configuration file exists and is properly formatted
   - Check that department groups are created before departments and users

4. **Migration Order Issues**
   - All migrations follow dependency order (referenced tables created first)
   - Use `php artisan migrate:status` to check current state

### Support

If issues occur during deployment, check:

1. Laravel logs: `storage/logs/laravel.log`
2. Database logs for constraint violations
3. PHP error logs for configuration issues

## Final Notes

- All migrations are designed to be idempotent (safe to run multiple times)
- Seeders use `updateOrCreate()` for idempotency
- Foreign key constraints ensure data integrity
- Soft deletes preserve audit trails
- All critical fields have proper indexes for performance

This completes the database preparation for production deployment. The schema is now consistent, the seed data is production-ready, and the deployment process is documented.