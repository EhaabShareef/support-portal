<?php

namespace App\Livewire\Admin\Settings\Modules\TicketSettings\Sections;

use App\Livewire\Admin\Settings\BaseSettingsComponent;
use App\Enums\TicketPriority;

class PrioritySection extends BaseSettingsComponent
{
    // Priority Colors
    public array $priorityColors = [];
    public bool $enableCustomColors = true;
    public bool $showPriorityIcons = true;
    public bool $enablePriorityEscalation = true;
    public int $escalationDelayHours = 24;

    protected function getSettingsGroup(): string
    {
        return 'tickets.priority';
    }

    protected function getTitle(): string
    {
        return 'Priority Colors';
    }

    protected function getDescription(): string
    {
        return 'Customize priority color schemes';
    }

    protected function getIcon(): string
    {
        return 'heroicon-o-palette';
    }

    protected function loadData(): void
    {
        $this->priorityColors = $this->getSetting('tickets.priority.colors', [
            'low' => '#10b981',
            'medium' => '#f59e0b',
            'high' => '#ef4444',
            'urgent' => '#dc2626',
            'critical' => '#7c2d12',
        ]);
        $this->enableCustomColors = (bool) $this->getSetting('tickets.priority.enable_custom_colors', true);
        $this->showPriorityIcons = (bool) $this->getSetting('tickets.priority.show_icons', true);
        $this->enablePriorityEscalation = (bool) $this->getSetting('tickets.priority.enable_escalation', true);
        $this->escalationDelayHours = (int) $this->getSetting('tickets.priority.escalation_delay_hours', 24);
    }

    protected function saveData(): void
    {
        $this->setSetting('tickets.priority.colors', $this->priorityColors, 'json');
        $this->setSetting('tickets.priority.enable_custom_colors', $this->enableCustomColors, 'boolean');
        $this->setSetting('tickets.priority.show_icons', $this->showPriorityIcons, 'boolean');
        $this->setSetting('tickets.priority.enable_escalation', $this->enablePriorityEscalation, 'boolean');
        $this->setSetting('tickets.priority.escalation_delay_hours', $this->escalationDelayHours, 'integer');
    }

    public function updatePriorityColor(string $priority, string $color): void
    {
        $this->priorityColors[$priority] = $color;
        $this->markAsChanged();
    }

    public function resetToDefaults(): void
    {
        $this->priorityColors = [
            'low' => '#10b981',
            'medium' => '#f59e0b',
            'high' => '#ef4444',
            'urgent' => '#dc2626',
            'critical' => '#7c2d12',
        ];
        $this->markAsChanged();
    }

    public function getPriorities(): array
    {
        return [
            'low' => [
                'name' => 'Low',
                'description' => 'Low priority tickets',
                'icon' => 'heroicon-o-arrow-down',
            ],
            'medium' => [
                'name' => 'Medium',
                'description' => 'Medium priority tickets',
                'icon' => 'heroicon-o-minus',
            ],
            'high' => [
                'name' => 'High',
                'description' => 'High priority tickets',
                'icon' => 'heroicon-o-arrow-up',
            ],
            'urgent' => [
                'name' => 'Urgent',
                'description' => 'Urgent priority tickets',
                'icon' => 'heroicon-o-exclamation-triangle',
            ],
            'critical' => [
                'name' => 'Critical',
                'description' => 'Critical priority tickets',
                'icon' => 'heroicon-o-exclamation-circle',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.admin.settings.modules.ticket-settings.sections.priority');
    }
}
