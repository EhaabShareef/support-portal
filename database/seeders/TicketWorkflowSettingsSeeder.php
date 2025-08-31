<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class TicketWorkflowSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Default status on reply
            [
                'key' => 'tickets.default_status_on_reply',
                'value' => '',
                'type' => 'string',
                'group' => 'tickets',
                'label' => 'Default Status on Reply',
                'description' => 'Default status when staff replies to tickets',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            
            // Reopen window days
            [
                'key' => 'tickets.reopen_window_days',
                'value' => '3',
                'type' => 'integer',
                'group' => 'tickets',
                'label' => 'Reopen Window Days',
                'description' => 'Days clients can reopen closed tickets',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            
            // Message ordering
            [
                'key' => 'tickets.message_ordering',
                'value' => 'newest_first',
                'type' => 'string',
                'group' => 'tickets',
                'label' => 'Message Ordering',
                'description' => 'How ticket messages are displayed',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            
            // Allow client escalation
            [
                'key' => 'tickets.allow_client_escalation',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'tickets',
                'label' => 'Allow Client Escalation',
                'description' => 'Allow clients to escalate after admin/agent de-escalation',
                'is_public' => false,
                'is_encrypted' => false,
            ],
            
            // Escalation cooldown hours
            [
                'key' => 'tickets.escalation_cooldown_hours',
                'value' => '24',
                'type' => 'integer',
                'group' => 'tickets',
                'label' => 'Escalation Cooldown Hours',
                'description' => 'Hours clients must wait after de-escalation before they can escalate again',
                'is_public' => false,
                'is_encrypted' => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
