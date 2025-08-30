<?php

namespace App\Livewire\Settings\Tickets;

use Livewire\Component;

class Index extends Component
{
    public string $section = 'workflow';

    public function setSection(string $section): void
    {
        $this->section = $section;
    }

    public function render()
    {
        return view('livewire.settings.tickets.index');
    }
}
