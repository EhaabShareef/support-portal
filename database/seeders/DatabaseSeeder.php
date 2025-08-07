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
        $this->command->info('🚀 Starting database seeding...');
        
        // Seed roles and permissions first
        $this->call([
            RolePermissionSeeder::class,
            BasicDataSeeder::class,
        ]);
        
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('Default login credentials:');
        $this->command->info('📧 Super Admin: superadmin@htm.com / password');
        $this->command->info('📧 Admin: admin@ht.com / password');
        $this->command->info('📧 Agent: agent@ht.com / password');
        $this->command->info('📧 Client: client@ht.com / password');
    }
}
