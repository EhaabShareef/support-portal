<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Livewire\Component;

class ConversationThread extends Component
{
    protected $listeners = ['thread:refresh' => 'refreshConversation'];

    public Ticket $ticket;

    /**
     * Initialize the component with the given ticket and build its conversation.
     *
     * Assigns the provided Ticket instance to the component and populates the ticket's
     * `conversation` relation by calling refreshConversation().
     *
     * @param Ticket $ticket The ticket instance this component will manage and render.
     */
    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        $this->refreshConversation();
    }

    /**
     * Build and attach a unified conversation collection to the component's ticket.
     *
     * Loads the ticket's messages and public (non-internal) notes, normalizes their
     * shape so each item has `sender_id`, `message`, `created_at`, a `type`
     * (`'message'` or `'note'`), `ticket_id`, `is_system_message`, and `attachments`,
     * then merges and sorts them by `created_at` (newest first).
     *
     * - Messages are loaded with their sender and attachments and tagged with type `'message'`.
     * - Public notes are loaded with their user, remapped to the same field names,
     *   tagged with type `'note'`, given `ticket_id = null`, `is_system_message = false`,
     *   and an empty `attachments` collection is assigned.
     *
     * The resulting collection is set as the ticket's `conversation` relation.
     *
     * @return void
     */
    public function refreshConversation(): void
    {
        $messages = $this->ticket->messages()
            ->select(['id','ticket_id','sender_id','message','is_system_message','created_at'])
            ->with(['sender:id,name','attachments:id,uuid,attachable_id,attachable_type,original_name,stored_name,mime_type,size,is_image'])
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

    /**
     * Render the conversation thread Livewire view.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.tickets.conversation-thread');
    }
}
