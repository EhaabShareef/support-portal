<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class LinkHardwareModal extends Component
{
    use AuthorizesRequests;

    public Ticket $ticket;
    public bool $show = false;

    protected $listeners = ['link-hardware:toggle' => 'toggle'];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->authorize('view', $this->ticket);
    }

    public function toggle(): void
    {
        $this->authorize('update', $this->ticket);

        if ($this->ticket->isClosed()) {
            return;
        }

        $this->show = ! $this->show;
    }

    public function render()
    {
        return view('livewire.tickets.link-hardware-modal');
    }
}

