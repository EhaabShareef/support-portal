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
5. **Fresh Setup**: Run complete database rebuild: `php artisan migrate:fresh --seed`
   - This clears all data and rebuilds the complete organizational structure
   - Creates 7 department groups, 22 departments, 2 roles, and 8 default users
   - **Alternative**: For existing setups, run individual seeders in order:
     - `php artisan db:seed --class=RolePermissionSeeder` (clears existing data)
     - `php artisan db:seed --class=DepartmentGroupSeeder`
     - `php artisan db:seed --class=DepartmentSeeder`
     - `php artisan db:seed --class=UserSeeder`
6. Start development servers: `php artisan serve` and `npm run dev`

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
- **Department Isolation**: Support staff are restricted to their assigned department

### 👥 **Default Roles**

| Role | Description | Access Level |
|------|-------------|--------------|
| **admin** | Full system access with all 50 permissions across all modules | System-wide |
| **support** | Limited access role with basic read, create, and update permissions (36 permissions) | Department-limited |

**Note**: The system now uses a simplified 2-role structure. Additional roles can be created through the admin interface as needed.

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

- **Department Isolation**: Support staff can only access tickets from their assigned department
- **Organization Isolation**: Clients can only access tickets from their organization
- **Automatic Role Assignment**: New users automatically receive "Client" role by default
- **Permission Inheritance**: Users inherit all permissions from their assigned role
- **UI Filtering**: Interface elements are hidden/shown based on user permissions

### 🚀 **Getting Started with RBAC**

1. **Fresh Setup**: `php artisan migrate:fresh --seed` (rebuilds entire structure)
2. **Access Role Management**: Navigate to `/admin/roles` (Admin role required)
3. **Manage Users**: Navigate to `/admin/users` (Admin role required)
4. **Create Custom Roles**: Use the permission grid to define new roles beyond admin/support
5. **Assign Roles**: Select appropriate roles when creating/editing users

### 🛠️ **Developer Notes**

- Permissions are checked using Spatie's `can()` method: `auth()->user()->can('tickets.create')`
- Role checks use: `auth()->user()->hasRole('support')`
- Department restrictions are enforced in Livewire components and policies
- All RBAC logic follows Laravel best practices and integrates seamlessly with the framework

## Organizational Structure

### 🏢 **Department Groups & Departments**

The system is organized into 7 department groups with 22 total departments:

#### **Admin Group** (Admin role)
- Admin
- Finance  
- Human Resource
- Project Manage
- Sales

#### **PMS Group** (Support role)
- Opera
- Opera Cloud
- Vision
- R&A (Reporting & Analytics)
- OXI
- Technical

#### **POS Group** (Support role)
- Simphny
- Simphony Cloud
- RES 3700
- RES 9700
- R&A (Reporting & Analytics)

#### **MC Group** (Support role)
- Materials Control
- Reporting

#### **BO Group** (Support role)
- BackOffice

#### **Hardware Group** (Support role)
- Local
- Oracle

#### **Email Group** (Admin role)
- Email Case

### 👤 **Default Users**

Each department group has a default manager user:

- **Admin**: `superadmin@hospitalitytechnology.com.mv` (admin role)
- **Admin Manager**: `admin@hospitalitytechnology.com.mv` (admin role)
- **PMS Manager**: `pms@hospitalitytechnology.com.mv` (support role)
- **POS Manager**: `pos@hospitalitytechnology.com.mv` (support role)
- **MC Manager**: `mc@hospitalitytechnology.com.mv` (support role)
- **BO Manager**: `bo@hospitalitytechnology.com.mv` (support role)
- **Hardware Manager**: `hardware@hospitalitytechnology.com.mv` (support role)
- **Email Manager**: `email@hospitalitytechnology.com.mv` (support role)

**Default Password**: `password` (should be changed after first login)

## Recent Updates

### 🚀 **v4.0.0 - Complete Organizational Structure Rebuild** (Latest)

#### 🏗️ **Major Restructuring**

- ✅ **Fresh Database Architecture**: Complete rebuild of organizational structure from scratch
  - 7 department groups following hospitality technology domains
  - 22 departments with proper hierarchical organization
  - Simplified 2-role system (admin/support) for clear access control
  - 8 default users with proper department group assignments

- ✅ **Streamlined Role System**: Simplified from 4 roles to 2 focused roles
  - **admin**: Full system access (50 permissions)
  - **support**: Limited access (36 permissions) - configurable via admin interface
  - Eliminates confusion between old role names and new standardized roles

- ✅ **Professional Email Structure**: Standardized email format
  - Format: `groupname@hospitalitytechnology.com.mv`
  - Proper domain alignment with Hospitality Technology branding
  - Clear identification of department group responsibilities

#### 🔧 **Technical Implementation**

- ✅ **Complete Seeder Overhaul**: 
  - `DepartmentGroupSeeder`: Creates 7 department groups with colors and descriptions
  - `DepartmentSeeder`: Creates 22 departments with proper group assignments
  - `RolePermissionSeeder`: Clears existing data, creates admin/support roles
  - `UserSeeder`: Creates manager users for each department group
  - `DatabaseSeeder`: Orchestrates complete rebuild process

- ✅ **Data Integrity**: 
  - Clears all existing users, roles, permissions, departments, and groups
  - Rebuilds from scratch to ensure clean organizational structure
  - Maintains referential integrity with proper foreign key relationships
  - Transaction-safe seeding with rollback capabilities

- ✅ **Migration Compatibility**: 
  - Works with existing migration structure
  - No breaking changes to existing database schema
  - Maintains compatibility with all existing features

#### 📊 **Business Logic Alignment**

- ✅ **Domain-Specific Organization**: 
  - **Admin**: Administrative functions (Finance, HR, Sales, Project Management)
  - **PMS**: Property Management Systems (Opera, Vision, OXI, Technical)
  - **POS**: Point of Sale Systems (Simphony, RES series)
  - **MC**: Materials Control and Reporting
  - **BO**: BackOffice operations
  - **Hardware**: Local and Oracle hardware support
  - **Email**: Email case management

- ✅ **Role Assignment Logic**: 
  - Admin and Email groups get admin role (full system access)
  - All technical groups (PMS, POS, MC, BO, Hardware) get support role
  - Admin user separate from department structure
  - Clear separation of administrative vs operational responsibilities

#### 🎯 **Deployment & Maintenance**

- ✅ **One-Command Setup**: `php artisan migrate:fresh --seed` rebuilds everything
- ✅ **Production Ready**: All seeders designed for safe production deployment
- ✅ **Scalable Structure**: Easy to add new departments and department groups
- ✅ **Permission System**: Maintains existing granular permission system with new role structure

### 🚀 **v3.0.0 - Schedule Management System & Advanced Calendar** (Previous)

#### 📅 **Major Features**

- ✅ **Comprehensive Schedule Management**: Full-featured team calendar system with event tracking
  - Monthly calendar view with user rows organized by department groups
  - Color-coded event badges with 18 predefined event types (PR, PO, WFH, DIL, SO, etc.)
  - Date range support for multi-day events with seamless spanning visualization
  - Interactive hover tooltips showing event details, dates, and remarks

- ✅ **Advanced Event Management**: Robust CRUD operations with policy-based authorization
  - Create, edit, and delete events directly from calendar cells
  - Hover-based action buttons for intuitive event management
  - Real-time validation preventing overlapping events per user
  - Comprehensive permission system with role-based access control

- ✅ **Professional Calendar Interface**: Modern, responsive design with enhanced UX
  - Spanning events display as seamless containers across multiple days
  - Interactive "+more" indicators with detailed event popovers
  - Sticky headers and custom scrollbars for optimal navigation
  - Department group filtering and event type filtering

#### 🔧 **Technical Architecture Improvements**

- ✅ **Database Schema Optimization**: Clean, performance-focused data structure
  - Removed legacy `date` column, standardized on `start_date`/`end_date`
  - Proper foreign key cascade rules for data integrity
  - Optimized indexes for date range queries and user lookups
  - Consolidated migrations following `2025_01_01_000X` naming convention

- ✅ **Performance Optimization**: Eliminated N+1 queries and improved scalability
  - Pre-grouped schedule data with `schedulesGroupedByUserAndDay` computed property
  - Replaced per-cell filtering with efficient data structure lookup
  - Optimized client user filtering with direct organization relationships
  - Strategic eager loading for user, department, and event type relationships

- ✅ **Authorization Enhancement**: Comprehensive policy-based security system
  - `SchedulePolicy` and `ScheduleEventTypePolicy` with granular permissions
  - Gates for module access control (`access-schedule-module`, `manage-schedules`)
  - Role-based viewing restrictions (Clients see only their organization, Support see department-level)
  - Method-level authorization using Laravel's `authorize()` helper

#### 🎨 **User Experience Enhancements**

- ✅ **Interactive Calendar Elements**: Rich, responsive user interactions
  - Edit/delete actions appear on hover with smooth transitions
  - Confirmation dialogs for destructive operations
  - Alpine.js powered popovers with event details and date ranges
  - Visual feedback for all user actions with loading states

- ✅ **Advanced Event Visualization**: Professional calendar appearance
  - Multi-day events span seamlessly across date ranges instead of separate cells
  - Event type dropdown with color preview and code display
  - Enhanced tooltips showing event type, date range, and custom remarks
  - Responsive design adapting to different screen sizes

- ✅ **Robust Form Handling**: Comprehensive event creation and editing
  - Date range picker with start/end date validation
  - User selection dropdown with department information
  - Event type selection with visual color coding
  - Real-time form validation with detailed error messages

#### 📊 **Data Management & Consistency**

- ✅ **Schedule Event Types**: Comprehensive event categorization system
  - 18 predefined event types with unique codes and colors
  - Configurable through admin settings interface
  - Proper seeding with default "SO" (Office Support) event type
  - Color management with Tailwind CSS class support

- ✅ **Business Logic Enforcement**: Robust validation and constraint handling
  - One event per user per time period validation
  - Overlap detection with comprehensive date range checking
  - Required field validation with user-friendly error messages
  - Consistent data integrity across all operations

#### 🛠️ **Developer Experience**

- ✅ **Clean Architecture**: Well-organized, maintainable codebase
  - Separate policy classes for authorization logic
  - Service provider registration for policies and gates
  - Consistent method naming and parameter handling
  - Comprehensive error handling with logging

- ✅ **Migration Management**: Streamlined database versioning
  - All migrations follow consistent `2025_01_01_000X` naming
  - Consolidated schedule-related migrations for clean deployment
  - Proper up/down migration methods with rollback support
  - Foreign key constraints with cascade delete rules

### 🚀 **v2.1.0 - Organization Management Overhaul & Performance Optimization** (Previous)

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
- ✅ **Strengthened Department Access Control**: Support staff strictly limited to their department's tickets
- ✅ **Updated Navigation**: Separate "Users" and "Roles" menu items for better organization
- ✅ **Role Descriptions**: Detailed descriptions for each role explaining their purpose and scope
- ✅ **Database Schema Updates**: Added description column to roles table with proper migrations
- ✅ **Comprehensive Seeding**: Dedicated `RolePermissionSeeder` for clean role/permission setup

## Contributing

Pull requests are welcome. Please ensure code style is maintained and tests are updated.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

