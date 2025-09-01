<?php

namespace App\Livewire\Settings\Users;

use Livewire\Component;

class Index extends Component
{
    public string $section = 'dep_groups';
    public bool $showMobileNav = false;

    public function setSection(string $section): void
    {
        $this->section = $section;
    }

    public function render()
    {
        return view('livewire.settings.users.index');
    }
}
