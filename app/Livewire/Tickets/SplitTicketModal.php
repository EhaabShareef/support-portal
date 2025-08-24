<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Services\Tickets\TicketSplitService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Component;

class SplitTicketModal extends Component
{
    use AuthorizesRequests;

    public Ticket $ticket;
    public ?int $startMessageId = null;
    public bool $closeOriginal = false;
    public bool $copyNotes = false;
    public bool $show = false;

    /** @var array<int, array{id:int,preview:string}> */
    public array $messagesList = [];

    protected $listeners = ['split:toggle' => 'toggle'];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->messagesList = $ticket->messages()
            ->where('is_log', false)
            ->latest()
            ->take(100)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'preview' => '#'.$m->id.' • '.($m->sender?->name ?? 'System').' • '.$m->created_at->format('Y-m-d H:i').' • '.Str::limit(strip_tags($m->message), 48),
            ])->toArray();
    }

    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    public function split(TicketSplitService $service)
    {
        $this->authorize('split', $this->ticket);

        $this->validate([
            'startMessageId' => 'required|integer',
        ]);

        $new = $service->split($this->ticket, $this->startMessageId, [
            'close_original' => $this->closeOriginal,
            'copy_notes' => $this->copyNotes,
        ], auth()->user());

        session()->flash('message', 'Ticket split successfully.');

        return redirect()->route('tickets.show', $new);
    }

    public function render()
    {
        return view('livewire.tickets.split-ticket-modal');
    }
}
