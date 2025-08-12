<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Attributes\On;
use Livewire\Component;

class ManageSettings extends Component
{
    public string $activeTab = 'application';
    public string $flashMessage = '';
    public string $flashType = '';

    protected $listeners = ['flash' => 'handleFlash'];

    public function mount()
    {
        // Check permissions - require admin role for access
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'You do not have permission to manage settings.');
        }
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->dispatch('tabChanged', $tab);
    }

    #[On('flash')]
    public function handleFlash(string $message, string $type = 'success'): void
    {
        session()->flash($type === 'error' ? 'error' : 'message', $message);
    }

    public function render()
    {
        return view('livewire.admin.settings.manage-settings');
    }
}