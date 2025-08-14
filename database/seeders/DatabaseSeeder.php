<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * For production deployment, this seeder provides only essential baseline data.
     * For development with sample data, add OrganizationContractSeeder, OrganizationHardwareSeeder, and SampleTicketSeeder.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting complete database rebuild...');
        
        // Core system data in dependency order
        $this->call([
            RolePermissionSeeder::class,        // Clears all data, creates permissions and roles
            BasicDataSeeder::class,             // Creates organization, department groups, and departments
            UserSeeder::class,                  // Creates users with proper assignments
            ScheduleEventTypeSeeder::class,     // Creates schedule event types
            ContractTypeSeeder::class,          // Creates contract types lookup data
            ContractStatusSeeder::class,        // Creates contract statuses lookup data
            HardwareTypeSeeder::class,          // Creates hardware types lookup data
            HardwareStatusSeeder::class,        // Creates hardware statuses lookup data
            TicketStatusSeeder::class,          // Creates ticket statuses and department group associations
            DashboardWidgetSeeder::class,       // Creates widget catalog
            UserWidgetSettingsSeeder::class,    // Creates default user widget settings
            ApplicationSettingsSeeder::class,   // Creates application settings
            
            // Sample data for default organization
            OrganizationContractSeeder::class,  // Creates sample contracts for default organization
            OrganizationHardwareSeeder::class,  // Creates sample hardware for default organization
            SampleTicketSeeder::class,          // Creates sample tickets with messages
        ]);
        
        $this->command->info('✅ Database rebuild completed successfully!');
    }
}
