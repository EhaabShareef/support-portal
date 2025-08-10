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
        
        DashboardWidget::create([
            'name' => 'System Health Monitor',
            'description' => 'Real-time system performance and health monitoring',
            'category' => 'admin',
            'base_component' => 'admin.system-health',
            'available_sizes' => ['1x1', '2x2', '3x2'],
            'default_size' => '2x2',
            'sort_order' => 2,
            'is_active' => true,
            'is_default_visible' => true,
            'permissions' => ['dashboard.admin'],
            'options' => [
                'cache_ttl' => 60,
                'alert_threshold' => 80,
                'show_detailed_metrics' => true,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Ticket Analytics',
            'description' => 'Advanced ticket statistics and trend analysis',
            'category' => 'admin',
            'base_component' => 'admin.ticket-analytics',
            'available_sizes' => ['1x1', '2x2', '3x2'],
            'default_size' => '2x2',
            'sort_order' => 3,
            'is_active' => true,
            'is_default_visible' => true,
            'permissions' => ['dashboard.admin'],
            'options' => [
                'cache_ttl' => 300,
                'show_trends' => true,
                'include_charts' => true,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Organization Management',
            'description' => 'Organization overview and contract alerts',
            'category' => 'admin',
            'base_component' => 'admin.organization-management',
            'available_sizes' => ['1x1', '2x2', '3x2'],
            'default_size' => '2x2',
            'sort_order' => 4,
            'is_active' => false, // Will be implemented later
            'is_default_visible' => true,
            'permissions' => ['dashboard.admin'],
            'options' => [
                'cache_ttl' => 300,
                'show_alerts' => true,
                'alert_days' => 30,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'User Activity Monitor',
            'description' => 'User activity and authentication metrics',
            'category' => 'admin',
            'base_component' => 'admin.user-activity',
            'available_sizes' => ['1x1', '2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 5,
            'is_active' => false, // Will be implemented later
            'is_default_visible' => true,
            'permissions' => ['dashboard.admin'],
            'options' => [
                'cache_ttl' => 300,
                'show_online_users' => true,
                'activity_period' => 24,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Department Performance',
            'description' => 'Department efficiency and workload analysis',
            'category' => 'admin',
            'base_component' => 'admin.department-performance',
            'available_sizes' => ['2x1', '2x2', '3x2'],
            'default_size' => '2x2',
            'sort_order' => 6,
            'is_active' => false, // Will be implemented later
            'is_default_visible' => true,
            'permissions' => ['dashboard.admin'],
            'options' => [
                'cache_ttl' => 300,
                'comparison_period' => 30,
                'show_trends' => true,
            ],
        ]);
    }
    
    private function createSupportWidgets(): void
    {
        DashboardWidget::create([
            'name' => 'My Workload',
            'description' => 'Personal ticket queue and workload metrics',
            'category' => 'support',
            'base_component' => 'support.my-workload',
            'available_sizes' => ['1x1', '2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 1,
            'is_active' => true,
            'is_default_visible' => true,
            'permissions' => ['dashboard.support'],
            'options' => [
                'cache_ttl' => 300,
                'show_priority' => true,
                'show_due_dates' => true,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Team Performance',
            'description' => 'Department team metrics and performance ranking',
            'category' => 'support',
            'base_component' => 'support.team-performance',
            'available_sizes' => ['1x1', '2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 2,
            'is_active' => false, // Will be implemented later
            'is_default_visible' => true,
            'permissions' => ['dashboard.support'],
            'options' => [
                'cache_ttl' => 300,
                'show_ranking' => true,
                'comparison_period' => 7,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Quick Actions',
            'description' => 'Common support actions and shortcuts',
            'category' => 'support',
            'base_component' => 'support.quick-actions',
            'available_sizes' => ['1x1', '2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 3,
            'is_active' => false, // Will be implemented later
            'is_default_visible' => true,
            'permissions' => ['dashboard.support'],
            'options' => [
                'show_create_ticket' => true,
                'show_knowledge_search' => true,
                'show_client_lookup' => true,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Recent Activity',
            'description' => 'Recent tickets and updates in department',
            'category' => 'support',
            'base_component' => 'support.recent-activity',
            'available_sizes' => ['2x1', '2x2', '3x2'],
            'default_size' => '2x2',
            'sort_order' => 4,
            'is_active' => false, // Will be implemented later
            'is_default_visible' => true,
            'permissions' => ['dashboard.support'],
            'options' => [
                'cache_ttl' => 180,
                'limit' => 10,
                'show_assignments' => true,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Knowledge Insights',
            'description' => 'Popular solutions and FAQ metrics',
            'category' => 'support',
            'base_component' => 'support.knowledge-insights',
            'available_sizes' => ['1x1', '2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 5,
            'is_active' => false, // Will be implemented later
            'is_default_visible' => false,
            'permissions' => ['dashboard.support'],
            'options' => [
                'cache_ttl' => 600,
                'show_trending' => true,
                'period' => 30,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Agent Ticket Contributions',
            'description' => 'GitHub-style heatmap of daily ticket activity',
            'category' => 'support',
            'base_component' => 'support.agent-contributions',
            'available_sizes' => ['1x1', '2x2', '3x2'],
            'default_size' => '2x2',
            'sort_order' => 6,
            'is_active' => true,
            'is_default_visible' => true,
            'permissions' => ['dashboard.support'],
            'options' => [
                'cache_ttl' => 300,
                'show_legend' => true,
                'color_theme' => 'green',
            ],
        ]);
    }
    
    private function createClientWidgets(): void
    {
        DashboardWidget::create([
            'name' => 'My Tickets Overview',
            'description' => 'Overview of organization tickets and status',
            'category' => 'client',
            'base_component' => 'client.my-tickets',
            'available_sizes' => ['1x1', '2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 1,
            'is_active' => true,
            'is_default_visible' => true,
            'permissions' => ['dashboard.client'],
            'options' => [
                'cache_ttl' => 300,
                'show_resolved' => false,
                'limit' => 10,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Service Status',
            'description' => 'Organization service health and SLA metrics',
            'category' => 'client',
            'base_component' => 'client.service-status',
            'available_sizes' => ['1x1', '2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 2,
            'is_active' => false, // Will be implemented later
            'is_default_visible' => true,
            'permissions' => ['dashboard.client'],
            'options' => [
                'cache_ttl' => 300,
                'show_sla_compliance' => true,
                'show_uptime' => true,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Contract Information',
            'description' => 'Active contracts and renewal alerts',
            'category' => 'client',
            'base_component' => 'client.contract-info',
            'available_sizes' => ['2x1', '2x2', '3x2'],
            'default_size' => '2x2',
            'sort_order' => 3,
            'is_active' => false, // Will be implemented later
            'is_default_visible' => true,
            'permissions' => ['dashboard.client'],
            'options' => [
                'cache_ttl' => 600,
                'show_expiration_alerts' => true,
                'alert_days' => 30,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Hardware Assets',
            'description' => 'Organization hardware inventory and warranty status',
            'category' => 'client',
            'base_component' => 'client.hardware-assets',
            'available_sizes' => ['1x1', '2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 4,
            'is_active' => false, // Will be implemented later
            'is_default_visible' => true,
            'permissions' => ['dashboard.client'],
            'options' => [
                'cache_ttl' => 600,
                'show_warranty_alerts' => true,
                'show_maintenance_schedule' => true,
            ],
        ]);
        
        DashboardWidget::create([
            'name' => 'Quick Support',
            'description' => 'Easy access to support options and resources',
            'category' => 'client',
            'base_component' => 'client.quick-support',
            'available_sizes' => ['1x1', '2x1', '2x2'],
            'default_size' => '2x1',
            'sort_order' => 5,
            'is_active' => false, // Will be implemented later
            'is_default_visible' => true,
            'permissions' => ['dashboard.client'],
            'options' => [
                'show_create_ticket' => true,
                'show_knowledge_base' => true,
                'show_contact_info' => true,
            ],
        ]);
    }
}