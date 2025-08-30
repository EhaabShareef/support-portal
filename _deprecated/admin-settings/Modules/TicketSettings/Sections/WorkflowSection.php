<?php

namespace App\Livewire\Admin\Settings\Modules\TicketSettings\Sections;

use App\Livewire\Admin\Settings\BaseSettingsComponent;

class WorkflowSection extends BaseSettingsComponent
{
    // Workflow Settings
    public string $defaultReplyStatus = 'in_progress';
    public int $reopenWindowDays = 3;
    public bool $requireEscalationConfirmation = true;
    public string $messageOrder = 'newest_first';

    protected function getSettingsGroup(): string
    {
        return 'tickets.workflow';
    }

    protected function getTitle(): string
    {
        return 'Workflow Settings';
    }

    protected function getDescription(): string
    {
        return 'Configure ticket workflow behavior';
    }

    protected function getIcon(): string
    {
        return 'heroicon-o-cog-6-tooth';
    }

    protected function loadData(): void
    {
        $this->defaultReplyStatus = $this->getSetting('tickets.workflow.default_reply_status', 'in_progress');
        $this->reopenWindowDays = (int) $this->getSetting('tickets.workflow.reopen_window_days', 3);
        $this->requireEscalationConfirmation = (bool) $this->getSetting('tickets.workflow.require_escalation_confirmation', true);
        $this->messageOrder = $this->getSetting('tickets.workflow.message_order', 'newest_first');
    }

    protected function saveData(): void
    {
        $this->setSetting('tickets.workflow.default_reply_status', $this->defaultReplyStatus);
        $this->setSetting('tickets.workflow.reopen_window_days', $this->reopenWindowDays, 'integer');
        $this->setSetting('tickets.workflow.require_escalation_confirmation', $this->requireEscalationConfirmation, 'boolean');
        $this->setSetting('tickets.workflow.message_order', $this->messageOrder);
    }

    public function render()
    {
        return view('livewire.admin.settings.modules.ticket-settings.sections.workflow');
    }
}
