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
        
        // Seed basic data
        $this->call([
            BasicDataSeeder::class,
        ]);
        
        $this->command->info('✅ Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('Default login credentials:');
        $this->command->info('📧 Super Admin: superadmin@samplecompany.com / password');
        $this->command->info('📧 Admin: admin@samplecompany.com / password');
        $this->command->info('📧 Agent: agent@samplecompany.com / password');
        $this->command->info('📧 Client: client@samplecompany.com / password');
    }
}
