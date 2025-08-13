<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\HardwareTypesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * For production deployment, this seeder provides only essential baseline data.
     * For development with sample data, use: php artisan db:seed --class=DevelopmentDatabaseSeeder
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting complete database rebuild...');
        
        // Seed in correct order
        $this->call([
            RolePermissionSeeder::class,     // Clears all data, creates permissions and roles
            BasicDataSeeder::class,          // Creates organization, department groups, and departments
            HardwareTypesSeeder::class,      // Seeds baseline hardware types
            UserSeeder::class,              // Creates users with proper assignments
            ScheduleEventTypeSeeder::class,  // Creates schedule event types
            ContractTypeSeeder::class,       // Creates contract types lookup data
            ContractStatusSeeder::class,     // Creates contract statuses lookup data
            HardwareTypeSeeder::class,       // Creates hardware types lookup data
            HardwareStatusSeeder::class,     // Creates hardware statuses lookup data
            TicketStatusSeeder::class,       // Creates ticket statuses and department group associations
            DashboardWidgetSeeder::class,    // Creates widget catalog
            UserWidgetSettingsSeeder::class, // Creates default user widget settings
            ApplicationSettingsSeeder::class, // Creates application settings
        ]);
        
        $this->command->info('✅ Database rebuild completed successfully!');
    }
}
