<?php

namespace Database\Seeders;

use App\Models\DashboardWidget;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DashboardWidgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🎯 Seeding dashboard widgets...');
        
        // Clear existing widgets
        DB::table('user_widget_settings')->delete();
        DB::table('dashboard_widgets')->delete();
        
        // Admin Widgets
        $this->createAdminWidgets();
        
        // Support Widgets (placeholder for future implementation)
        $this->createSupportWidgets();
        
        // Client Widgets (placeholder for future implementation)
        $this->createClientWidgets();
        
        $this->command->info('✅ Dashboard widgets seeded successfully!');
    }
    
    private function createAdminWidgets(): void
    {
        DashboardWidget::create([
            'name' => 'Admin Metrics',
            'description' => 'Comprehensive system metrics for administrators',
            'category' => 'admin',
            'base_component' => 'admin.metrics',
            'available_sizes' => ['1x1', '2x2', '3x2'],
            'default_size' => '2x2',
            'sort_order' => 1,
            'is_active' => true,
            'is_default_visible' => true,
            'permissions' => ['dashboard.admin'],
            'options' => [
                'cache_ttl' => 300,
                'show_trends' => true,
                'include_charts' => true,
            ],
        ]);
        
        // Placeholder for future admin widgets
        DashboardWidget::create([
            'name' => 'System Health',
            'description' => 'Monitor system performance and health',
            'category' => 'admin',
            'base_component' => 'admin.system-health',
            'available_sizes' => ['2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 2,
            'is_active' => false, // Disabled until implemented
            'is_default_visible' => false,
            'permissions' => ['dashboard.admin'],
            'options' => [
                'cache_ttl' => 60,
                'alert_threshold' => 80,
            ],
        ]);
    }
    
    private function createSupportWidgets(): void
    {
        DashboardWidget::create([
            'name' => 'My Tickets',
            'description' => 'Overview of assigned tickets and workload',
            'category' => 'support',
            'base_component' => 'support.my-tickets',
            'available_sizes' => ['1x1', '2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 1,
            'is_active' => false, // Disabled until implemented
            'is_default_visible' => true,
            'permissions' => ['dashboard.support'],
            'options' => [
                'show_priority' => true,
                'group_by_status' => true,
            ],
        ]);
    }
    
    private function createClientWidgets(): void
    {
        DashboardWidget::create([
            'name' => 'My Organization Tickets',
            'description' => 'Overview of organization tickets and status',
            'category' => 'client',
            'base_component' => 'client.organization-tickets',
            'available_sizes' => ['2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 1,
            'is_active' => false, // Disabled until implemented
            'is_default_visible' => true,
            'permissions' => ['dashboard.client'],
            'options' => [
                'show_resolved' => false,
                'limit' => 10,
            ],
        ]);
    }
}