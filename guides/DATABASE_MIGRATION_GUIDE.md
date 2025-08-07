# Database Migration Cleanup & Enhancement Guide

## Overview
This guide documents the database migration cleanup and enhancement process performed to improve the Support Portal's database structure.

## What Was Changed

### 🗑️ **Removed Migrations**
- `2025_08_04_082554_fix_model_has_roles_department_id_nullable.php` - Problematic permission fix
- `2025_08_04_082703_drop_department_id_from_model_has_roles.php` - Failed permission fix  
- `2025_07_24_122604_create_roles_table.php` - Redundant (using Spatie Permission)

### ✨ **New Enhanced Migrations**
All migrations have been recreated with standardized naming, proper indexes, and enhanced features:

#### Core Tables
1. **`2025_01_01_000001_create_organizations_table.php`**
   - Added UUID support
   - Unique constraints on email/tin_no
   - Subscription status for billing
   - Soft deletes
   - Performance indexes

2. **`2025_01_01_000002_create_departments_table.php`**  
   - Added email field for department contact
   - Sort order for custom ordering
   - Soft deletes
   - Status management

3. **`2025_01_01_000003_create_users_table.php`**
   - UUID for public-facing IDs
   - Email verification timestamp
   - Last login tracking
   - Timezone preferences
   - User preferences JSON field
   - Proper foreign key constraints
   - Soft deletes

4. **`2025_01_01_000004_create_tickets_table.php`**
   - UUID and human-readable ticket numbers
   - Enhanced status/priority enums
   - SLA tracking fields (response/resolution time)
   - Proper foreign key naming and constraints
   - Comprehensive indexing strategy

#### Supporting Tables
5. **`2025_01_01_000005_create_ticket_messages_table.php`**
   - Proper Laravel timestamps
   - Internal/system message flags
   - Metadata JSON field
   - Soft deletes

6. **`2025_01_01_000006_create_ticket_notes_table.php`**
   - Standardized boolean naming
   - Note type categorization  
   - Proper timestamps

7. **`2025_01_01_000007_create_knowledge_articles_table.php`**
   - UUID support
   - SEO-friendly slugs
   - Status workflow (draft/published/archived)
   - View/feedback tracking
   - Full-text search indexes
   - Proper audit fields

8. **`2025_01_01_000008_create_organization_contracts_table.php`**
   - Contract numbers
   - Enhanced contract types
   - Financial fields (value, currency)
   - Renewal management
   - Service level definitions

9. **`2025_01_01_000009_create_organization_hardware_table.php`**
   - Asset tag tracking
   - Enhanced hardware information
   - Warranty management
   - Maintenance scheduling
   - Custom fields support

### 🚀 **New System Tables**
10. **`2025_01_01_000010_create_attachments_table.php`** - File management
11. **`2025_01_01_000011_create_activity_logs_table.php`** - Audit trails
12. **`2025_01_01_000012_create_settings_table.php`** - Dynamic configuration
13. **`2025_01_01_000013_create_notifications_table.php`** - In-app notifications

## Enhanced Seeders

### 📊 **New Seeder Structure**
- **`SystemSeeder`** - Core system data (departments, organizations, settings)
- **`EnhancedPermissionSeeder`** - Comprehensive permissions and roles
- **`UserSeeder`** - Users with proper role assignments
- **`DatabaseSeeder`** - Orchestrates all seeders with proper dependencies

### 🔐 **Enhanced Permission System**
- 40+ granular permissions across all system areas
- Proper role-based access control
- Department-specific permissions for agents
- Future-ready permission structure

## Model Enhancements

### 🏗️ **New Model Traits**
- **`HasUuid`** - UUID generation and routing
- **`HasStatus`** - Status management (active/inactive)
- **`LogsActivity`** - Automatic activity logging

### 📋 **Benefits**
- **Consistency** - Standardized naming and structure
- **Performance** - Proper indexing strategy
- **Scalability** - UUID support, soft deletes, partitioning-ready
- **Audit Trail** - Complete activity logging
- **Flexibility** - JSON fields for custom data
- **SEO Ready** - Slugs and metadata support

## Migration Instructions

### 🔄 **Fresh Installation**
```bash
# Drop all tables and start fresh
php artisan migrate:fresh --seed

# This will:
# 1. Drop all existing tables
# 2. Run new enhanced migrations
# 3. Seed with proper data structure
```

### ⚠️ **Production Migration** (Not Recommended - Fresh install preferred)
If you must migrate existing data:

1. **Backup your database first!**
2. Export important data (users, tickets, organizations)
3. Run fresh migration
4. Import data using custom seeders

### 🧪 **Testing the New Structure**
```bash
# Run migrations
php artisan migrate:fresh --seed

# Test login credentials (all password: "password")
# admin: admin@company.com
# IT support: itsupport@company.com  
# client: client@company.com

# Verify role assignments
php artisan tinker
>>> User::find(1)->isAdmin()  // Should return true
>>> User::find(2)->hasRole('support')  // Should return true
```

## Database Schema Overview

```
organizations (with soft deletes, subscription management)
├── organization_contracts (enhanced with financials)
│   └── organization_hardware (with maintenance tracking)
└── users (with UUIDs, preferences, audit trails)
    ├── tickets (with UUIDs, SLA tracking, enhanced workflow)
    │   ├── ticket_messages (with metadata, internal flags)
    │   ├── ticket_notes (with categorization)
    │   └── attachments (polymorphic file system)
    ├── knowledge_articles (with SEO, workflow, feedback)
    ├── activity_logs (comprehensive audit trails)
    └── notifications (in-app notification system)

departments (with contacts, ordering)
settings (dynamic system configuration)
```

## Next Steps

1. **Update Models** - Add new traits and relationships
2. **Update Controllers** - Use new model features
3. **Update Views** - Support new fields and features
4. **Add File Upload** - Implement attachment system
5. **Add Activity Logs** - Show audit trails in UI
6. **Add Notifications** - Implement notification system

This enhanced database structure provides a solid foundation for future development with improved performance, maintainability, and feature capabilities.