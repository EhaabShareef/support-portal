<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Url;

class UsersRoles extends Component
{
    #[Url(as: 'tab')]
    public $activeTab = 'users';

    public function mount($tab = null)
    {
        // Set active tab from query parameter
        if ($tab && in_array($tab, ['users', 'roles'])) {
            $this->activeTab = $tab;
        }
    }

    public function setTab($tab)
    {
        if (in_array($tab, ['users', 'roles'])) {
            $this->activeTab = $tab;
        }
    }

    public function render()
    {
        return view('livewire.admin.users-roles')
            ->title('Users & Roles - Admin')
            ->layout('components.layouts.app');
    }
}