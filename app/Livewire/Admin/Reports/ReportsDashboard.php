<?php

namespace App\Livewire\Admin\Reports;

use Livewire\Component;

class ReportsDashboard extends Component
{
    public function render()
    {
        // Ensure admin role authorization
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to reports.');
        }
        
        $reportCategories = [
            'Ticket & Support Performance' => [
                [
                    'name' => 'Ticket Volume & Status Trends',
                    'description' => 'Track ticket counts and workload trends by status over time',
                    'route' => 'admin.reports.ticket-volume',
                    'icon' => 'chart-bar',
                    'available' => true
                ],
                [
                    'name' => 'Response & Resolution Time Analysis', 
                    'description' => 'Analyze SLA compliance and response performance',
                    'route' => 'admin.reports.response-times',
                    'icon' => 'clock'
                ],
                [
                    'name' => 'Agent Workload Distribution',
                    'description' => 'Monitor agent capacity and workload distribution',
                    'route' => 'admin.reports.agent-workload',
                    'icon' => 'user-group'
                ],
                [
                    'name' => 'Ticket Type & Priority Breakdown',
                    'description' => 'Analyze ticket patterns by type and priority',
                    'route' => 'admin.reports.ticket-breakdown',
                    'icon' => 'chart-pie'
                ],
                [
                    'name' => 'Aging & Overdue Tickets',
                    'description' => 'Identify stalled tickets requiring attention',
                    'route' => 'admin.reports.aging-tickets',
                    'icon' => 'exclamation-triangle'
                ]
            ],
            'Organization & Contract Oversight' => [
                [
                    'name' => 'Organization Summary',
                    'description' => 'High-level overview of client engagement and health',
                    'route' => 'admin.reports.organization-summary',
                    'icon' => 'building-office-2',
                    'available' => true
                ],
                [
                    'name' => 'Contract Renewal Forecast',
                    'description' => 'Track contracts approaching renewal dates',
                    'route' => 'admin.reports.contract-renewals',
                    'icon' => 'document-text'
                ],
                [
                    'name' => 'Contract Value by Organization',
                    'description' => 'Revenue tracking and contract value analysis',
                    'route' => 'admin.reports.contract-values',
                    'icon' => 'currency-dollar'
                ]
            ],
            'Hardware & Asset Management' => [
                [
                    'name' => 'Hardware Inventory Snapshot',
                    'description' => 'Complete asset inventory with status and allocation',
                    'route' => 'admin.reports.hardware-inventory',
                    'icon' => 'computer-desktop'
                ],
                [
                    'name' => 'Warranty & Maintenance Schedule',
                    'description' => 'Track upcoming maintenance and warranty expirations',
                    'route' => 'admin.reports.warranty-schedule',
                    'icon' => 'wrench-screwdriver'
                ],
                [
                    'name' => 'Hardware Allocation by Contract',
                    'description' => 'Validate contract deliverables and asset distribution',
                    'route' => 'admin.reports.hardware-allocation',
                    'icon' => 'squares-2x2'
                ]
            ],
            'User & Department Activity' => [
                [
                    'name' => 'User Account Status & Access',
                    'description' => 'Security audit and access management overview',
                    'route' => 'admin.reports.user-status',
                    'icon' => 'users'
                ],
                [
                    'name' => 'Department Performance',
                    'description' => 'Measure support team effectiveness and resource allocation',
                    'route' => 'admin.reports.department-performance',
                    'icon' => 'building-office'
                ],
                [
                    'name' => 'Agent Productivity',
                    'description' => 'Track individual agent performance metrics',
                    'route' => 'admin.reports.agent-productivity',
                    'icon' => 'chart-line'
                ],
                [
                    'name' => 'User Activity Log',
                    'description' => 'Audit trail of user actions across the system',
                    'route' => 'admin.reports.user-activity',
                    'icon' => 'clipboard-document-list',
                    'available' => true
                ]
            ],
            'Schedule & Workforce Planning' => [
                [
                    'name' => 'Schedule Coverage',
                    'description' => 'Ensure adequate staffing and identify coverage gaps',
                    'route' => 'admin.reports.schedule-coverage',
                    'icon' => 'calendar-days'
                ],
                [
                    'name' => 'User Schedule Summary',
                    'description' => 'Track workload and validate attendance data',
                    'route' => 'admin.reports.schedule-summary',
                    'icon' => 'calendar-days'
                ]
            ]
        ];

        return view('livewire.admin.reports.dashboard', compact('reportCategories'))
            ->title('Reports Dashboard');
    }

}