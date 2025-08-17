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
    public string $cc = '';
    public bool $show = false;

    protected $rules = [
        'replyMessage' => 'required|string|max:2000',
        'replyStatus' => 'required|string',
        'attachments.*' => 'file|max:10240',
        'cc' => 'nullable|string'
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

        $message = TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'sender_id' => auth()->id(),
            'message' => $this->replyMessage,
            'is_system_message' => false,
        ]);

        foreach (array_filter(array_map('trim', preg_split('/[,\s]+/', $this->cc))) as $email) {
            \App\Models\TicketCcRecipient::updateOrCreate([
                'ticket_id' => $this->ticket->id,
                'email' => $email,
            ], ['active' => true]);
        }

        $this->ticket->update(['status' => $this->replyStatus]);

        $this->reset(['replyMessage','attachments']);

        $this->dispatch('thread:refresh')->to(ConversationThread::class);
    }

    public function render()
    {
        return view('livewire.tickets.reply-form');
    }
}
