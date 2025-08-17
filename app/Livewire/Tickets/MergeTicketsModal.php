<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Services\Tickets\TicketMergeService;
use Livewire\Component;

class MergeTicketsModal extends Component
{
    public Ticket $ticket;
    public string $ticketsInput = '';
    public bool $show = false;

    protected $rules = [
        'ticketsInput' => 'required|string',
    ];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    public function merge(TicketMergeService $service)
    {
        $data = $this->validate();
        $ids = array_merge([$this->ticket->id], array_filter(array_map('intval', explode(',', $data['ticketsInput']))));
        $new = $service->merge($ids, auth()->id());
        return redirect()->route('tickets.show', $new);
    }

    public function render()
    {
        return view('livewire.tickets.merge-tickets-modal');
    }
}
