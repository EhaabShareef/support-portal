<?php

namespace Database\Seeders;

use App\Models\DashboardWidget;
use App\Models\User;
use App\Models\UserWidgetSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserWidgetSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('⚙️ Seeding user widget settings...');

        // Get all users with their roles
        $users = User::with('roles')->get();
        $widgets = DashboardWidget::all()->keyBy('key');

        $settingsCount = 0;

        foreach ($users as $user) {
            $userRole = $user->roles->first()?->name ?? 'client';
            
            // Get widgets appropriate for this user's role
            $roleWidgets = $this->getWidgetsForRole($userRole, $widgets);
            
            foreach ($roleWidgets as $widgetKey) {
                $widget = $widgets->get($widgetKey);
                
                if (!$widget) {
                    continue;
                }

                // Check if user has permission for this widget
                if ($widget->permissions && !empty($widget->permissions)) {
                    $hasPermission = true;
                    foreach ($widget->permissions as $permission) {
                        if (!$user->can($permission)) {
                            $hasPermission = false;
                            break;
                        }
                    }
                    if (!$hasPermission) {
                        continue;
                    }
                }

                // Create default settings for this user-widget combination
                UserWidgetSetting::create([
                    'user_id' => $user->id,
                    'widget_id' => $widget->id,
                    'is_visible' => true,
                    'size' => $widget->default_size,
                    'sort_order' => $widget->sort_order,
                    'options' => null, // Start with no custom options
                ]);

                $settingsCount++;
            }
        }

        $this->command->info("✅ User widget settings seeded successfully! Created {$settingsCount} settings for {$users->count()} users.");
    }

    /**
     * Get appropriate widgets for each role
     */
    private function getWidgetsForRole(string $role, $widgets): array
    {
        switch ($role) {
            case 'admin':
                return [
                    'admin_metrics',
                    'ticket_trends_chart',
                    'department_activity',
                    'contract_alerts',
                    'hardware_alerts',
                ];

            case 'support':
                return [
                    'agent_metrics',
                    'contribution_graph',
                    'department_ticket_status',
                    'agent_performance',
                    'recent_activity',
                    'quick_actions_support',
                ];

            case 'client':
            default:
                return [
                    'client_metrics',
                    'ticket_status_chart',
                    'active_contracts',
                    'recent_tickets',
                    'quick_actions_client',
                    'contact_support',
                ];
        }
    }
}