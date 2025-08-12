<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;
use App\Models\Organization;
use App\Services\TicketColorService;

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

            // Support Hotline Numbers - used by CreateTicket and other components
            [
                'key' => 'support_hotlines',
                'value' => json_encode([
                    'pms_hotline' => [
                        'name' => 'PMS Hotline',
                        'number' => '+1-800-PMS-HELP',
                        'description' => 'Property Management System technical support',
                        'is_active' => true,
                        'sort_order' => 1,
                    ],
                    'pos_hotline' => [
                        'name' => 'POS Hotline', 
                        'number' => '+1-800-POS-HELP',
                        'description' => 'Point of Sale system technical support',
                        'is_active' => true,
                        'sort_order' => 2,
                    ],
                    'mc_hotline' => [
                        'name' => 'MC Hotline',
                        'number' => '+1-800-MC-HELP',
                        'description' => 'Management Center technical support',
                        'is_active' => true,
                        'sort_order' => 3,
                    ],
                    'manager_on_duty' => [
                        'name' => 'Manager on Duty',
                        'number' => '+1-800-MOD-HELP',
                        'description' => 'Emergency escalation and management assistance',
                        'is_active' => true,
                        'sort_order' => 4,
                    ],
                ]),
                'type' => 'json',
                'label' => 'Support Hotline Numbers',
                'description' => 'Technical support hotline numbers for different systems',
                'group' => 'support',
                'validation_rules' => ['required', 'json'],
                'is_public' => true,
                'is_encrypted' => false,
            ],
        ];

        // Add ticket color settings
        $colorSettings = TicketColorService::getDefaultSettings();
        $settings = array_merge($settings, $colorSettings);

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