<?php

namespace App\Livewire\Admin\Settings;

use App\Contracts\SettingsRepositoryInterface;
use Livewire\Attributes\On;
use Livewire\Component;

class Shell extends Component
{
    public string $activeTab = 'general';
    public string $flashMessage = '';
    public string $flashType = '';

    protected $listeners = [
        'saved' => 'handleSaved',
        'error' => 'handleError', 
        'reset' => 'handleReset'
    ];

    public function mount()
    {
        // Check permissions - require admin role for access
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to manage settings.');
        }

        // Validate active tab parameter
        $validTabs = ['general', 'ticket', 'organization', 'contracts', 'hardware', 'schedule', 'users'];
        if (request('tab') && in_array(request('tab'), $validTabs)) {
            $this->activeTab = request('tab');
        }
    }

    public function setActiveTab(string $tab): void
    {
        $validTabs = ['general', 'ticket', 'organization', 'contracts', 'hardware', 'schedule', 'users'];
        
        if (in_array($tab, $validTabs)) {
            $this->activeTab = $tab;
            $this->dispatch('tabChanged', $tab);
        }
    }

    #[On('saved')]
    public function handleSaved(string $message): void
    {
        session()->flash('message', $message);
    }

    #[On('error')]
    public function handleError(string $message): void
    {
        session()->flash('error', $message);
    }

    #[On('reset')]
    public function handleReset(string $message): void
    {
        session()->flash('message', $message);
    }

    public function getTabsProperty()
    {
        return [
            'general' => [
                'label' => 'General',
                'description' => 'App-wide settings and hotlines',
                'icon' => 'heroicon-o-cog-6-tooth',
                'component' => 'admin.settings.tabs.settings-general'
            ],
            'ticket' => [
                'label' => 'Ticket',
                'description' => 'Ticket workflow, colors, and statuses',
                'icon' => 'heroicon-o-ticket',
                'component' => 'admin.settings.tabs.settings-ticket'
            ],
            'organization' => [
                'label' => 'Organization',
                'description' => 'Department groups and departments',
                'icon' => 'heroicon-o-building-office',
                'component' => 'admin.settings.tabs.settings-organization'
            ],
            'contracts' => [
                'label' => 'Contracts',
                'description' => 'Contract types and statuses',
                'icon' => 'heroicon-o-document-text',
                'component' => 'admin.settings.tabs.settings-contracts'
            ],
            'hardware' => [
                'label' => 'Hardware',
                'description' => 'Hardware types and statuses',
                'icon' => 'heroicon-o-computer-desktop',
                'component' => 'admin.settings.tabs.settings-hardware'
            ],
            'schedule' => [
                'label' => 'Schedule',
                'description' => 'Weekend days and event types',
                'icon' => 'heroicon-o-calendar-days',
                'component' => 'admin.settings.tabs.settings-schedule'
            ],
            'users' => [
                'label' => 'Users',
                'description' => 'User management defaults',
                'icon' => 'heroicon-o-users',
                'component' => 'admin.settings.tabs.settings-users'
            ]
        ];
    }

    public function render()
    {
        return view('livewire.admin.settings.shell');
    }
}