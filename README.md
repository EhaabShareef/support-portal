# Support Portal

Support Portal is a web-based application for managing customer support requests. It is built with **Laravel 12**, **Livewire 3**, and **Tailwind CSS**, providing a modern stack for building responsive, reactive interfaces.

The application features a comprehensive **Role-Based Access Control (RBAC)** system using **Spatie Laravel Permission** for secure, scalable user and permission management.

## Key Features

- 🎫 **Ticket Management** - Create, track, and manage support tickets
- 🏢 **Organization Management** - Multi-tenant organization support
- 📋 **Contract Tracking** - View and manage client contracts
- 🛠️ **Hardware Management** - Track hardware assets and support
- 👥 **User Management** - Comprehensive user administration
- 📅 **Schedule Management** - Team schedule calendar with event tracking
- 🔐 **Role-Based Permissions** - Granular permission system with role management
- 📱 **Mobile Responsive** - Fully responsive design for all devices
- 🌙 **Dark Mode** - Toggle between light and dark themes

## Requirements

- PHP 8.2+
- Node.js 18+
- Composer
- npm
- MySQL 8.0+

## Getting Started

1. Install PHP dependencies: `composer install`
2. Install JavaScript dependencies: `npm install`
3. Copy the example environment: `cp .env.example .env` and adjust settings
4. Generate an application key: `php artisan key:generate`
5. Run database migrations: `php artisan migrate`
6. Seed the database with roles and permissions: `php artisan db:seed --class=RolePermissionSeeder`
7. (Optional) Seed with sample data: `php artisan db:seed --class=BasicDataSeeder`
8. Start development servers: `php artisan serve` and `npm run dev`

## Project Structure

The repository is organized as follows:

- `app/` – Application code including models, controllers, Livewire components, policies, services, console commands and traits.
- `bootstrap/` – Framework bootstrapping and cached files.
- `config/` – Configuration files for the framework and third‑party packages.
- `database/` – Database migrations, seeders and model factories.
- `public/` – Front controller (`index.php`) and publicly accessible assets.
- `resources/` – Frontend resources.
  - `views/` – Blade templates for the UI.
  - `js/` and `css/` – Source assets compiled via Vite.
- `routes/` – Route definitions for web and API endpoints.
- `storage/` – Compiled templates, file uploads and logs.
- `tests/` – Feature and unit test suites.
- `DATABASE_MIGRATION_GUIDE.md` – Notes on database setup and migrations.
- `STYLING_GUIDE.md` – Frontend styling conventions.
- `USER_ROLE_MANAGEMENT.md` – Documentation for role and permission management.

## Role-Based Access Control (RBAC)

The Support Portal implements a comprehensive RBAC system using **Spatie Laravel Permission**. This system provides secure, scalable access control with the following features:

### 🔑 **Core Principles**

- **Role-Based**: Permissions are assigned to roles, never directly to users
- **Single Role**: Each user has exactly one role for clarity and simplicity  
- **Module-Based**: Permissions are organized by application modules (Users, Tickets, Organizations, etc.)
- **CRUD Operations**: Standard Create, Read, Update, Delete permissions per module
- **Department Isolation**: Agents are restricted to their assigned department

### 👥 **Default Roles**

| Role | Description | Access Level |
|------|-------------|--------------|
| **Super Admin** | Full system access with all permissions | System-wide |
| **Admin** | Administrative access to manage users, organizations, and all modules | Organization-wide |
| **Agent** | Support agent with limited access to tickets and operations within their department | Department-limited |
| **Client** | Basic access to create and view tickets and articles | Organization-limited |

### 🛡️ **Permission Modules**

The system organizes permissions into the following modules:

- **Users** (create, read, update, delete)
- **Organizations** (create, read, update, delete)
- **Departments** (create, read, update, delete)
- **Tickets** (create, read, update, delete)
- **Contracts** (create, read, update, delete)
- **Hardware** (create, read, update, delete)
- **Settings** (read, update)
- **Notes** (create, read, update, delete)
- **Messages** (create, read, update, delete)
- **Articles** (create, read, update, delete)
- **Reports** (read)
- **Schedules** (create, read, update, delete)
- **Schedule Event Types** (create, read, update, delete)

### 🎛️ **Management Interface**

- **Role Management**: `/admin/roles` - Create and manage roles with permission grid interface
- **User Management**: `/admin/users` - Manage users and assign roles
- **Permission Grid**: Matrix-style interface for easy role-permission assignment
- **User Permissions View**: See exactly what permissions a user has through their role

### 🔒 **Access Control Features**

- **Department Isolation**: Agents can only access tickets from their assigned department
- **Organization Isolation**: Clients can only access tickets from their organization
- **Automatic Role Assignment**: New users automatically receive "Client" role by default
- **Permission Inheritance**: Users inherit all permissions from their assigned role
- **UI Filtering**: Interface elements are hidden/shown based on user permissions

### 🚀 **Getting Started with RBAC**

1. **Seed Roles & Permissions**: `php artisan db:seed --class=RolePermissionSeeder`
2. **Access Role Management**: Navigate to `/admin/roles` (Admin+ required)
3. **Manage Users**: Navigate to `/admin/users` (Admin+ required)
4. **Create Custom Roles**: Use the permission grid to define new roles
5. **Assign Roles**: Select appropriate roles when creating/editing users

### 🛠️ **Developer Notes**

- Permissions are checked using Spatie's `can()` method: `auth()->user()->can('tickets.create')`
- Role checks use: `auth()->user()->hasRole('Agent')`
- Department restrictions are enforced in Livewire components and policies
- All RBAC logic follows Laravel best practices and integrates seamlessly with the framework

## Recent Updates

### 🚀 **v2.1.0 - Organization Management Overhaul & Performance Optimization** (Latest)

#### 🎯 **Major Features**

- ✅ **Unified Organization Management**: Comprehensive overhaul of organization, contract, hardware, and user management with consistent UI/UX patterns
- ✅ **Compact Dashboard Tabs**: Streamlined organization view with compact cards showing essential information only
- ✅ **Dedicated Management Pages**: Separate management interfaces for contracts, hardware, users, and tickets with full CRUD operations
- ✅ **Smooth Transitions**: Enhanced user experience with Alpine.js transitions and dismissible elements
- ✅ **Centralized Validation**: Shared validation logic across organization components for consistency and maintainability

#### 🔧 **Performance & Code Quality Improvements**

- ✅ **Eliminated N+1 Queries**: Implemented comprehensive eager loading strategies across all organization components
  - `ViewOrganization` now eager-loads `users.roles`, `hardware.contract`, `tickets.client/assigned/department`
  - Optimized database queries for better performance with large datasets

- ✅ **Centralized Business Logic**: 
  - Created `HardwareValidationService` for consistent contract validation across all hardware forms
  - Developed `ValidatesOrganizations` trait to eliminate duplicate validation rules
  - Standardized hardware contract requirements enforcement

- ✅ **Enhanced Authorization**: Strengthened security with explicit permission checks in component lifecycle hooks
  - Added authorization verification in `ManageUsers` mount method
  - Implemented organization-scoped access control for client users

#### 🎨 **UI/UX Enhancements**

- ✅ **Consistent Design Patterns**: Applied unified compact tab design across all organization modules
  - **Contracts Tab**: Shows first 3 contracts with basic info, "Manage Contracts" button for full functionality
  - **Hardware Tab**: Displays first 3 hardware items with essential details, dedicated management interface
  - **Users Tab**: Lists first 3 users with role indicators, separate user management page
  - **Tickets Tab**: Compact view showing Subject, Case Number, Status, Client, Owner, Department with view-only access

- ✅ **Dismissible Elements**: Added user-controllable interface elements
  - Guidelines banner in users tab can be dismissed with smooth transitions
  - Better user experience with contextual help that doesn't obstruct workflow

- ✅ **Improved Form UX**: Standardized Livewire bindings for consistent interactions
  - `.defer` for form fields to reduce unnecessary network traffic
  - `.live.debounce.300ms` for search fields with optimized response times
  - `.live` for real-time filters

#### 📊 **Data Management & Consistency**

- ✅ **Hardware Type Standardization**: Created comprehensive enum system
  - `HardwareType` enum with 14 hardware categories (Desktop, Laptop, Server, Printer, etc.)
  - `HardwareStatus` enum with badge styling for visual consistency
  - Eliminated hard-coded options across all forms, improving maintainability

- ✅ **Role Filtering Optimization**: Replaced fragile array-based role filtering with proper model relationships
  - Changed from `where('roles.0.name', 'Client')` to `hasRole('Client')` for better performance and reliability
  - Improved query efficiency and reduced database load

- ✅ **Model Documentation**: Updated PHPDoc blocks to reflect actual database schema
  - Fixed `Organization` model documentation (`active_yn` → `is_active`)
  - Added missing properties (`subscription_status`, `notes`)
  - Removed unused fields (`custom_fields` from OrganizationHardware)

#### 🛠️ **Technical Architecture**

- ✅ **Service Layer Implementation**: 
  - `HardwareValidationService`: Centralized hardware-contract validation logic
  - Consistent business rule enforcement across all hardware entry points
  - Reduced code duplication and improved error handling

- ✅ **Trait-Based Validation**: 
  - `ValidatesOrganizations` trait with reusable validation rules and messages
  - Eliminates duplicate validation logic between `ManageOrganizations` and `ViewOrganization`
  - Supports exclusion rules for edit operations

- ✅ **Component Organization**: 
  - Separate management components (`ManageContracts`, `ManageHardware`, `ManageUsers`) for focused functionality
  - Compact overview tabs for quick information access
  - Clear separation of concerns between overview and management interfaces

#### 🔍 **Developer Experience**

- ✅ **Maintainable Codebase**: Significant reduction in code duplication through shared services and traits
- ✅ **Consistent Patterns**: Standardized approach to form handling, validation, and UI interactions
- ✅ **Performance Optimization**: Strategic eager loading prevents N+1 queries in high-traffic scenarios
- ✅ **Documentation**: Updated model PHPDoc blocks and inline code comments for better IDE support

### 🎉 **v2.0.0 - Enhanced RBAC System** (Previous)

- ✅ **Separate Role & User Management**: Independent role management interface with dedicated `/admin/roles` route
- ✅ **Permission Grid Interface**: Matrix-style role-permission management with module-based organization
- ✅ **Comprehensive Permission System**: 30+ granular permissions across 11 application modules
- ✅ **Default Client Role Assignment**: New users automatically receive Client role with appropriate permissions
- ✅ **Enhanced User Views**: ViewUser component now displays inherited permissions through roles
- ✅ **Strengthened Department Access Control**: Agents strictly limited to their department's tickets
- ✅ **Updated Navigation**: Separate "Users" and "Roles" menu items for better organization
- ✅ **Role Descriptions**: Detailed descriptions for each role explaining their purpose and scope
- ✅ **Database Schema Updates**: Added description column to roles table with proper migrations
- ✅ **Comprehensive Seeding**: Dedicated `RolePermissionSeeder` for clean role/permission setup

## Contributing

Pull requests are welcome. Please ensure code style is maintained and tests are updated.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

