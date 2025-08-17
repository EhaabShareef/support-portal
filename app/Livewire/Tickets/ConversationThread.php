<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Livewire\Component;

class ConversationThread extends Component
{
    protected $listeners = ['thread:refresh' => 'refreshConversation'];

    public Ticket $ticket;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->refreshConversation();
    }

    public function refreshConversation(): void
    {
        $messages = $this->ticket->messages()
            ->where('is_log', false)
            ->select(['id','ticket_id','sender_id','message','is_system_message','created_at'])
            ->with(['sender:id,name','attachments:id,ticket_message_id,original_name,path,disk'])
            ->selectRaw("'message' as type")
            ->get();

        $publicNotes = $this->ticket->notes()
            ->where('is_internal', false)
            ->select(['id','user_id as sender_id','note as message','created_at'])
            ->with('user:id,name')
            ->selectRaw("'note' as type")
            ->selectRaw("null as ticket_id")
            ->selectRaw("false as is_system_message")
            ->get()
            ->map(function ($note) {
                $note->attachments = collect();
                return $note;
            });

        $conversation = $messages->concat($publicNotes)->sortByDesc('created_at')->values();
        $this->ticket->setRelation('conversation', $conversation);
    }

    public function render()
    {
        return view('livewire.tickets.conversation-thread');
    }
}
