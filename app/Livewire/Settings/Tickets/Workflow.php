<?php

namespace App\Livewire\Settings\Tickets;

use Livewire\Component;
use App\Models\TicketStatus;
use App\Models\Setting;
use App\Enums\TicketPriority;

class Workflow extends Component
{
    // Form properties
    public $defaultStatusOnReply = '';
    public $reopenWindowDays = 3;
    public $messageOrdering = 'newest_first';
    public $allowClientEscalation = false;
    public $escalationCooldownHours = 24;

    // Available options
    public $statusOptions = [];
    public $messageOrderingOptions = [
        'newest_first' => 'Newest First',
        'oldest_first' => 'Oldest First'
    ];
    public $priorityOptions = [];

    // UI state
    public $showFlash = false;
    public $flashMessage = '';
    public $flashType = 'success';

    protected $rules = [
        'defaultStatusOnReply' => 'required|exists:ticket_statuses,id',
        'reopenWindowDays' => 'required|integer|min:0|max:365',
        'messageOrdering' => 'required|in:newest_first,oldest_first',
        'allowClientEscalation' => 'boolean',
        'escalationCooldownHours' => 'required_if:allowClientEscalation,true|integer|min:1|max:168',
    ];

    public function mount()
    {
        $this->loadSettings();
        $this->loadStatusOptions();
        $this->loadPriorityOptions();
    }

    public function loadSettings()
    {
        // Load existing settings
        $this->defaultStatusOnReply = Setting::get('tickets.default_status_on_reply', '');
        $this->reopenWindowDays = Setting::get('tickets.reopen_window_days', 3);
        $this->messageOrdering = Setting::get('tickets.message_ordering', 'newest_first');
        $this->allowClientEscalation = Setting::get('tickets.allow_client_escalation', false);
        $this->escalationCooldownHours = Setting::get('tickets.escalation_cooldown_hours', 24);
    }

    public function loadStatusOptions()
    {
        $this->statusOptions = TicketStatus::orderBy('name')->pluck('name', 'id')->toArray();
    }

    public function loadPriorityOptions()
    {
        $this->priorityOptions = TicketPriority::options();
    }

    public function saveSettings()
    {
        $this->validate();

        try {
            // Save settings
            Setting::set('tickets.default_status_on_reply', $this->defaultStatusOnReply);
            Setting::set('tickets.reopen_window_days', $this->reopenWindowDays);
            Setting::set('tickets.message_ordering', $this->messageOrdering);
            Setting::set('tickets.allow_client_escalation', $this->allowClientEscalation);
            Setting::set('tickets.escalation_cooldown_hours', $this->escalationCooldownHours);

            $this->showFlashMessage('Workflow settings saved successfully!', 'success');
        } catch (\Exception $e) {
            $this->showFlashMessage('Error saving settings: ' . $e->getMessage(), 'error');
        }
    }

    public function resetToDefaults()
    {
        $this->defaultStatusOnReply = '';
        $this->reopenWindowDays = 3;
        $this->messageOrdering = 'newest_first';
        $this->allowClientEscalation = false;
        $this->escalationCooldownHours = 24;

        $this->showFlashMessage('Settings reset to defaults!', 'success');
    }

    private function showFlashMessage($message, $type = 'success')
    {
        $this->flashMessage = $message;
        $this->flashType = $type;
        $this->showFlash = true;

        $this->dispatch('flash-message', [
            'message' => $message,
            'type' => $type
        ]);
    }

    public function render()
    {
        return view('livewire.settings.tickets.workflow');
    }
}
