# User and Role Management System

## Overview
This system implements comprehensive user and role management with role-based access control (RBAC) using the Spatie Laravel Permission package.

## Role Structure

### 1. **Admin** 
- **Purpose**: Company managers with full system access
- **Permissions**: Complete access to all features
- **Data Access**: Can view and manage all users, tickets, organizations
- **Department/Organization**: Not tied to specific department or organization

### 2. **support**
- **Purpose**: Support agents managing tickets within their departments
- **Permissions**: Can view and update tickets, view organizations
- **Data Access**: Limited to tickets within their assigned department
- **Department/Organization**: Must be assigned to a department

### 3. **Client**
- **Purpose**: Users from organizations who can create and view tickets
- **Permissions**: Can create and view tickets from their organization
- **Data Access**: Limited to tickets from their own organization
- **Department/Organization**: Must be assigned to an organization

## Features Implemented

### User Management (Admin Only)
- ✅ **CRUD Operations**: Create, read, update, delete users
- ✅ **Soft Deletion**: Uses `active_yn` field instead of hard deletion
- ✅ **Role Assignment**: Single role per user based on business logic
- ✅ **Department/Organization Assignment**: Automatic based on role
- ✅ **Search & Filtering**: By name, email, role, department, organization, status
- ✅ **Status Toggle**: Activate/deactivate users

### Role-Based Access Control
- ✅ **Route Protection**: Admin routes protected by role middleware
- ✅ **Data Filtering**: Automatic filtering based on user role
- ✅ **Permission System**: Granular permissions for different actions
- ✅ **Policy-Based Authorization**: Laravel policies for fine-grained control

### Security Features
- ✅ **Password Hashing**: Automatic password hashing in User model
- ✅ **Unique Constraints**: Username and email uniqueness validation
- ✅ **Form Validation**: Comprehensive form validation with custom messages
- ✅ **Authorization Policies**: Prevent unauthorized access to resources

## File Structure

```
app/
├── Livewire/
│   └── Admin/
│       └── ManageUsers.php          # User management component
├── Policies/
│   ├── UserPolicy.php               # User authorization policies
│   └── TicketPolicy.php             # Ticket authorization policies
├── Http/
│   └── Middleware/
│       └── FilterByUserRole.php     # Role-based filtering middleware
└── Models/
    └── User.php                     # Enhanced User model

resources/views/livewire/admin/
└── manage-users.blade.php           # User management interface

database/seeders/
└── PermissionAndRoleSeeder.php      # Updated with comprehensive permissions

routes/
└── web.php                          # Admin routes with role protection
```

## Permissions Structure

### Ticket Permissions
- `tickets.read` - View tickets
- `tickets.create` - Create new tickets
- `tickets.update` - Update existing tickets
- `tickets.delete` - Delete tickets

### User Management Permissions
- `users.read` - View users
- `users.create` - Create new users
- `users.update` - Update existing users
- `users.delete` - Delete users

### Organization Permissions
- `organizations.read` - View organizations
- `organizations.create` - Create organizations
- `organizations.update` - Update organizations
- `organizations.delete` - Delete organizations

### System Permissions
- `admin.access` - Access admin panel

## Usage

### Accessing User Management
1. Login as an Admin user
2. Navigate to `/admin/users`
3. The interface provides full CRUD operations

### Creating Users
1. Click "Add User" button
2. Fill in required information
3. Select appropriate role (Admin/Agent/Client)
4. For support: Must select a department
5. For client: Must select an organization
6. Set password and activation status

### Data Access Patterns

#### Admin Users
```php
// Can access all data without restrictions
Ticket::all();
User::all();
Organization::all();
```

#### support Users
```php
// Automatically filtered to their department
Ticket::where('dept_id', auth()->user()->department_id);
```

#### Client Users
```php
// Automatically filtered to their organization
Ticket::where('org_id', auth()->user()->organization_id);
```

## Database Schema

### Users Table
- `active_yn` - Boolean for soft deletion
- `department_id` - FK to departments (for Agents)
- `organization_id` - FK to organizations (for Clients)

### Role Assignment Logic
- **admin**: `department_id = null`, `organization_id = null`
- **support**: `department_id = required`, `organization_id = null`
- **client**: `department_id = null`, `organization_id = required`

## Next Steps for Enhancement

1. **User Profile Management**: Allow users to update their own profiles
2. **Bulk Operations**: Bulk activate/deactivate users
3. **Advanced Filtering**: Date ranges, custom filters
4. **Audit Logging**: Track user management actions
5. **Email Notifications**: Welcome emails, password resets
6. **Export Functionality**: Export user lists
7. **Two-Factor Authentication**: Enhanced security

## Security Considerations

- All routes are protected by authentication middleware
- Admin routes require `admin` role
- Passwords are automatically hashed
- Form inputs are validated and sanitized
- Authorization policies prevent unauthorized access
- Soft deletion preserves data integrity