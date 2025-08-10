<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
            UserSeeder::class,              // Creates users with proper assignments
            ScheduleEventTypeSeeder::class,  // Creates schedule event types
            DashboardWidgetSeeder::class,    // Creates widget catalog
            UserWidgetSettingsSeeder::class, // Creates default user widget settings
            ApplicationSettingsSeeder::class, // Creates application settings
        ]);
        
        $this->command->info('✅ Database rebuild completed successfully!');
    }
}
