<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ReplyForm extends Component
{
    use WithFileUploads;

    public Ticket $ticket;
    public string $replyMessage = '';
    public string $replyStatus = 'in_progress';
    public array $attachments = [];
    public bool $show = false;

    protected $rules = [
        'replyMessage' => 'required|string|max:2000',
        'replyStatus' => 'required|string',
        'attachments.*' => 'file|max:10240'
    ];

    protected $listeners = ['reply:toggle' => 'toggle'];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    public function sendMessage(): void
    {
        $this->validate();

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'sender_id' => auth()->id(),
            'message' => $this->replyMessage,
            'is_system_message' => false,
        ]);

        $this->ticket->update(['status' => $this->replyStatus]);

        $this->reset(['replyMessage','attachments']);

        $this->dispatch('thread:refresh')->to(ConversationThread::class);
    }

    public function render()
    {
        return view('livewire.tickets.reply-form');
    }
}
