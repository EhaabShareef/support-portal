<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Organization;

class ApplicationSettingsSeeder extends Seeder
{
    /**
     * Seed basic application settings.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding application settings...');

        // Get default organization for the default_organization setting
        $defaultOrg = Organization::first();
        
        if (!$defaultOrg) {
            $this->command->warn('No organization found. Please run organization seeder first.');
            return;
        }

        $settings = [
            // Schedule Weekend Configuration - used by ScheduleCalendar component
            [
                'key' => 'weekend_days',
                'value' => json_encode(['Friday', 'Saturday']),
                'type' => 'array',
                'label' => 'Weekend Days',
                'description' => 'Days to be highlighted as weekends in the schedule calendar',
                'group' => 'schedule',
                'validation_rules' => ['required', 'json'],
                'is_public' => false,
                'is_encrypted' => false,
            ],

            // Default Organization Setting - used by ManageUsers component
            [
                'key' => 'default_organization',
                'value' => $defaultOrg->id,
                'type' => 'integer',
                'label' => 'Default Organization',
                'description' => 'Default organization assigned to new users created through admin panel',
                'group' => 'user_management',
                'validation_rules' => ['required', 'integer', 'exists:organizations,id'],
                'is_public' => false,
                'is_encrypted' => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
            $this->command->info("✓ Setting configured: {$setting['key']}");
        }

        $this->command->info("✅ Application settings seeded successfully!");
    }
}