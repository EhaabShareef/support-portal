# Permission System Deployment Guide

This document provides step-by-step instructions for deploying permission system changes and maintaining the role/permission structure.

## 📋 Prerequisites

Before deploying permission changes, ensure you have:
- Database backup (recommended)
- Admin access to the application
- Command line access to run artisan commands

## 🚀 Deployment Steps

### 1. Standard Deployment (Recommended)

For regular deployments when permissions have been updated:

```bash
# Step 1: Run database migrations (if any)
php artisan migrate

# Step 2: Sync permissions and roles from config
php artisan permissions:sync

# Step 3: Clear application caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 2. Fresh Installation or Major Updates

When setting up a new environment or after major permission restructuring:

```bash
# Step 1: Run all migrations
php artisan migrate

# Step 2: Seed permissions and roles (fresh)
php artisan db:seed --class=RolePermissionSeeder

# Step 3: Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 3. Development/Testing Deployments

For development environments where you want to reset permissions:

```bash
# WARNING: This will delete existing permissions and roles
php artisan permissions:sync --fresh

# Clear caches
php artisan cache:clear
```

## 🔄 Permission Management Commands

### Sync Command Options

```bash
# Standard sync (updates existing, adds new)
php artisan permissions:sync

# Fresh sync (deletes and recreates all)
php artisan permissions:sync --fresh

# Dry run (shows what would be done)
php artisan permissions:sync --dry-run

# Fresh dry run
php artisan permissions:sync --fresh --dry-run
```

### Other Useful Commands

```bash
# Check current permission statistics
php artisan permissions:sync --dry-run

# Clear permission cache manually
php artisan cache:forget spatie.permission.cache

# List all available artisan commands
php artisan list
```

## 📝 Configuration Files

### Key Files to Maintain

1. **`config/modules.php`** - Central definition of all modules and permissions
2. **`database/seeders/RolePermissionSeeder.php`** - Seeder that uses modules config
3. **`app/Policies/*.php`** - Policy files for model-specific authorization
4. **`app/Services/PermissionService.php`** - Helper service for permission checks

### Module Configuration Structure

When adding new modules or permissions, update `config/modules.php`:

```php
// Add new module
'new-module' => [
    'label' => 'New Module',
    'description' => 'Description of the new module',
    'actions' => ['create', 'read', 'update', 'delete'],
    'icon' => 'heroicon-o-cube',
],

// Add to appropriate group
'groups' => [
    'existing-group' => [
        // ... existing config
        'modules' => ['existing-modules', 'new-module'],
    ],
],
```

## 🧪 Testing Permission Changes

### Pre-Deployment Testing

1. **Run dry-run to see changes:**
   ```bash
   php artisan permissions:sync --dry-run
   ```

2. **Test in development environment:**
   ```bash
   # Create test database backup
   php artisan db:seed --class=RolePermissionSeeder
   ```

3. **Verify role assignments:**
   - Log in as different role types
   - Test access to protected routes
   - Verify UI elements show/hide correctly

### Post-Deployment Verification

1. **Check permission counts:**
   ```bash
   php artisan permissions:sync --dry-run
   ```

2. **Test critical user flows:**
   - Super Admin: All access
   - Admin: Management functions
   - Agent: Department-limited access
   - Client: Basic access only

3. **Verify database integrity:**
   ```sql
   -- Check for orphaned permissions
   SELECT * FROM permissions WHERE name NOT LIKE '%.*';
   
   -- Check role distribution
   SELECT r.name, COUNT(mu.user_id) as user_count 
   FROM roles r 
   LEFT JOIN model_has_roles mu ON r.id = mu.role_id 
   GROUP BY r.id, r.name;
   ```

## 🔒 Security Considerations

### During Deployment

1. **Backup before major changes:**
   ```bash
   mysqldump -u username -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Use transactions for seeding:**
   The seeder automatically uses database transactions for safety.

3. **Test in staging first:**
   Always test permission changes in a staging environment.

### After Deployment

1. **Audit user access:**
   - Review who has admin roles
   - Check for any unauthorized permissions
   - Verify role assignments are correct

2. **Monitor logs:**
   - Check for authorization errors
   - Monitor access patterns
   - Review any permission-related exceptions

## 🚨 Troubleshooting

### Common Issues

1. **Permission cache not clearing:**
   ```bash
   php artisan permission:cache-reset
   # or
   php artisan cache:clear
   ```

2. **Users can't access areas they should:**
   ```bash
   # Check user's roles and permissions
   php artisan tinker
   >>> $user = App\Models\User::find(1);
   >>> $user->roles;
   >>> $user->getAllPermissions();
   ```

3. **Seeder fails:**
   - Check database connection
   - Verify config/modules.php syntax
   - Check for duplicate permissions

4. **404 errors on protected routes:**
   - Verify middleware configuration
   - Check route definitions
   - Ensure policies are registered

### Recovery Procedures

1. **If permissions are corrupted:**
   ```bash
   # Reset permissions completely
   php artisan permissions:sync --fresh
   ```

2. **If users lose access:**
   ```bash
   # Manually assign Super Admin role
   php artisan tinker
   >>> $user = App\Models\User::find(1);
   >>> $user->assignRole('Super Admin');
   ```

3. **If seeder fails mid-process:**
   Database transactions will automatically rollback, but verify:
   ```bash
   php artisan permissions:sync --dry-run
   ```

## 📊 Monitoring and Maintenance

### Regular Maintenance Tasks

1. **Weekly:**
   - Review user role assignments
   - Check for any permission-related errors in logs

2. **Monthly:**
   - Audit admin users
   - Review and clean up unused permissions
   - Update documentation if modules change

3. **Before major releases:**
   - Run full permission sync dry-run
   - Test all user roles in staging
   - Document any permission changes

### Performance Monitoring

- Permission caching is enabled by default
- Monitor query performance on permission checks
- Consider role-based caching for heavy permission operations

## 🔧 Advanced Configuration

### Custom Permission Patterns

You can extend the role templates in `config/modules.php` with complex patterns:

```php
'permissions' => [
    'users.*',           // All user permissions
    'tickets.read',      // Specific permission
    'contracts.create',  // Another specific permission
    // Patterns are resolved by RolePermissionSeeder
],
```

### Environment-Specific Configurations

Create environment-specific overrides:

```php
// config/modules.production.php
return array_merge(
    require __DIR__ . '/modules.php',
    [
        'role_templates' => [
            // Production-specific role modifications
        ]
    ]
);
```

## 📚 Additional Resources

- [Laravel Permission Package Documentation](https://spatie.be/docs/laravel-permission/)
- [Laravel Policy Documentation](https://laravel.com/docs/authorization#policies)
- [Application Permission Roadmap](./PERMISSION_ROADMAP.md)

---

**⚠️ Important:** Always test permission changes thoroughly before deploying to production. Permission errors can lock users out of critical application areas.