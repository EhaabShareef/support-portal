# Migration and Seeder Cleanup Plan for Beta Release 5.5.0

This guide documents the tasks required to align migrations and seeders with the current application state.
Follow the steps in order; each item notes the affected file, the problem, and the proposed fix.

## 1. Migration Audit and Refactor

### 1.1 Remove Deprecated Migrations
- **Files:** `database/migrations/deprecated/*`
- **Issue:** Legacy migrations remain in the repository and can cause conflicts or confusion during fresh setups.
- **Fix:** Delete the entire `deprecated` directory.

### 1.2 Consolidate Ticket Table Migrations
- **Files:**
  - `database/migrations/2025_01_01_000005_create_tickets_table.php`
  - `database/migrations/2025_08_11_131605_add_latest_message_at_to_tickets_table.php`
  - `database/migrations/2025_08_12_120741_move_ticket_descriptions_to_messages.php`
  - `database/migrations/2025_08_13_175511_convert_tickets_status_enum_to_string.php`
  - `database/migrations/2025_01_01_000021_add_performance_indexes_to_tickets_table.php`
- **Issue:** Multiple incremental patches add columns and indexes for the same table.
- **Fix:** Merge the columns and indexes into a single `create_tickets_table` migration.
- **Issue:** Multiple patches modify columns, indexes, and status handling for the same table.
- **Fix:** Merge these changes into a single `create_tickets_table` migration.
  - Drop the deprecated `description` column and create an initial ticket message instead.
  - Include the `latest_message_at` timestamp and required indexes directly in the base migration.
  - Include the `latest_message_at` timestamp, string-based `status` field with index, and required performance indexes directly in the base migration.
  - Remove the subsequent patch migrations.
- **Before:**
  ```php
  // create_tickets_table
  $table->text('description')->nullable();
  ```
- **After:**
  ```php
  // create_tickets_table
  $table->timestamp('latest_message_at')->nullable();
  // description removed; messages stored in ticket_messages
  ```

### 1.3 Merge User Avatar Column
### 1.3 Integrate Related Table Indexes
 **Files:**
 - `database/migrations/2025_01_01_000006_create_ticket_messages_table.php`
 - `database/migrations/2025_01_01_000007_create_ticket_notes_table.php`
 - `database/migrations/2025_01_01_000004_create_users_table.php`
 - `database/migrations/2025_01_01_000003_create_departments_table.php`
 - `database/migrations/2025_01_01_000021_add_performance_indexes_to_tickets_table.php`
 **Issue:** Indexes for these tables were added in a separate patch migration.
 **Fix:** Add the necessary indexes directly to each table's creation migration and remove `add_performance_indexes_to_tickets_table.php`.
 **Before:**
 ```php
 // create_ticket_messages_table
 Schema::create('ticket_messages', function (Blueprint $table) {
     $table->id();
     $table->foreignId('ticket_id');
     $table->timestamps();
 });
 ```
 **After:**
 ```php
 // create_ticket_messages_table
 Schema::create('ticket_messages', function (Blueprint $table) {
     $table->id();
     $table->foreignId('ticket_id');
     $table->timestamps();
     $table->index(['ticket_id', 'created_at']);
 });
 ```
### 1.4 Merge User Avatar Column
- **Files:**
  - `database/migrations/2025_01_01_000004_create_users_table.php`
  - `database/migrations/2025_01_01_000023_add_avatar_to_users_table.php`
- **Issue:** Avatar column added via separate patch.
- **Fix:** Add `avatar` field to `create_users_table` and drop the patch migration.
- **Before:**
  ```php
  // create_users_table
  $table->string('password');
  ```
- **After:**
  ```php
  // create_users_table
  $table->string('password');
  $table->string('avatar')->nullable();
  ```

### 1.4 Standardize Schedule Event Types
### 1.5 Standardize Schedule Event Types
- **Files:**
  - `database/migrations/2025_01_01_000015_create_schedule_event_types_table.php`
  - `database/migrations/2025_01_01_000026_standardize_schedule_event_types_structure.php`
- **Issue:** Later migration renames `name` to `label` and adds styling fields.
- **Fix:** Fold the `label` and `tailwind_classes` fields into the initial creation migration and remove the restructuring patch.
- **Before:**
  ```php
  // create_schedule_event_types_table
  $table->string('name')->unique();
  ```
- **After:**
  ```php
  // create_schedule_event_types_table
  $table->string('label')->unique();
  $table->string('tailwind_classes');
  ```

### 1.5 Settings Table Data Migrations
### 1.6 Settings Table Data Migrations
- **Files:**
  - `database/migrations/2025_01_01_000011_update_setting_groups.php`
  - `database/migrations/2025_08_12_124426_add_ticket_reopen_window_setting.php`
- **Issue:** These migrations only seed or update data and duplicate logic already present in seeders.
- **Fix:** Remove these migrations and ensure `ApplicationSettingsSeeder` seeds the required values.

### 1.6 Dashboard Widget Key
### 1.7 Dashboard Widget Key
- **Files:**
  - `database/migrations/2025_01_01_000024_create_dashboard_widgets_table.php`
  - `database/seeders/DashboardWidgetSeeder.php`
  - `database/seeders/UserWidgetSettingsSeeder.php`
- **Issue:** Seeder expects a `key` column (`DashboardWidget::all()->keyBy('key')`) but the table lacks it.
- **Fix:**
  - Add a unique `key` string column to the `dashboard_widgets` migration.
  - Update `DashboardWidgetSeeder` to set this `key` for each widget.
  - Keep `UserWidgetSettingsSeeder` using the `key` for lookups.
- **Before:**
  ```php
  // create_dashboard_widgets_table
  $table->string('name', 100);
  ```
- **After:**
  ```php
  // create_dashboard_widgets_table
  $table->string('key', 100)->unique();
  $table->string('name', 100);
  ```

### 1.8 Unify Hardware Types Table
- **Files:**
  - `database/migrations/2025_01_01_000011_create_hardware_types_table.php`
  - `database/migrations/2025_08_13_174623_create_hardware_types_table.php`
- **Issue:** Two separate "create" migrations exist for the same table.
- **Fix:** Keep a single `create_hardware_types_table` migration with slug, description, ordering, flags, and indexes; remove the earlier minimal version.
- **Before:**
  ```php
  // create_hardware_types_table (old)
  $table->id();
  $table->string('name');
  $table->timestamps();
  ```
- **After:**
  ```php
  // create_hardware_types_table (final)
  $table->id();
  $table->string('name');
  $table->string('slug')->unique();
  $table->text('description')->nullable();
  $table->integer('sort_order')->default(0);
  $table->boolean('is_protected')->default(false);
  $table->boolean('is_active')->default(true);
  $table->timestamps();
  $table->index(['is_active', 'sort_order']);
  $table->index('slug');
  ```

## 2. Seeder Review and Updates

### 2.1 Client Sample Data Seeder
- **File:** `database/seeders/ClientSampleDataSeeder.php`
- **Issue:** Tickets are seeded with a `description` field which will be removed from the schema.
- **Fix:** After creating each ticket, insert the initial client message into `ticket_messages` and remove the `description` assignment.
- **Before:**
  ```php
  $ticket = new Ticket([
      'subject' => $ticketData['subject'],
      'description' => $ticketData['description'],
      // ...
  ]);
  ```
- **After:**
  ```php
  $ticket = Ticket::create([
      'subject' => $ticketData['subject'],
      // ...
  ]);
  TicketMessage::create([
      'ticket_id' => $ticket->id,
      'sender_id' => $clientUser->id,
      'message' => $ticketData['description'],
  ]);
  ```

### 2.2 User Widget Settings Seeder
- **File:** `database/seeders/UserWidgetSettingsSeeder.php`
- **Issue:** Uses `$widgets = DashboardWidget::all()->keyBy('key')` but `dashboard_widgets` table currently lacks the `key` column.
- **Fix:** Once the `key` column is added to the migration and seeded, no further changes are required. Ensure the seeder relies on the `key` column for lookups.

### 2.3 Application Settings Seeder
- **File:** `database/seeders/ApplicationSettingsSeeder.php`
- **Issue:** Already seeds `tickets.reopen_window_days`; migration duplicate must be removed.
- **Fix:** No changes needed other than removing the overlapping migration.

### 2.4 Hardware Type Seeders
- **Files:**
  - `database/seeders/HardwareTypesSeeder.php`
  - `database/seeders/HardwareTypeSeeder.php`
- **Issue:** Two seeders populate `hardware_types` with overlapping data.
- **Fix:** Consolidate into `HardwareTypeSeeder.php` which matches the final schema. Remove `HardwareTypesSeeder.php` and its reference in `DatabaseSeeder`.

## 3. Final Cleanup Steps

1. Apply the migration refactors above, ensuring each table has a single authoritative migration ordered by dependency.
2. Run `php artisan migrate:fresh --seed` to verify the new migrations and seeders execute without errors.
3. Commit the cleaned migrations and seeders, then tag the release as **Beta 5.5.0**.

---
Following this plan will leave the codebase with a concise, ordered migration set and seeders that match the final schema, ensuring reliable deployments for Beta Release 5.5.0.
