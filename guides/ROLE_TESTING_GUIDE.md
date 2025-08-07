# Role-Based Access Control Testing Guide

This guide explains how to use the automated testing tools for validating role-based access control in the Support Portal application.

## 🛠️ Available Test Tools

### 1. Quick Test (`test-role-access.php`)
A lightweight test for quick validation during development.

```bash
php test-role-access.php
```

**Features:**
- Tests existing users (no setup required)
- Quick navigation and route access validation
- Color-coded output
- Perfect for daily development checks

### 2. Enhanced Test Suite (`run-access-tests.php`)
Comprehensive testing with configuration-driven approach.

```bash
# Basic test
php run-access-tests.php

# Detailed output
php run-access-tests.php --verbose

# JSON output for CI/CD
php run-access-tests.php --json

# Save detailed report
php run-access-tests.php --save
```

**Features:**
- Configuration-driven testing
- Detailed reporting and analytics
- JSON export for automation
- Extensible for new features
- Critical path validation

### 3. Full Test Suite (`tests/RoleAccessTest.php`)
Complete test suite with user creation and cleanup.

```bash
php tests/RoleAccessTest.php
```

**Features:**
- Creates temporary test users
- Comprehensive blade template parsing
- Route middleware validation
- Detailed access matrix generation
- Optional cleanup after testing

## 📋 Configuration

### Updating Test Configuration

When adding new features, update `config/access-test.php`:

```php
// Add new navigation elements
'navigation_elements' => [
    [
        'name' => 'New Feature',
        'route' => 'feature.index',
        'role_requirement' => 'admin',
        'permission_requirement' => 'feature.read',
        'description' => 'New feature description',
        'critical' => true,
    ],
    // ... existing elements
],

// Add new permissions
'key_permissions' => [
    'new_module' => [
        'new_module.create' => 'Create new module items',
        'new_module.read' => 'View new module items',
        'new_module.update' => 'Update new module items',
        'new_module.delete' => 'Delete new module items',
    ],
    // ... existing permissions
],
```

### Role Expectations

Define what each role should access:

```php
'role_expectations' => [
    'admin' => [
        'description' => 'Full system administrator',
        'should_access' => [
            'new_feature' => true,
            'advanced_settings' => true,
        ],
        'permission_count' => 55, // Update when adding permissions
    ],
    // ... other roles
],
```

## 🎯 When to Run Tests

### During Development
```bash
# Quick check after changes
php test-role-access.php

# Detailed validation before commits
php run-access-tests.php --verbose
```

### Before Deployment
```bash
# Full validation
php run-access-tests.php --save

# CI/CD integration
php run-access-tests.php --json | jq '.results'
```

### After Adding New Features
1. Update `config/access-test.php` with new elements
2. Run comprehensive test: `php run-access-tests.php --verbose`
3. Fix any access issues discovered
4. Re-run tests to confirm fixes

## 📊 Understanding Test Results

### ✅ Passed Tests
- User has expected access to feature
- Permissions are correctly assigned
- Navigation elements show/hide properly

### ❌ Failed Tests
- User lacks expected access
- User has unexpected access
- Permission counts don't match expectations

### ⚠️ Warnings
- Missing roles or users
- Configuration inconsistencies
- Non-critical access issues

## 🔧 Troubleshooting

### No Users Found
```bash
php artisan migrate:fresh --seed
```

### Permission Count Mismatches
Check if new permissions were added:
```bash
php artisan permissions:sync --dry-run
```

### Navigation Tests Failing
1. Check blade files for updated role names
2. Verify route middleware is correct
3. Update role requirements in config

### Role Structure Issues
```bash
# Check actual vs expected roles
php artisan tinker
>>> \Spatie\Permission\Models\Role::pluck('name')
```

## 🚀 Integration with CI/CD

### GitHub Actions Example
```yaml
- name: Run Role Access Tests
  run: |
    php run-access-tests.php --json > access-test-results.json
    
- name: Check Test Results
  run: |
    if [ $(jq -r '.results.role_structure.status' access-test-results.json) != "passed" ]; then
      echo "Role access tests failed"
      exit 1
    fi
```

### Laravel Testing Integration
```php
// tests/Feature/RoleAccessTest.php
public function test_admin_can_access_user_management()
{
    $admin = User::factory()->create()->assignRole('admin');
    
    $response = $this->actingAs($admin)
        ->get(route('admin.users.index'));
        
    $response->assertStatus(200);
}
```

## 🔄 Maintenance

### Regular Tasks
1. **Weekly**: Run quick tests during development
2. **Before releases**: Full test suite with reports
3. **After role changes**: Update expectations and re-test
4. **New features**: Add to configuration and test

### Updating for New Roles
1. Add role to `config/access-test.php`
2. Define expected permissions and access
3. Update navigation elements as needed
4. Run tests to validate configuration

## 📈 Best Practices

1. **Test Early**: Run tests during feature development
2. **Configuration First**: Update config before implementing
3. **Document Changes**: Update role expectations when adding features
4. **Automate**: Include in CI/CD pipeline
5. **Review Reports**: Regularly check saved reports for trends

## 🆘 Getting Help

If tests are failing:

1. Check recent changes to roles/permissions
2. Verify configuration matches implementation
3. Run `php artisan permissions:sync` to update permissions
4. Check blade files for old role names
5. Review route middleware definitions

For complex issues, save a detailed report:
```bash
php run-access-tests.php --save --verbose
```

The report will be saved in `storage/logs/` with full debugging information.