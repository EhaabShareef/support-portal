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
- 📊 **Reports & Analytics** - Comprehensive admin-only reporting system with real-time insights
- 🎛️ **Dashboard Widgets** - Role-based customizable dashboard with smart widget system
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

- **Users** (create, read, update, delete, manage)
- **Organizations** (create, read, update, delete)
- **Departments** (create, read, update, delete)
- **Tickets** (create, read, update, delete, assign)
- **Contracts** (create, read, update, delete)
- **Hardware** (create, read, update, delete)
- **Settings** (read, update)
- **Notes** (create, read, update, delete)
- **Messages** (create, read, update, delete)
- **Articles** (create, read, update, delete)
- **Reports** (read) - *Admin-only comprehensive analytics and reporting system*
- **Schedules** (create, read, update, delete)
- **Schedule Event Types** (create, read, update, delete)
- **Dashboard** (access)

### 🎛️ **Management Interface**

- **Role Management**: `/admin/roles` - Create and manage roles with permission grid interface
- **User Management**: `/admin/users` - Manage users and assign roles
- **Reports Dashboard**: `/admin/reports` - Comprehensive analytics and reporting system (Admin-only)
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

## 🎛️ Dashboard Widget System

### 📊 **Role-Based Widgets**

The Support Portal features a comprehensive dashboard widget system that provides role-specific insights and functionality. Each user role has access to widgets tailored to their responsibilities and permissions.

#### **🔧 Admin Widgets**
- **System Health Monitor** - Real-time monitoring of database, cache, queues, and storage
- **Ticket Analytics** - Comprehensive ticket trends, status breakdown, and performance metrics  
- **Organization Management** - Contract alerts and organization overview metrics *(planned)*
- **User Activity Monitor** - Authentication metrics and online user tracking *(planned)*
- **Department Performance** - Efficiency analysis and workload distribution *(planned)*

#### **🎧 Support Widgets**
- **My Workload** - Personal ticket queue with priority indicators and resolution metrics
- **Team Performance** - Department rankings and comparative metrics *(planned)*
- **Quick Actions** - Common support shortcuts and ticket creation tools *(planned)*
- **Recent Activity** - Latest tickets and updates within department *(planned)*
- **Knowledge Insights** - Popular solutions and FAQ metrics *(planned)*

#### **👤 Client Widgets**
- **My Tickets Overview** - Organization ticket dashboard with status indicators
- **Service Status** - SLA compliance and system uptime metrics *(planned)*
- **Contract Information** - Active contracts and renewal alerts *(planned)*
- **Hardware Assets** - Inventory status and warranty tracking *(planned)*
- **Quick Support** - Easy access to support resources and ticket creation *(planned)*

### ⚙️ **Widget Management Features**

- **🎨 Customizable Layout**: Drag-and-drop widget reordering with persistent user preferences
- **📏 Multiple Sizes**: Each widget supports Small (1x1), Medium (2x2), and Large (3x2) variants
- **👁️ Visibility Control**: Show/hide widgets based on individual user preferences
- **🔄 Real-Time Updates**: Individual widget refresh with 5-minute cache TTL for performance
- **🛡️ Permission-Based**: Widgets automatically filtered by user roles and permissions

### 🚀 **Smart Fallback System**

- **🔍 Automatic Detection**: System checks component existence before rendering
- **😊 Friendly Messages**: Unimplemented widgets show "Widget still in beta brew" with widget details
- **🛠️ Development-Friendly**: Add widgets to database without breaking dashboard functionality
- **⚡ No Errors**: Missing components gracefully fall back instead of crashing

### 🎯 **Getting Started with Widgets**

1. **Access Dashboard**: Navigate to `/dashboard` after login
2. **Customize Layout**: Click "Customize" button to modify widget arrangement
3. **Toggle Sizes**: Select different widget sizes for optimal information display
4. **Refresh Data**: Use individual widget refresh buttons or global dashboard refresh
5. **Role-Based View**: Widgets automatically adjust based on your user role permissions

### 💻 **Technical Implementation**

- **Modular Architecture**: Widgets organized by role with size-specific implementations
- **Database-Driven**: Complete widget system configuration stored in database
- **Performance Optimized**: Efficient caching, lazy loading, and optimized queries
- **Security-First**: Multi-layer permission checks and role-based access control

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

## 📊 Reports & Analytics Module

### 🔍 **Comprehensive Reporting System**

The Support Portal features a powerful admin-only reporting system that provides detailed insights into all aspects of your support operations. Access reports through the `/admin/reports` dashboard.

#### **📈 Available Report Categories**

##### **1. Ticket & Support Performance**
- **Ticket Volume & Status Trends** ✅ - Track ticket counts and workload patterns with advanced filtering
- **Response & Resolution Time Analysis** - Monitor SLA compliance and team performance  
- **Agent Workload Distribution** - Ensure balanced workloads across support staff
- **Ticket Type & Priority Breakdown** - Analyze patterns to optimize resource allocation
- **Aging & Overdue Tickets** - Identify stalled requests requiring attention

##### **2. Organization & Contract Oversight**
- **Organization Summary** ✅ - High-level client engagement metrics with status indicators
- **Contract Renewal Forecast** - Proactive contract management and renewal planning
- **Contract Value Analysis** - Revenue tracking and financial insights

##### **3. Hardware & Asset Management**
- **Hardware Inventory Snapshot** - Complete asset visibility and allocation tracking
- **Warranty & Maintenance Schedule** - Proactive maintenance planning
- **Hardware Allocation by Contract** - Contract deliverable validation

##### **4. User & Department Activity**
- **User Account Status & Access** - Security auditing and access management
- **Department Performance** - Team effectiveness and resource allocation insights
- **Agent Productivity** - Individual performance tracking and recognition

##### **5. Schedule & Workforce Planning**
- **Schedule Coverage** - Staffing adequacy and coverage gap identification
- **User Schedule Summary** - Workload tracking and attendance validation

### 🛡️ **Security & Access Control**

- **Admin-Only Access**: All reports require admin role for maximum data security
- **Role-Based Authorization**: Multiple layers of permission checking
- **Read-Only Operations**: Reports never modify data, ensuring operational safety
- **Optimized Queries**: Performance-focused database queries with proper indexing

### ⚡ **Report Features**

- **Advanced Filtering**: Date ranges, organizations, departments, agents, status, priority, and more
- **Real-Time Updates**: Live filtering with instant results
- **Export Capabilities**: CSV and Excel export functionality (framework ready)
- **Responsive Design**: Mobile-friendly interface with dark mode support
- **Query String Persistence**: Shareable URLs with filter state preservation
- **Pagination Support**: Efficient handling of large datasets

### 🎯 **Getting Started with Reports**

1. **Access**: Navigate to `/admin/reports` (requires admin role)
2. **Browse Categories**: Select from 5 organized report categories
3. **Apply Filters**: Use comprehensive filtering options for targeted insights
4. **Analyze Data**: View trends, patterns, and key metrics
5. **Export Results**: Download reports for external analysis

## Recent Updates

### 🚀 **v4.2.0 - Dashboard Widget System Implementation** (Latest)

#### 📊 **Comprehensive Dashboard Widget Framework**

- ✅ **Role-Based Widget System**: Dynamic dashboard widgets customized for user roles (Admin, Support, Client)
  - **Admin Widgets**: System Health Monitor, Ticket Analytics, Organization Management, User Activity, Department Performance
  - **Support Widgets**: My Workload, Team Performance, Quick Actions, Recent Activity, Knowledge Insights  
  - **Client Widgets**: My Tickets Overview, Service Status, Contract Information, Hardware Assets, Quick Support
  - Each widget supports multiple size variants: Small (1x1), Medium (2x2), Large (3x2)

- ✅ **Smart Widget Management**: User-customizable dashboard with persistent settings
  - **Customize Modal**: Drag-and-drop widget reordering with live preview
  - **Size Selection**: Toggle between available widget sizes with instant updates
  - **Visibility Control**: Show/hide widgets based on user preferences
  - **Permission-Based Access**: Widgets filtered by user roles and permissions

- ✅ **Universal Fallback System**: Graceful handling of unimplemented widgets
  - **Automatic Detection**: System checks if widget components exist before rendering
  - **Friendly Fallback**: "Widget still in beta brew" message with widget name and size
  - **No Error Crashes**: Missing components show fallback instead of breaking the dashboard
  - **Development-Friendly**: Easy to add new widgets to seeder without implementation

#### 🎯 **Implemented Widgets** (Working Examples)

- ✅ **System Health Monitor** (Admin - All sizes): Real-time system monitoring
  - Database connectivity, cache status, queue health, storage space
  - Performance metrics including response times and resource usage
  - Visual status indicators with color-coded health states

- ✅ **Ticket Analytics** (Admin - Small/Medium): Advanced ticket trend analysis  
  - Today/weekly ticket counts with percentage trend indicators
  - Status breakdown (open, in progress, resolved, closed)
  - Priority distribution and average resolution time metrics

- ✅ **My Workload** (Support - Small): Personal ticket queue management
  - Open assigned tickets count with high priority indicators
  - Daily resolution metrics and total workload overview
  - Real-time updates with 5-minute cache TTL

- ✅ **My Tickets Overview** (Client - Small): Organization ticket dashboard
  - Recent tickets with creation dates and status indicators
  - Organization-scoped filtering for multi-tenant security
  - Clean interface showing ticket subjects and current status

#### 🔧 **Technical Architecture**

- ✅ **Modular Component Structure**: Organized by role and size variants
  ```
  app/Livewire/Dashboard/Widgets/
  ├── Admin/SystemHealth/{Small,Medium,Large}.php
  ├── Admin/TicketAnalytics/{Small,Medium,Large}.php  
  ├── Support/MyWorkload/{Small,Medium,Large}.php
  ├── Client/MyTickets/{Small,Medium,Large}.php
  └── FallbackWidget.php
  ```

- ✅ **Smart Component Loading**: Automatic fallback system prevents errors
  - `componentExists()` method checks for widget class existence
  - `getComponentForSizeWithFallback()` returns appropriate component or fallback
  - Dashboard template updated to handle missing components gracefully

- ✅ **Database-Driven Configuration**: Complete widget system in database
  - 16 widget definitions across all roles with size variants and permissions
  - User settings table stores personal widget preferences
  - Dynamic component resolution based on database configuration

#### 🎨 **User Experience Features**

- ✅ **Responsive Grid Layout**: Adaptive dashboard layout for all screen sizes
  - CSS Grid with dynamic columns (1/2/4 columns based on screen size)
  - Widget size classes: Small (1x1), Medium (2x2), Large (3x2)
  - Proper spacing and visual hierarchy

- ✅ **Interactive Elements**: Rich user interactions throughout
  - Refresh buttons on individual widgets with loading states
  - Hover effects and smooth transitions using Alpine.js
  - Real-time data updates with caching for performance

- ✅ **Dark Mode Support**: Complete dark theme compatibility
  - All widgets support light/dark mode with proper contrast
  - Consistent styling with application theme
  - Backdrop blur effects and translucent backgrounds

#### 🛡️ **Security & Performance**

- ✅ **Permission-Based Access**: Multi-layer security enforcement
  - Role-based widget filtering (`dashboard.admin`, `dashboard.support`, `dashboard.client`)
  - Component-level permission checks in mount methods
  - User can only see widgets appropriate for their role

- ✅ **Performance Optimization**: Efficient data loading and caching
  - 5-minute cache TTL for all widget data queries
  - Lazy loading with proper loading states
  - Optimized database queries with eager loading relationships

- ✅ **Error Handling**: Comprehensive error management
  - Try-catch blocks in all data loading methods
  - Graceful degradation with error state displays
  - Logging for debugging without breaking user experience

#### 📈 **Future-Ready Framework**

- ✅ **Expansion Capabilities**: Easy to add new widgets
  - Standardized component patterns for consistent development
  - Database seeder with 16 widget definitions (5 implemented, 11 planned)
  - Template structure supports rapid widget development

- ✅ **Customization Options**: Rich configuration possibilities
  - Widget options stored as JSON for flexible configuration
  - Size variant system supports different layouts per widget
  - User preferences persist across sessions

### 🚀 **v4.1.0 - Reports & Analytics Module Implementation** (Previous)

#### 📊 **Major Features**

- ✅ **Comprehensive Reports System**: Admin-only reporting module with 15 planned report types across 5 categories
  - **Ticket & Support Performance**: Volume trends, response times, agent workload, type breakdowns, aging analysis
  - **Organization & Contract Oversight**: Organization summaries, renewal forecasts, contract value analysis
  - **Hardware & Asset Management**: Inventory snapshots, warranty schedules, allocation tracking
  - **User & Department Activity**: Account status, performance metrics, productivity analysis
  - **Schedule & Workforce Planning**: Coverage analysis, schedule summaries, workforce insights

- ✅ **Advanced Report Implementation**: Two fully functional reports with rich features
  - **Ticket Volume & Status Trends**: Multi-dimensional filtering, grouping options, date aggregation, visual charts
  - **Organization Summary**: High-level metrics dashboard, status indicators, pagination support
  - Framework established for rapid implementation of remaining 13 reports

- ✅ **Enterprise-Grade Features**: Production-ready reporting infrastructure
  - **Advanced Filtering**: Date ranges, multi-level organization/department filters, user selection, status/priority filtering
  - **Real-Time Updates**: Live filter application with instant results and query string persistence
  - **Visual Analytics**: Percentage bars, status badges, metric cards, responsive charts
  - **Export Framework**: Ready for CSV/Excel export implementation across all reports

#### 🔒 **Security & Access Control**

- ✅ **Admin-Only Access**: Complete restriction to admin role with multiple authorization layers
  - Route-level middleware protection (`role:admin`)
  - Component-level authorization checks in all Livewire components
  - Permission-based access control through existing Spatie system
  - Reports module configured in `config/modules.php` with `reports.read` permission

- ✅ **Data Security**: Read-only operations with optimized, secure database queries
  - Parameterized queries preventing SQL injection
  - Role-based data filtering ensuring users see only authorized information
  - Performance-optimized queries using indexed columns and eager loading

#### 🎨 **User Interface & Experience**

- ✅ **Professional Dashboard**: Clean, categorized report selection interface
  - 5 organized categories with 15 total report types
  - Visual icons and descriptions for each report
  - Availability indicators showing implemented vs planned reports
  - Responsive card-based layout with hover effects

- ✅ **Rich Report Interface**: Advanced filtering and visualization
  - Comprehensive filter panels with real-time updates
  - Visual data representation with percentage bars and charts
  - Status badges, metric cards, and summary statistics
  - Mobile-responsive design with dark mode support
  - Consistent styling matching existing application theme

#### 🛠️ **Technical Architecture**

- ✅ **Scalable Framework**: Modular structure for rapid report development
  - Standardized Livewire component patterns for consistent development
  - Reusable filter components and query building patterns
  - Template-based report structure for quick implementation
  - Configuration-driven dashboard with easy report addition

- ✅ **Performance Optimization**: Efficient data handling for large datasets
  - Strategic eager loading preventing N+1 queries
  - Pagination support for handling large result sets
  - Computed properties for expensive calculations
  - Query string persistence for shareable report URLs

- ✅ **Navigation Integration**: Seamless integration with existing admin interface
  - Reports link added to admin sidebar navigation
  - Consistent with existing admin module patterns
  - Breadcrumb navigation and back buttons for intuitive flow

#### 📁 **Implementation Structure**

- ✅ **Organized File Structure**: Clean separation of concerns
  ```
  app/Livewire/Admin/Reports/          # Report components
  resources/views/livewire/admin/reports/ # Report templates  
  routes/web.php                       # Protected admin routes
  config/modules.php                  # Permission configuration
  ```

- ✅ **Documentation**: Comprehensive implementation documentation
  - `REPORTS_MODULE_IMPLEMENTATION.md` with complete technical details
  - Updated README with reports section and usage instructions
  - Code comments and architectural notes for maintainability

#### 🎯 **Business Value**

- ✅ **Immediate Operational Insights**: Two working reports providing instant value
  - Track ticket volume trends and identify workload spikes
  - Monitor organization health and engagement metrics
  - Data-driven decision making for resource allocation

- ✅ **Expansion Ready**: Framework supports rapid development of remaining reports
  - 13 additional reports can be implemented quickly using established patterns
  - Consistent user experience across all report types
  - Scalable architecture supporting future reporting needs

### 🚀 **v4.0.0 - Complete Organizational Structure Rebuild** (Previous)

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

