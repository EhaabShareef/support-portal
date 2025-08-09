<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting complete database rebuild...');
        
        // Seed in correct order
        $this->call([
            RolePermissionSeeder::class,  // Clears all data, creates permissions and roles
            DepartmentGroupSeeder::class, // Creates department groups
            DepartmentSeeder::class,      // Creates departments
            UserSeeder::class,           // Creates users with proper assignments
            DashboardWidgetSeeder::class, // Creates widget catalog
            UserWidgetSettingsSeeder::class, // Creates default user widget settings
        ]);
        
        $this->command->info('✅ Database rebuild completed successfully!');
        $this->command->info('');
        $this->command->info('🔑 Default login credentials:');
        $this->command->info('📧 Admin: superadmin@hospitalitytechnology.com.mv / password');
        $this->command->info('📧 Admin Manager: admin@hospitalitytechnology.com.mv / password');
        $this->command->info('📧 PMS Manager: pms@hospitalitytechnology.com.mv / password');
        $this->command->info('📧 POS Manager: pos@hospitalitytechnology.com.mv / password');
        $this->command->info('📧 MC Manager: mc@hospitalitytechnology.com.mv / password');
        $this->command->info('📧 BO Manager: bo@hospitalitytechnology.com.mv / password');
        $this->command->info('📧 Hardware Manager: hardware@hospitalitytechnology.com.mv / password');
        $this->command->info('📧 Email Manager: email@hospitalitytechnology.com.mv / password');
        $this->command->info('');
        $this->command->info('👥 Roles created: admin (full access), support (limited access)');
    }
}
